<?php

class Vindi_Subscription_Helper_Bill
{
    /**
     * Handle 'bill_created' event.
     * The bill can be related to a subscription or a single payment.
     *
     * @param array $data
     *
     * @return bool
     */
    public function processBillCreated($data)
    {
        $bill = $data['bill'];
        $vindiData = $this->loadBillData($data);
        $lastOrder = $this->getLastPeriod($data);

        $order = $this->orderHandler->createOrder($lastOrder, $vindiData);

        // remove inactive products
        $this->orderHandler->updateProductsList($order, $vindiData, $bill['charges']);

        if (!$order) {
            $this->logger->log('Impossível gerar novo pedido!', 4);
            return false;
        }
        return $this->orderHandler->renewalOrder($order, $vindiData);
    }

    public function getLastPeriod($bill)
    {
        $currentPeriod = $bill['period']['cycle'];
        $subscriptionId = $bill['subscription']['id'];
        return $this->orderHandler->getSubscriptionOrder($subscriptionId, $currentPeriod - 1);
    }

    public function loadBillData($data)
    {
        $vindiData = [
            'bill'     => [
                'id'           => $data['bill']['id'],
                'amount'       => $data['bill']['amount'],
                'subscription' => $data['bill']['subscription']['id'],
                'cycle'        => $data['bill']['period']['cycle']
            ],
            'products' => [],
            'shipping' => [],
            'taxes'    => [],
        ];

        foreach ($data['bill']['bill_items'] as $billItem) {
            if ($billItem['product']['code'] == 'frete') {
                $vindiData['shipping'] = $billItem;
            }
            elseif ($billItem['product']['code'] == 'taxa') {
                $vindiData['taxes'][] = $billItem;
            }
            else {
                $vindiData['products'][] = $billItem;
            }
        }
        return $vindiData;
    }

    /**
     * Handle 'bill_paid' event.
     * The bill can be related to a subscription or a single payment.
     *
     * @param array $data
     *
     * @return bool
     */
    public function processBillPaid($data)
    {
        if (! ($order = $this->orderHandler->getOrder($data))) {
            $this->logger->log(sprintf(
                'Ainda não existe um pedido para ciclo %s da assinatura: %d.',
                $data['bill']['period']['cycle'], $data['bill']['subscription']['id']), 4);
            return false;
        }
        return $this->orderHandler->createInvoice($order);
    }
}
