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
                return $this->billHandler->billCreated($data);
            case 'bill_paid':
                return $this->billHandler->billPaid($data);
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

        if (! ($order = $this->orderHandler->getOrderFromBill($charge['bill']['id']))) {
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
            $this->orderHandler->addStatusMessage($order, $gatewayMessage);
            $this->logger->log(sprintf(
                'Tentativa de pagamento do pedido %s foi rejeitada. Motivo: "%s".' .
                ' Uma nova tentativa será feita.', $order->getId(), $gatewayMessage));
        }
        return true;
    }
}
