<?php

class Vindi_Subscription_Helper_Validator
{
    /**
     * Valida estrutura da cobrança
     *
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

    /**
     * Valida estrutura da fatura
     *
     * @param array $data
     *
     * @return bool
     */
    public function validateBillCreatedWebhook($data)
    {
        $bill = $data['bill'];

        if (! $bill) {
            $this->logger->log('Erro ao interpretar webhook "bill_created".', 5);
            return false;
        }

        if (! isset($bill['subscription']) || is_null($bill['subscription'])) {
            $this->logger->log(sprintf('Ignorando o evento "bill_created" para venda avulsa.'), 5);
            return false;
        }

        if (isset($bill['period']) && ($bill['period']['cycle'] === 1)) {
            $this->logger->log(sprintf(
                'Ignorando o evento "bill_created" para o primeiro ciclo.'), 5);
            return false;
        }

        $order = $this->orderHandler->getOrder($data)

        if ($order) {
            $this->logger->log(sprintf('Já existe o pedido %s para o evento "bill_created".',
                $order->getId()), 5);
            return false;
        }

        if (isset($bill['subscription']['id']) && ($bill['period']['cycle'])) {
            $lastPeriodOrder = $this->billHandler->getLastPeriod($data);

            if (! $lastPeriodOrder || ! $lastPeriodOrder->getId()) {
                $this->logger->log('Pedido anterior não encontrado. Ignorando evento.', 4);
                return false;
            }
            $this->billHandler->processBillCreated($data);
        }

        return true;
    }
}
