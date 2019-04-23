<?php

class Vindi_Subscription_Helper_Validator
{
	use Vindi_Subscription_Trait_LogMessenger; 

	protected $billHandler;
	protected $orderHandler;

	public function __construct() 
	{
		$this->billHandler  = Mage::helper('vindi_subscription/bill');
		$this->orderHandler = Mage::helper('vindi_subscription/order');
	}

	/**
	 * Valida estrutura do Webhook 'charge_rejected'
	 *
	 * @param array $data
	 *
	 * @return bool
	 */
	public function validateChargeWebhook($data)
	{
		$charge = $data['charge'];
		$vindiOrder = $this->orderHandler->getOrderFromVindi($charge['bill']['id']);
		$paymentMethod = reset($vindiOrder['bill']['charges'])['payment_method']['type'];

		# Ignora evento se for o primeiro ciclo de uma assinatura via cartão de crédito;
		# Com exceção de transações com suspeita de fraude, 
		# o Magento exclui o pedido caso o pagamento seja imediatamente rejeitado.
		# Desse modo, não é possível realizar alterações no pedido
		if ($paymentMethod == 'PaymentMethod::CreditCard'
			&& $vindiOrder['bill']['period']['cycle'] == 1) {
			$this->logWebhook('Ignorando evento "charge_rejected" para o primeiro ciclo.');
			return true;
		}

		$this->orderHandler->getOrder($vindiOrder);
		
		if (! $order) {
			$this->logWebhook('Pedido não encontrado.', 4);
			return false;
		}

		# Invalida evento se a cobrança já estiver paga
		if (! $order->canInvoice()) {
			$orderStatus = $order->getStatusLabel();
			$this->logWebhook('Evento não processado!');
			$this->logWebhook('O pedido possui o status: $orderStatus e ' .
				'a cobrança possui o status:' . $charge['status']);	
			return true;
		}

		$gatewayMessage = $charge['last_transaction']['gateway_message'];

		if (is_null($charge['next_attempt'])) {
			$this->orderHandler->updateToRejected($order, $gatewayMessage);
			$this->logWebhook(sprintf(
				'Todas as tentativas de pagamento do pedido %s foram rejeitadas. Motivo: "%s".',
				$order->getId(), $gatewayMessage));
			return true;
		}

		$this->orderHandler->addRetryStatusMessage($order, $gatewayMessage);
		$this->logWebhook(sprintf(
			'Tentativa de pagamento do pedido %s foi rejeitada. Motivo: "%s".' .
			' Uma nova tentativa será feita.', $order->getId(), $gatewayMessage));
		return true;
	}

	/**
	 * Valida estrutura do Webhook 'bill_created'
	 *
	 * @param array $data
	 *
	 * @return bool
	 */
	public function validateBillCreatedWebhook($data)
	{
		$bill = $data['bill'];

		if (! $bill) {
			$this->logWebhook('Erro ao interpretar webhook "bill_created".', 5);
			return false;
		}

		if (! isset($bill['subscription']) || is_null($bill['subscription'])) {
			$this->logWebhook(sprintf('Ignorando o evento "bill_created" para venda avulsa.'), 5);
			return true;
		}

		if (isset($bill['period']) && ($bill['period']['cycle'] === 1)) {
			$this->logWebhook(sprintf(
				'Ignorando o evento "bill_created" para o primeiro ciclo.'), 5);
			return true;
		}

		$order = $this->orderHandler->getOrder($data);

		if ($order) {
			$this->logWebhook(sprintf('Já existe o pedido %s para o evento "bill_created".',
				$order->getId()), 5);
			return true;
		}

		if (isset($bill['subscription']['id']) && $bill['period']['cycle']) {
			$this->billHandler->processBillCreated($data);
			return true;
		}
		$this->logWebhook('Pedido anterior não encontrado. Ignorando evento.', 4);
		return false;
	}

	/**
	 * Valida estrutura do Webhook 'bill_paid'
	 * A fatura pode estar relacionada a uma assinatura ou uma compra avulsa
	 *
	 * @param array $data
	 *
	 * @return bool
	 */
	public function validateBillPaidWebhook($data)
	{
		$billInfo = $this->getBillInfo($data['bill']);
		$order = $this->orderHandler->getOrderFromMagento(
			$billInfo['type'],
			$billInfo['id'],
			$billInfo['cycle']
		);

		if ($order)
			return $this->billHandler->processBillPaid($order, $data);

		if ($billInfo['type'] == 'assinatura') {
			$this->logWebhook(sprintf(
				'Ainda não existe um pedido para ciclo %s da assinatura: %d.',
				$billInfo['cycle'], $billInfo['id']), 4);
			return false;
		}
		$this->logWebhook(sprintf('Pedido não encontrado para a "fatura": %d.', $billInfo['id']));
		return false;
	}

	/**
	 * Carrega informações fatura paga (ID, Tipo, Ciclo)
	 *
	 * @param array $bill
	 *
	 * @return array
	 */
	public function getBillInfo($bill)
	{
		if (is_null($bill['subscription'])) {
			return array(
				'type' 	=> 'fatura',
				'id' 	=> $bill['id'],
				'cycle' => null
			);
		}
		return array(
			'type' 	=> 'assinatura',
			'id' 	=> $bill['subscription']['id'],
			'cycle' => $bill['period']['cycle']
		);
	}
}
