<?php

class Vindi_Subscription_Helper_WebhookHandler extends Mage_Core_Helper_Abstract
{
    /**
     * @param string   $message
     * @param int|null $level
     */
    public function log($message, $level = null)
    {
        Mage::log($message, $level, 'vindi_webhooks.log');

        switch ($level) {
            case 4:
                http_response_code(422);
                return false;
                break;
            case 5:
                return false;
                break;
            default:
                return true;
                break;
        }

    }

    /**
     * Handle incoming webhook.
     *
     * @param string $body
     *
     * @return bool
     */
    public function handle($body)
    {
        try {
            $jsonBody = json_decode($body, true);

            if (! $jsonBody || ! isset($jsonBody['event'])) {
                Mage::throwException('Evento do Webhook não encontrado!');
            }

            $type = $jsonBody['event']['type'];
            $data = $jsonBody['event']['data'];
        } catch (Exception $e) {
            $this->log(sprintf('Falha ao interpretar JSON do webhook: %s', $e->getMessage()), 5);
            return false;
        }

        switch ($type) {
            // the webhook is being called before Order is actually placed.
            // I'm sorry for this, not going to use queues for now, so the solution is to use sleep().

            case 'test':
                $this->log('Evento de teste do webhook.');
                return false;
            case 'bill_created':
                return $this->billCreated($data);
            case 'bill_paid':
                return $this->billPaid($data);
            case 'charge_rejected':
                return $this->chargeRejected($data);
            default:
                $this->log(sprintf('Evento do webhook ignorado pelo plugin: "%s".', $type), 5);
        }
    }

    /**
     * Handle 'bill_created' event.
     * The bill can be related to a subscription or a single payment.
     *
     * @param array $data
     *
     * @return bool
     */
    public function billCreated($data)
    {
        if (! ($bill = $data['bill'])) {
            $this->log('Erro ao interpretar webhook "bill_created".', 5);

            return false;
        }

        if (! isset($bill['subscription']) || is_null($bill['subscription'])) {
            $this->log(sprintf('Ignorando o evento "bill_created" para venda avulsa.'), 5);

            return false;
        }

        $period = intval($bill['period']['cycle']);

        if (isset($bill['period']) && ($period === 1)) {
            $this->log(sprintf('Ignorando o evento "bill_created" para o primeiro ciclo.'), 5);

            return false;
        }

        if (($order = $this->getOrder($data))) {
            $this->log(sprintf('Já existe o pedido %s para o evento "bill_created".', $order->getId()), 5);

            return false;
        }

        $subscriptionId = $bill['subscription']['id'];
        $lastPeriodOrder = $this->getOrderForPeriod($subscriptionId, $period - 1);

        if (! $lastPeriodOrder || ! $lastPeriodOrder->getId()) {
            $this->log('Pedido anterior não encontrado. Ignorando evento.', 4);

            return false;
        }

        $vindiData = [
                    'bill'     => [
                                    'id'    => $data['bill']['id'],
                                    'amount' => $data['bill']['amount']
                                ],
                    'products' => [],
                    'shipping' => [],
                    'taxes'    => [],
                ];
        foreach ($data['bill']['bill_items'] as $billItem) {
            if ($billItem['product']['code'] == 'frete') {
                $vindiData['shipping'] = $billItem;
            } elseif ($billItem['product']['code'] == 'taxa') {
                $vindiData['taxes'][] = $billItem;
            } else {
                $vindiData['products'][] = $billItem;
            }
        }

        $order = $this->createOrder($lastPeriodOrder, $vindiData);

        // remove inactive products
        $this->updateProductsList($order, $vindiData);

        if (! $order) {
            $this->log('Impossível gerar novo pedido!', 4);

            return false;
        }

        $this->log(sprintf('Novo pedido gerado: %s.', $order->getId()));

        $order->setVindiSubscriptionId($subscriptionId);
        $order->setVindiSubscriptionPeriod($period);
        $order->setBaseGrandTotal($vindiData['bill']['amount']);
        $order->setGrandTotal($vindiData['bill']['amount']);
        $order->save();

        if(Mage::getStoreConfig('vindi_subscription/general/bankslip_link_in_order_comment'))
        {
            foreach ($data['bill']['charges'] as $charge)
            {
                if ($charge['payment_method']['type'] == 'PaymentMethod::BankSlip')
                {
                    $order->addStatusHistoryComment(sprintf(
                        '<a target="_blank" href="%s">Clique aqui</a> para visualizar o boleto.',
                        $charge['print_url']
                    ))
                    ->setIsVisibleOnFront(true);
                    $order->save();
                }
            }
        }

        return true;
    }

