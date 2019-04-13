<?php

class Vindi_Subscription_Helper_Order
{
	use Vindi_Subscription_Trait_LogMessenger; 
	
	/**
	 * Altera o status de um pedido para Cancelado
	 *
	 * @param Mage_Sales_Model_Order $order, String $gatewayMessage
	 *
	 */
	public function updateToRejected($order, $gatewayMessage)
	{
		$order->setState(Mage_Sales_Model_Order::STATE_CANCELED, true,
			sprintf('Todas as tentativas de pagamento foram rejeitadas. Motivo: "%s".',
			$gatewayMessage), true);

		$order->save();
	}

	/**
	 * Adiciona no pedido uma mensagem de falha no pagamento
	 *
	 * @param Mage_Sales_Model_Order $order, String $gatewayMessage
	 *
	 */
	public function addRetryStatusMessage($order, $gatewayMessage)
	{
		$order->addStatusHistoryComment(sprintf(
			'Tentativa de Pagamento rejeitada. Motivo: "%s". Uma nova tentativa será feita.',
			$gatewayMessage));

		$order->save();
	}

	/**
	 * Consulta via API uma fatura vindi através do ID
	 * Esse se faz necessário, pois os Webhooks de 'charge' não retornam os dados da fatura
	 *
	 * @param int $billId
	 *
	 * @return bool|Mage_Sales_Model_Order
	 */
	public function getOrderFromVindi($billId)
	{
		/** @var Vindi_Subscription_Helper_API $api */
		$api = Mage::helper('vindi_subscription/api');
		$bill = $api->getBill($billId);

		if (! $bill) {
			return false;
		}
		return $this->getOrder(compact('bill'));
	}

	/**
	 * Valida a existência do pedido (fatura/assinatura) com os parâmetros informados
	 *
	 * @param array $data
	 *
	 * @return Mage_Sales_Model_Order|bool
	 */
	public function getOrder($data)
	{
		if (! isset($data['bill'])) {
			return false;
		}

		$orderCode = filter_var($data['bill']['subscription']['id'], FILTER_SANITIZE_NUMBER_INT);
		if (isset($data['bill']['subscription']['id']) && $orderCode) {
			$orderType = 'assinatura';
			$order = $this->getOrderFromMagento($orderType,
				$orderCode, $data['bill']['period']['cycle']);
		}
		else {
			$orderCode = filter_var($data['bill']['id'], FILTER_SANITIZE_NUMBER_INT);
			$orderType = 'fatura';
			$order = $this->getOrderFromMagento($orderType, $orderCode);
		}

		if (! $order || ! $order->getId()) {
			$this->logWebhook(sprintf('Pedido não encontrado para a "%s": %d.', $orderType,
				$orderCode));
			return false;
		}
		return $order;
	}

	/**
	 * Tenta criar um 'fatura' no Magento
	 * Uma invoice registra o histórico de tentativas e pagamentos em um pedido
	 *
	 * @param Mage_Sales_Model_Order $order
	 *
	 * @return bool
	 */
	public function createInvoice($order, $data)
	{
		$orderId = $order->getId();
		if ($orderId && $order->canInvoice()) {
			$this->logWebhook('Gerando fatura para o pedido: ' . $orderId);
			$this->updateToSuccess($order);
			$paymentMethod = new Vindi_Subscription_Model_PaymentMethod();
			$paymentMethod->processPaidReturn($data['bill'], $order);
			$this->logWebhook('Fatura gerada com sucesso.');
			return true;
		}
		elseif ($orderId) { 
			$this->logWebhook('Impossível gerar fatura para o pedido ' . $orderId, 4);
		}
		return false;
	}

