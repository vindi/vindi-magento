<?php

class Vindi_Subscription_Helper_WebhookHandler extends Mage_Core_Helper_Abstract
{
    
    protected $logger;
    protected $billHandler;
    protected $orderHandler;
    
    public function __construct() {
        $this->logger = Mage::helper('vindi_subscription/logger');
        $this->billHandler = Mage::helper('vindi_subscription/bill');
        $this->orderHandler = Mage::helper('vindi_subscription/order');
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
            $this->logger->log('Falha ao interpretar JSON do webhook: ' . $e->getMessage(), 5);
            return false;
        }

        switch ($type) {
            // the webhook is being called before Order is actually placed.
            // I'm sorry for this, not going to use queues for now, so the solution is to use sleep().

            case 'test':
                $this->logger->log('Evento de teste do webhook.');
                return false;
            case 'bill_created':
                return $this->billHandler->processBillCreated($data);
            case 'bill_paid':
                return $this->billHandler->processBillPaid($data);
            case 'charge_rejected':
                return $this->validateChargeWebhook($data);
            default:
                $this->logger->log('Evento do webhook ignorado pelo plugin: ' . $type);
                return true;
        }
    }

    /**
     * @param array $data
     *
     * @return bool
     */
    public function validateChargeWebhook($data)
    {
        $charge = $data['charge'];

        if (! ($order = $this->orderHandler->getOrderFromVindi($charge['bill']['id']))) {
            $this->logger->log('Pedido não encontrado.', 4);
            return false;
        }

        $gatewayMessage = $charge['last_transaction']['gateway_message'];

        if (is_null($charge['next_attempt'])) {
            $this->orderHandler->updateToRejected($order, $gatewayMessage);
            $this->logger->log(sprintf(
                'Todas as tentativas de pagamento do pedido %s foram rejeitadas. Motivo: "%s".',
                $order->getId(), $gatewayMessage));
        } else {
            $this->orderHandler->addRetryStatusMessage($order, $gatewayMessage);
            $this->logger->log(sprintf(
                'Tentativa de pagamento do pedido %s foi rejeitada. Motivo: "%s".' .
                ' Uma nova tentativa será feita.', $order->getId(), $gatewayMessage));
        }
        return true;
    }

    public function validBillCreatedWebhook($data)
    {
        $bill = $data['bill'];
        $valid = true;
        if (!$bill) {
            $this->logger->log('Erro ao interpretar webhook "bill_created".', 5);
            $valid = false;
        }
        elseif (!isset($bill['subscription']) || is_null($bill['subscription'])) {
            $this->logger->log(sprintf('Ignorando o evento "bill_created" para venda avulsa.'), 5);
            $valid = false;
        }
        elseif (isset($bill['period']) && ($bill['period']['cycle'] === 1)) {
            $this->logger->log(sprintf('Ignorando o evento "bill_created" para o primeiro ciclo.'), 5);
            $valid = false;
        }
        elseif (($order = $this->getOrder($data))) {
            $this->logger->log(sprintf('Já existe o pedido %s para o evento "bill_created".', $order->getId()), 5);
            $valid = false;
        }
        elseif (isset($subscriptionId = $bill['subscription']['id']) && ($period = $bill['period']['cycle'])) {
            $lastPeriodOrder = $this->billHandler->getLastPeriod($data);
            if (! $lastPeriodOrder || ! $lastPeriodOrder->getId()) {
                $this->logger->log('Pedido anterior não encontrado. Ignorando evento.', 4);
                $valid = false;
            }
        }
        return $valid;
    }
}