    /**
     * Handle 'bill_paid' event.
     * The bill can be related to a subscription or a single payment.
     *
     * @param array $data
     *
     * @return bool
     */
    public function billPaid($data)
    {
        if (! ($order = $this->getOrder($data))) {
            $this->log(sprintf('Ainda não existe um pedido para ciclo %s da assinatura: %d.',
                $data['bill']['period']['cycle'],
                $data['bill']['subscription']['id']),
                4
            );

            return false;
        }

        return $this->createInvoice($order);
    }

    /**
     * @param array $data
     *
     * @return bool
     * @throws \Exception
     */
    public function chargeRejected($data)
    {
        $charge = $data['charge'];

        if (! ($order = $this->getOrderFromBill($charge['bill']['id']))) {
            $this->log('Pedido não encontrado.', 4);

            return false;
        }

        $gatewayMessage = $charge['last_transaction']['gateway_message'];
        $isLastAttempt = is_null($charge['next_attempt']);

        if ($isLastAttempt) {
            $order->setState(Mage_Sales_Model_Order::STATE_CANCELED, true, sprintf(
                'Todas as tentativas de pagamento foram rejeitadas. Motivo: "%s".',
                $gatewayMessage
            ), true);
            $this->log(sprintf(
                'Todas as tentativas de pagamento do pedido %s foram rejeitadas. Motivo: "%s".',
                $order->getId(),
                $gatewayMessage
            ));
        } else {
            $order->addStatusHistoryComment(sprintf(
                'Tentativa de Pagamento rejeitada. Motivo: "%s". Uma nova tentativa será feita.',
                $gatewayMessage
            ));
            $this->log(sprintf(
                'Tentativa de pagamento do pedido %s foi rejeitada. Motivo: "%s". Uma nova tentativa será feita.',
                $order->getId(),
                $gatewayMessage
            ));
        }

        $order->save();

        return true;
    }

    /**
     * @param Mage_Sales_Model_Order $order
     *
     * @return bool
     */
    public function createInvoice($order)
    {
        if (! $order->getId()) {
            return false;
        }

        $this->log(sprintf('Gerando fatura para o pedido: %s.', $order->getId()));

        $order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, true,
            'O pagamento foi confirmado e o pedido está sendo processado.', true);

        if (! $order->canInvoice()) {
            $this->log(sprintf('Impossível gerar fatura para o pedido %s.', $order->getId()), 4);

            // TODO define how to handle this

            return false;
        }
        $invoice = $order->prepareInvoice();
        $invoice->setRequestedCaptureCase(Mage_Sales_Model_Order_Invoice::CAPTURE_OFFLINE);
        $invoice->register();
        Mage::getModel('core/resource_transaction')->addObject($invoice)->addObject($invoice->getOrder())->save();
        $invoice->sendEmail(true);
        $this->log('Fatura gerada com sucesso.');