	/**
	 * Atualiza o status de uma 'fatura' no Magento para processando (pago)
	 *
	 * @param Mage_Sales_Model_Order $order
	 */
	public function updateToSuccess($order)
	{
		$invoice = $order->prepareInvoice();
		$invoice->setRequestedCaptureCase(Mage_Sales_Model_Order_Invoice::CAPTURE_OFFLINE);
		$invoice->setBaseGrandTotal($invoice->getGrandTotal());
		$invoice->register();

		Mage::getModel('core/resource_transaction')
			->addObject($invoice)
			->addObject($invoice->getOrder())
			->save();
		$invoice->sendEmail(true);
		$order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, true,
			'O pagamento foi confirmado e o pedido está sendo processado.', true);
		$order->save();
	}

	/**
	 * Busca um pedido referente a uma fatura ou assinatura Vindi
	 *
	 * @param int $billId
	 *
	 * @return Mage_Sales_Model_Order
	 */
	public function getOrderFromMagento($type, $vindiId, $subscriptionPeriod = null)
	{
		if ($type == 'fatura') {
			return Mage::getModel('sales/order')
			    ->getCollection()
				->addAttributeToSelect('*')
				->addFieldToFilter('vindi_bill_id', $vindiId)
				->getFirstItem();
		}
		$orders = Mage::getModel('sales/order')
			->getCollection()
			->addAttributeToSelect('*')
			->addFieldToFilter('vindi_subscription_id', $vindiId);

		$lastPeriod = $orders->addFieldToFilter('vindi_subscription_period', $subscriptionPeriod)
			->getFirstItem();

		if ($lastPeriod->getData()) {
			return $lastPeriod;
		}

		$this->logWebhook("Pedido não encontrado para o ciclo: $subscriptionPeriod
			da Assinatura: $vindiId", 4);

		return $orders->getFirstItem();
	}

	/**
	 * Sincroniza os itens da fatura Vindi com os itens do pedido Magento
	 *
	 * @param Mage_Sales_Model_Order $order, array $vindiData
	 */
	public function updateProductsList($order, $vindiData)
	{
		$codes = [];
		foreach ($vindiData['products'] as $product) {
			$codes[] = $product['product']['code'];
		}

		$itens = $order->getAllItems();
		foreach ($itens as $item) {
			if (! in_array($item->getSku(), $codes)) {
				$item->delete();
				$order->setTotalItemCount(count($itens) - 1);
				$order->setSubtotal($order->getSubtotal() - $item->getPrice());
				$order->save();
			}
		}
	}

	/*
	 * Atualiza o pedido inserindo as informações refentes a renovação de assinatura Vindi
	 * 
	 * @param Mage_Sales_Model_Order $order, array $vindiData, array $charges
	 *
	 * @return bool
	 */
	public function renewalOrder($order, $vindiData, $charges)
	{
		$order->setVindiSubscriptionId($vindiData['bill']['subscription']);
		$order->setVindiSubscriptionPeriod($vindiData['bill']['cycle']);
		$order->setBaseGrandTotal($vindiData['bill']['amount']);
		$order->setGrandTotal($vindiData['bill']['amount']);
		$order->save();

		if (Mage::getStoreConfig('vindi_subscription/general/bankslip_link_in_order_comment')) {
			foreach ($charges as $charge) {
				if ($charge['payment_method']['type'] == 'PaymentMethod::BankSlip') {
					$order->addStatusHistoryComment(sprintf(
						'<a target="_blank" href="%s">Clique aqui</a> para visualizar o boleto.',
						$charge['print_url']
					))->setIsVisibleOnFront(true);
					$order->save();
				}
			}
		}
		$this->logWebhook(sprintf('Novo pedido gerado: %s.', $order->getId()));
		return true;
	}

	/*
	 * Carrega os dados e valores referentes ao Frete do pedido
	 *
	 * @param Mage_Sales_Model_Quote $quote, array $vindiData, Mage_Sales_Model_Quote $shippingMethod 
	 */
	private function loadShipping($quote, $vindiData, $shippingMethod)
	{
		// Carrega todos os métodos de entrega
		$shippingMethods = Mage::getSingleton('vindi_subscription/config_shippingmethod')
			->getActivedShippingMethodsValues();

		// Verifica se o método recebido está ativo
		if (! in_array($shippingMethod, $shippingMethods)) {
			$oldShippingMethod = $shippingMethod;
			$shippingMethod = Mage::getStoreConfig(
				'vindi_subscription/general/default_shipping_method');
			$this->logWebhook(sprintf(
				"Erro ao utilizar o método de envio %s alterado para o método padrão %s.",
				$oldShippingMethod, $shippingMethod));
			unset($oldShippingMethod);
		}

		// Carrega os valores padrões do método de entrega
		$quote->getShippingAddress()
			->setShippingMethod($shippingMethod)
			->setCollectShippingRates(true)
			->collectShippingRates()
			->collectTotals();

		if (isset($vindiData['shipping']['pricing_schema']['price'])
			&& ! empty($vindiData['shipping']['pricing_schema']['price'])) {

			// Seta o novo valor do frete
			$billShippingPrice = $vindiData['shipping']['pricing_schema']['price'];

			$quote->setPrice($billShippingPrice)
				->setCost($billShippingPrice);

			$address = $quote->getShippingAddress();
			$address->setShippingAmount($billShippingPrice);
			$address->setBaseShippingAmount($billShippingPrice);

			$rates = $address->collectShippingRates()
				->getGroupedAllShippingRates();

			foreach ($rates as $carrier) {
				foreach ($carrier as $rate) {
					$rate->setPrice($billShippingPrice);
					$rate->save();
				}
			}
			$address->save();
		}
		$quote->save();
	}

	/*
	 * Carrega os dados e valores referentes as Taxas do pedido
	 *
	 * @param Mage_Sales_Model_Quote $quote, array $vindiData
	 */
	private function loadTaxes($quote, $vindiData)
	{
		if (isset(reset($vindiData['taxes'])['pricing_schema']['price'])
			&& ! empty(reset($vindiData['taxes'])['pricing_schema']['price'])) {
			$quote->getShippingAddress()->setTaxAmount(
				reset($vindiData['taxes'])['pricing_schema']['price']);
		}

		$quote->collectTotals()
			->save();
	}

	/*
	 * Carrega os dados e valores referentes aos Produtos do pedido
	 *
	 * @param Mage_Sales_Model_Quote $quote, array $vindiData
	 */
	private function loadProducts($quote, $vindiData)
	{
		foreach ($vindiData['products'] as $item) {
			$magentoProduct = Mage::getModel('catalog/product')
				->loadByAttribute('vindi_product_id', $item['product']['id']);

			if (! $magentoProduct) {
				$this->logWebhook(sprintf('O produto com ID Vindi #%s não existe no Magento.',
					$item['product']['id']), 5);
			}
			elseif (number_format($magentoProduct->getPrice(), 2)
				!== number_format($item['pricing_schema']['price'], 2)) {

				$this->logWebhook(sprintf("Divergencia de valores na fatura #%s:  " . 
					"produto %s: ID Magento #%s , ID Vindi #%s: " . 
					"Valor Magento R$ %s , Valor Vindi R$ %s",
					$vindiData['bill']['id'],
					$magentoProduct->getName(),
					$magentoProduct->getId(),
					$item['product']['id'],
					$magentoProduct->getPrice(),
					$item['pricing_schema']['price']));

				$quote->getItemByProduct($magentoProduct)
					->setOriginalCustomPrice($item['pricing_schema']['price'])
					->setCustomPrice($item['pricing_schema']['price'])
					->save();
			}
		}
		$quote->setTotalsCollectedFlag(false)
			->collectTotals()
			->save();
	}

	/**
	 * Cria um novo pedido no Magento utilizando a função 're-order'
	 *
	 * @param Mage_Sales_Model_Order $oldOrder, array $vindiData
	 *
	 * @return Mage_Sales_Model_Order|bool
	 */
	public function createOrder($oldOrder, $vindiData)
	{
		$oldOrder->setReordered(true);

		$model = Mage::getSingleton('adminhtml/sales_order_create');

		/** @var Mage_Adminhtml_Model_Sales_Order_Create $order */
		$order = $model->initFromOrder($oldOrder);

		$quote = $order->getQuote();

		// Add Shipping values
		$this->loadShipping($quote, $vindiData, $oldOrder->getShippingMethod());

		// Add Product values
		$this->loadProducts($quote, $vindiData);

		// Add Tax values
		$this->loadTaxes($quote, $vindiData);

		$session = Mage::getSingleton('core/session');
		$session->setData('vindi_is_reorder', true);

		try {
			$order = $order->createOrder();
		}
		catch (Exception $e) {
			$this->logWebhook("Erro ao criar pedido!");

			if ($e->getMessage()) {
				$this->logWebhook($e->getMessage(), 5);
				return false;
			}

			$messages = $order->getSession()->getMessages(true);
			foreach ($messages->getItems() as $message) {
				$this->logWebhook($message->getText(), 5);
			}
			return false;
		}
		return $order;
	}
}
