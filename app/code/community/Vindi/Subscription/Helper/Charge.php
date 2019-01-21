<?php

class Charge
{
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
            $this->logger->log('Pedido não encontrado.', 4);

            return false;
        }

        $gatewayMessage = $charge['last_transaction']['gateway_message'];
        $isLastAttempt = is_null($charge['next_attempt']);

        if ($isLastAttempt) {
            $order->setState(Mage_Sales_Model_Order::STATE_CANCELED, true, sprintf(
                'Todas as tentativas de pagamento foram rejeitadas. Motivo: "%s".',
                $gatewayMessage
            ), true);
            $this->logger->log(sprintf(
                'Todas as tentativas de pagamento do pedido %s foram rejeitadas. Motivo: "%s".',
                $order->getId(),
                $gatewayMessage
            ));
        } else {
            $order->addStatusHistoryComment(sprintf(
                'Tentativa de Pagamento rejeitada. Motivo: "%s". Uma nova tentativa será feita.',
                $gatewayMessage
            ));
            $this->logger->log(sprintf(
                'Tentativa de pagamento do pedido %s foi rejeitada. Motivo: "%s". Uma nova tentativa será feita.',
                $order->getId(),
                $gatewayMessage
            ));
        }

        $order->save();

        return true;
    }
}