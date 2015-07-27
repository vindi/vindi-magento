<?php

trait Vindi_Subscription_Trait_PaymentMethod
{

    /**
     * @param string $paymentAction
     * @param object $stateObject
     *
     * @return bool|Mage_Payment_Model_Method_Abstract
     */
    public function initialize($paymentAction, $stateObject)
    {
        if ($this->checkForReorder()) {
            return $this->processReorder($paymentAction, $stateObject);
        }

        return $this->processNewOrder($paymentAction, $stateObject);
    }

    /**
     * @return bool
     */
    protected function checkForReorder()
    {
        $session = Mage::getSingleton('core/session');
        $isReorder = $session->getData('vindi_is_reorder', false);
        $session->unsetData('vindi_is_reorder');

        return $isReorder;
    }

    /**
     * @param Mage_Sales_Model_Order       $order
     * @param Mage_Customer_Model_Customer $customer
     *
     * @return bool|int|null
     */
    protected function createCustomer($order, $customer)
    {
        $billing = $order->getBillingAddress();

        $customer->setWebsiteId(Mage::app()->getWebsite()->getId());
        $customer->loadByEmail($billing->getEmail());
        //TODO fix user being created again if validation fails
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
     * @param string $paymentAction
     * @param object $stateObject
     *
     * @return bool|Mage_Payment_Model_Method_Abstract
     */
    protected function processReorder($paymentAction, $stateObject)
    {
        $payment = $this->getInfoInstance();
        $order = $payment->getOrder();

        $payment->setAmount($order->getTotalDue());
        $this->setStore($order->getStoreId());

        $payment->setStatus(Mage_Sales_Model_Order::STATE_PAYMENT_REVIEW, Mage_Sales_Model_Order::STATE_PAYMENT_REVIEW,
            'Novo período da assinatura criado', true);
        $stateObject->setStatus(Mage_Sales_Model_Order::STATE_PAYMENT_REVIEW)
            ->setState(Mage_Sales_Model_Order::STATE_PAYMENT_REVIEW);

        return $this;
    }

    /**
     * @param Mage_Payment_Model_Method_Abstract $payment
     * @param Mage_Sales_Model_Order             $order
     * @param int                                $customerId
     *
     * @return bool
     * @throws \Mage_Core_Exception
     */
    protected function processSubscription($payment, $order, $customerId)
    {
        $subscription = $this->createSubscription($order, $customerId);

        if ($subscription === false) {
            Mage::throwException('Erro ao criar a assinatura. Verifique os dados e tente novamente!');

            return false;
        }

        $payment->setAmount($order->getTotalDue());
        $this->setStore($order->getStoreId());

        $payment->setStatus(Mage_Sales_Model_Order::STATE_PAYMENT_REVIEW, Mage_Sales_Model_Order::STATE_PAYMENT_REVIEW,
            'Assinatura criada', true);

        return true;
    }

    /**
     * @param Mage_Payment_Model_Method_Abstract $payment
     * @param Mage_Sales_Model_Order             $order
     * @param int                                $customerId
     *
     * @return bool
     * @throws \Mage_Core_Exception
     */
    protected function processSinglePayment($payment, $order, $customerId)
    {
        $uniquePaymentProduct = $this->api()->findOrCreateUniquePaymentProduct();

        $this->log(sprintf('Produto para pagamento único: %d.', $uniquePaymentProduct));

        $body = [
            'customer_id'         => $customerId,
            'payment_method_code' => $this->getPaymentMethodCode(),
            'bill_items'          => [
                [
                    'product_id' => $uniquePaymentProduct,
                    'amount'     => $order->getGrandTotal(),
                ],
            ],
        ];

        //TODO add installments option

        $billId = $this->api()->createBill($body);

        if (! $billId) {
            $this->log(sprintf('Erro no pagamento do pedido %d.', $order->getId()));

            $message = sprintf('Pagamento Falhou. (%s)', $this->api()->lastError);
            $payment->setStatus(
                Mage_Sales_Model_Order::STATE_CANCELED,
                Mage_Sales_Model_Order::STATE_CANCELED,
                $message,
                true
            );

            Mage::throwException($message);

            return false;
        }

        $order->setVindiBillId($billId);
        $order->save();

        return $billId;
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
            'code'                => $order->getIncrementId(),
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

        $order->setVindiSubscriptionId($subscription['id']);
        $order->setVindiSubscriptionPeriod(1);
        $order->save();

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

    /*
     * @param Mage_Sales_Model_Order $order
     */
    protected function isSingleOrder($order)
    {
        foreach ($order->getAllVisibleItems() as $item) {
            if (($product = $item->getProduct()) && ($product->getTypeId() === 'subscription')) {
                return false;
            }
        }

        return true;
    }
}