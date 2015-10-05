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
            $this->log(sprintf('Falha ao interpretar JSON do webhook: %s', $e->getMessage()));
            http_response_code(422);
            exit('0');
        }

        switch ($type) {
            // the webhook is being called before Order is actually placed.
            // I'm sorry for this, not going to use queues for now, so the solution is to use sleep().

            case 'test':
                $this->log('Evento de teste do webhook.');
                exit('1');
            case 'bill_created':
                return $this->billCreated($data);
            case 'bill_paid':
                sleep(10);

                return $this->billPaid($data);
            case 'charge_rejected':
                sleep(10);

                return $this->chargeRejected($data);
            default:
                $this->log(sprintf('Evento do webhook ignorado pelo plugin: "%s".', $type));
                exit('0');
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
            $this->log('Erro ao interpretar webhook "bill_created".');

            return false;
        }

        if (! isset($bill['subscription']) || is_null($bill['subscription'])) {
            $this->log(sprintf('Ignorando o evento "bill_created" para venda avulsa.'));

            return true;
        }

        $period = intval($bill['period']['cycle']);

        if (isset($bill['period']) && ($period === 1)) {
            $this->log(sprintf('Ignorando o evento "bill_created" para o primeiro ciclo.'));

            return true;
        }

        if (($order = $this->getOrder($data))) {
            $this->log(sprintf('Já existe o pedido %s para o evento "bill_created".', $order->getId()));

            return true;
        }

        $subscriptionId = $bill['subscription']['id'];
        $lastPeriodOrder = $this->getOrderForPeriod($subscriptionId, $period - 1);

        if (! $lastPeriodOrder || ! $lastPeriodOrder->getId()) {
            $this->log('Pedido anterior não encontrado. Ignorando evento.');

            return false;
        }

        $order = $this->createOrder($lastPeriodOrder);

        if (! $order) {
            $this->log('Impossível gerar novo pedido!');

            return false;
        }

        $this->log(sprintf('Novo pedido gerado: %s.', $order->getId()));

        $order->setVindiSubscriptionId($subscriptionId);
        $order->setVindiSubscriptionPeriod($period);
        $order->save();

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
            $this->log('Pedido não encontrado.');

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
            $this->log(sprintf('Impossível gerar fatura para o pedido %s.', $order->getId()));

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

    /**
     * Create a new order from an old one using reorder functionality.
     *
     * @param Mage_Sales_Model_Order $oldOrder
     *
     * @return Mage_Sales_Model_Order;
     */
    private function createOrder($oldOrder)
    {
        $oldOrder->setReordered(true);

        $model = Mage::getSingleton('adminhtml/sales_order_create');

        /** @var Mage_Adminhtml_Model_Sales_Order_Create $order */
        $order = $model->initFromOrder($oldOrder);

        $quote = $order->getQuote();

        $shippingMethod = $oldOrder->getShippingMethod();
        $activedShippingMethods = Mage::getSingleton('vindi_subscription/config_shippingmethod')->getActivedShippingMethodsValues();

        if(!in_array($shippingMethod, $activedShippingMethods)){
            $shippingMethod = Mage::getStoreConfig('vindi_subscription/general/default_shipping_method');
        }

        $quote->getShippingAddress()
            ->setShippingMethod($shippingMethod)
            ->setCollectShippingRates(true)
            ->collectShippingRates()
            ->collectTotals()
            ->save();

        $quote->setTotalsCollectedFlag(false)
            ->collectTotals()
            ->save();

        $session = Mage::getSingleton('core/session');
        $session->setData('vindi_is_reorder', true);

        try {
            $order = $order->createOrder();
        } catch (Exception $e) {
            $this->log("Erro ao criar pedido!");
            if($e->getMessage()){
                $this->log($e->getMessage());
                echo $e->getMessage();
            }else{
                $messages = $order->getSession()->getMessages(true);
                foreach($messages->getItems() as $message)
                {
                   $this->log($message->getText());
                   echo $message->getText();
                }
            }

            return false;
        }

        return $order;
    }
}