        return true;
    }

    /**
     * @param array $data
     *
     * @return Mage_Sales_Model_Order|bool
     */
    private function getOrder($data)
    {
        if (! isset($data['bill'])) {
            return false;
        }

        if (isset($data['bill']['subscription']) && ($subscription = $data['bill']['subscription'])
            && ($subscriptionId = filter_var($subscription['id'], FILTER_SANITIZE_NUMBER_INT))
        ) {
            $order = $this->getOrderForPeriod($subscriptionId, $data['bill']['period']['cycle']);

            if (! $order || ! $order->getId()) {
                $this->log(sprintf('Nenhum pedido encontrado para a assinatura: %d.', $subscriptionId));

                return false;
            }

            return $order;
        } else {
            $order = $this->getSingleOrder($data['bill']['id']);

            if (! $order || ! $order->getId()) {
                $this->log(sprintf('Nenhum pedido encontrado para a fatura: %d.', $data['bill']['id']));

                return false;
            }

            return $order;
        }
    }

    /**
     * @param int $billId
     *
     * @return bool|\Mage_Sales_Model_Order
     */
    private function getOrderFromBill($billId)
    {
        /** @var Vindi_Subscription_Helper_API $api */
        $api = Mage::helper('vindi_subscription/api');

        if (! $bill = $api->getBill($billId)) {
            return false;
        }

        return $this->getOrder(compact('bill'));
    }

    /**
     * @param int $subscriptionId
     * @param int $subscriptionPeriod
     *
     * @return Mage_Sales_Model_Order
     */
    private function getOrderForPeriod($subscriptionId, $subscriptionPeriod)
    {
        return Mage::getModel('sales/order')->getCollection()
            ->addAttributeToSelect('*')
            ->addFieldToFilter('vindi_subscription_id', $subscriptionId)
            ->addFieldToFilter('vindi_subscription_period', $subscriptionPeriod)
            ->getFirstItem();
    }

    /**
     * @param int $billId
     *
     * @return Mage_Sales_Model_Order
     */
    private function getSingleOrder($billId)
    {
        return Mage::getModel('sales/order')->getCollection()
            ->addAttributeToSelect('*')
            ->addFieldToFilter('vindi_bill_id', $billId)
            ->getFirstItem();
    }

    private function updateProductsList($order, $vindiData)
    {
        $codes = [];
        foreach ($vindiData['products'] as $product) {
            $codes[] = $product['product']['code'];
        }

        $itens = $order->getAllItems();
        foreach ($itens as $item) {
            if (!in_array($item->getSku(), $codes)) {
                $item->delete();
                $order->setTotalItemCount(count($items) - 1);
                $order->setSubtotal($order->getSubtotal() - $item->getPrice());
                $order->save();
            }
        }
    }

    /**
     * Create a new order from an old one using reorder functionality.
     *
     * @param Mage_Sales_Model_Order $oldOrder
     *
     * @return Mage_Sales_Model_Order;
     */
    private function createOrder($oldOrder, $vindiData)
    {
        $oldOrder->setReordered(true);

        $model = Mage::getSingleton('adminhtml/sales_order_create');

        /** @var Mage_Adminhtml_Model_Sales_Order_Create $order */
        $order = $model->initFromOrder($oldOrder);

        $quote = $order->getQuote();

        // get shipping method
        $shippingMethod = $oldOrder->getShippingMethod();
        $activedShippingMethods = Mage::getSingleton('vindi_subscription/config_shippingmethod')->getActivedShippingMethodsValues();

        // verify if current shipping method is active
        if(!in_array($shippingMethod, $activedShippingMethods)){
            $oldShippingMethod = $shippingMethod;
            $shippingMethod = Mage::getStoreConfig('vindi_subscription/general/default_shipping_method');
            $this->log(sprintf("Erro ao utilizar o método de envio %s alterado para o método padrão %s.",
                        $oldShippingMethod,
                        $shippingMethod
                    ));
            unset($oldShippingMethod);
        }

        // quote shipping method
        $quote->getShippingAddress()
            ->setShippingMethod($shippingMethod)
            ->setCollectShippingRates(true)
            ->collectShippingRates()
            ->collectTotals();

        if(isset($vindiData['shipping']['pricing_schema']['price']) && !empty($vindiData['shipping']['pricing_schema']['price']))
        {
            // set shipping price
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

        foreach ($vindiData['products'] as $item) {
            $magentoProduct = Mage::getModel('catalog/product')->loadByAttribute('vindi_product_id', $item['product']['id']);
            if(!$magentoProduct)
            {
                $this->log(sprintf('O produto com ID Vindi #%s não existe no Magento.', $item['product']['id']), 5);
            }else{
                if(number_format($magentoProduct->getPrice(), 2) !== number_format($item['pricing_schema']['price'], 2)){
                    $this->log(sprintf("Divergencia de valores na fatura #%s: produto %s: ID Magento #%s , ID Vindi #%s: Valor Magento R$ %s , Valor Vindi R$ %s",
                                $vindiData['bill']['id'],
                                $magentoProduct->getName(),
                                $magentoProduct->getId(),
                                $item['product']['id'],
                                $magentoProduct->getPrice(),
                                $item['pricing_schema']['price'])
                            );

                    $quote->getItemByProduct($magentoProduct)
                        // ->setPrice($item['pricing_schema']['price'])
                        // ->setCost($item['pricing_schema']['price'])
                        ->setOriginalCustomPrice($item['pricing_schema']['price'])
                        ->setCustomPrice($item['pricing_schema']['price'])
                        ->save();

                }
            }
        }

        $quote->setTotalsCollectedFlag(false)
            ->collectTotals()
            ->save();

        if (isset(reset($vindiData['taxes'])['pricing_schema']['price']) && !empty(reset($vindiData['taxes'])['pricing_schema']['price'])) {
                $quote->getShippingAddress()->setTaxAmount(reset($vindiData['taxes'])['pricing_schema']['price']);
        }

        $quote->collectTotals()
            ->save();

        $session = Mage::getSingleton('core/session');
        $session->setData('vindi_is_reorder', true);

        try {
            $order = $order->createOrder();
        } catch (Exception $e) {
            $this->log("Erro ao criar pedido!");

            if($e->getMessage()){
                $this->log($e->getMessage(), 5);
            }else{
                $messages = $order->getSession()->getMessages(true);
                foreach($messages->getItems() as $message)
                {
                   $this->log($message->getText(), 5);
                }
            }

            return false;
        }

        return $order;
    }
}
