<?php

trait Vindi_Subscription_Trait_PaymentMethod
{

    /**
     * @param Mage_Sales_Model_Order      $order
     * @param Mage_Customer_Model_Customer $customer
     *
     * @return bool|int|null
     */
    protected function createCustomer($order, $customer)
    {
        $billing = $order->getBillingAddress();

        $customer->setWebsiteId(Mage::app()->getWebsite()->getId());
        $customer->loadByEmail($billing->getEmail());

        if (! ($userCode = $customer->getVindiUserCode())) {
            $userCode = 'mag-' . $customer->getId() . '-' . time();

            $customer->setVindiUserCode($userCode);
            $customer->save();
        }

        $address = [
            'street'             => $billing->getStreet(1),
            'number'             => $billing->getStreet(2),
            'additional_details' => $billing->getStreet(3),
            'neighborhood'       => $billing->getStreet(4),
            'zipcode'            => $billing->getPostcode(),
            'city'               => $billing->getCity(),
            'state'              => $billing->getRegionCode(),
            'country'            => $billing->getCountry(),
        ];

        $customerVindi = [
            'name'          => $billing->getFirstname() . ' ' . $billing->getLastname(),
            'email'         => $order->getBillingAddress()->getEmail(),
            'registry_code' => $order->getData('customer_taxvat'),
            'code'          => $userCode,
            'address'       => $address,
        ];

        $customerId = $this->api()->findOrCreateCustomer($customerVindi);

        if ($customerId === false) {
            Mage::throwException('Falha ao registrar o usuário. Verifique os dados e tente novamente!');
        }

        return $customerId;
    }

    /**
     * @return Vindi_Subscription_Helper_API
     */
    protected function api()
    {
        if (isset($this->vindiApi)) {
            return $this->vindiApi;
        }

        return $this->vindiApi = Mage::helper('vindi_subscription/api');
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @param int                    $customerId
     *
     * @return bool
     */
    protected function createSubscription($order, $customerId)
    {
        $orderItems = $order->getItemsCollection();
        $item = $orderItems->getFirstItem();
        $product = Mage::getModel('catalog/product')->load($item->getProductId());

        // TODO change type to vindi_subscription
        if ($product->getTypeID() !== 'subscription') {
            Mage::throwException('Produto escolhido não é uma assinatura.');

            return false;
        }

        $plan = $product->getData('vindi_subscription_plan');

        $productItems = $this->api()->buildPlanItemsForSubscription($plan, $order->getGrandTotal());

        $body = [
            'customer_id'         => $customerId,
            'payment_method_code' => $this->getPaymentMethodCode(),
            'plan_id'             => $plan,
            'product_items'       => $productItems,
        ];

        $subscription = $this->api()->createSubscription($body);

        if (! isset($subscription['id']) || empty($subscription['id'])) {
            $message = sprintf('Pagamento Falhou. (%s)', $this->api()->lastError);
            $this->log(sprintf('Erro no pagamento do pedido %s.\n%s', $order->getId(), $message));

            Mage::throwException($message);

            // TODO update order status?
            return false;
        }

        return $subscription;
    }

    /**
     * @param string   $message
     * @param int|null $level
     */
    protected function log($message, $level = null)
    {
        Mage::log($message, $level, $this->_code . '.log');
    }
}