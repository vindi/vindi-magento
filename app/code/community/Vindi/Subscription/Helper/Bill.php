<?php

class Bill
{
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
            $this->logger->log('Erro ao interpretar webhook "bill_created".', 5);

            return false;
        }

        if (! isset($bill['subscription']) || is_null($bill['subscription'])) {
            $this->logger->log(sprintf('Ignorando o evento "bill_created" para venda avulsa.'), 5);

            return false;
        }

        $period = intval($bill['period']['cycle']);

        if (isset($bill['period']) && ($period === 1)) {
            $this->logger->log(sprintf('Ignorando o evento "bill_created" para o primeiro ciclo.'), 5);

            return false;
        }

        if (($order = $this->getOrder($data))) {
            $this->logger->log(sprintf('Já existe o pedido %s para o evento "bill_created".', $order->getId()), 5);

            return false;
        }

        $subscriptionId = $bill['subscription']['id'];
        $lastPeriodOrder = $this->getOrderForPeriod($subscriptionId, $period - 1);

        if (! $lastPeriodOrder || ! $lastPeriodOrder->getId()) {
            $this->logger->log('Pedido anterior não encontrado. Ignorando evento.', 4);

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
            $this->logger->log('Impossível gerar novo pedido!', 4);

            return false;
        }

        $this->logger->log(sprintf('Novo pedido gerado: %s.', $order->getId()));

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
            $this->logger->log(sprintf('Ainda não existe um pedido para ciclo %s da assinatura: %d.',
                $data['bill']['period']['cycle'],
                $data['bill']['subscription']['id']),
                4
            );

            return false;
        }

        return $this->createInvoice($order);
    }
}