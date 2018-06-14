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
     * @param Mage_Customer_Model_Customer $customer
     *
     * @return bool|string
     * @throws \Mage_Core_Exception
     */
    protected function getCustomerTipoPessoa($customer)
    {
        $attribute = Mage::getSingleton('eav/config')->getAttribute('customer', 'tipopessoa');

        $tipopessoa = $customer->getTipopessoa();

        if ($attribute && $attribute->usesSource() && $tipopessoa) {
            return $attribute->getSource()->getOptionText($tipopessoa);
        }

        return false;
    }

    /**
     * @param array Customer phones $phone
     * @return array
     */
    public function format_phone($phone)
    {
        $phone = '55' . preg_replace('/^0|\D+/', '', $phone);

        switch(strlen($phone)) {
            case 12:
                $phone_type = 'landline';
                break;
            case 13:
                $phone_type = 'mobile';
                break;
        }

        if (isset($phone_type)) {
            return [[
                'phone_type' => $phone_type,
                'number'     => $phone
            ]];
        }
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

        if (Mage::app()->getStore()->isAdmin()) {
            $customer = Mage::getSingleton('adminhtml/session_quote')->getCustomer();
        } else {
            $customer->setWebsiteId(Mage::app()->getWebsite()->getId());
            $customer->loadByEmail($billing->getEmail());
        }

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
            'phones'        => $this->format_phone($order->getBillingAddress()->getTelephone()),
            'address'       => $address
        ];

        if (Mage::getStoreConfig('vindi_subscription/general/send_nfe_information')) {
            switch ($this->getCustomerTipoPessoa($customer)) {
                case "Física":
                    $customerVindi['metadata'] = [
                        'carteira_de_identidade' => $customer->getIe(),
                    ];
                    break;
                case "Jurídica":
                    $customerVindi['metadata'] = [
                        'inscricao_estadual' => $customer->getIe(),
                    ];
                    break;
            }
        }

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

        $payment->setStatus(Mage_Sales_Model_Order::STATE_PENDING_PAYMENT, Mage_Sales_Model_Order::STATE_PENDING_PAYMENT,
            'Novo período da assinatura criado', true);
        $stateObject->setStatus(Mage_Sales_Model_Order::STATE_PENDING_PAYMENT)
            ->setState(Mage_Sales_Model_Order::STATE_PENDING_PAYMENT);

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
        $subscription = $this->createSubscription($payment, $order, $customerId);

        if ($subscription === false) {
            Mage::throwException('Erro ao criar a assinatura. Verifique os dados e tente novamente!');

            return false;
        }

        $payment->setAmount($order->getTotalDue());
        $this->setStore($order->getStoreId());

        $payment->setStatus(Mage_Sales_Model_Order::STATE_PENDING_PAYMENT, Mage_Sales_Model_Order::STATE_PENDING_PAYMENT,
            'Assinatura criada', true);

        return $order->getVindiBillId();
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
            ]
        ];

        $paymentProfile = $payment->getPaymentProfile();

        if ($paymentProfile) {
            $body['payment_profile'] = [
                'id' => $paymentProfile['payment_profile']['id']
            ];
        }

        if ($installments = $payment->getAdditionalInformation('installments')) {
            $body['installments'] = (int) $installments;
        }

        $currentBill = $this->api()->createBill($body);
        $billId = $currentBill['id'];

        if ($billId) {
            if ($currentBill['payment_method_code'] === "bank_slip" || $currentBill['status'] === "paid" || $currentBill['status'] === "review"){
                $order->setVindiBillId($billId);
                $order->save();
                return $billId;
            }
        }

        $this->log(sprintf('Erro no pagamento do pedido %d.', $order->getId()));

        $message = sprintf("Houve um problema na confirmação do pagamento, por favor entre em contato com o banco emissor do cartão. (%s)", $this->api()->lastError);
        $payment->setStatus(
            Mage_Sales_Model_Order::STATE_CANCELED,
            Mage_Sales_Model_Order::STATE_CANCELED,
            $message,
            true
        );

        $this->api()->deleteBill($billId);
        
        Mage::throwException($message);
    }

    /**
     * @param Mage_Payment_Model_Method_Abstract    $payment
     * @param Mage_Sales_Model_Order                $order
     * @param int                                   $customerId
     *
     * @return bool
     */
    protected function createSubscription($payment, $order, $customerId)
    {
        $orderItems = $order->getItemsCollection();
        $item = $orderItems->getFirstItem();
        $product = Mage::getModel('catalog/product')->load($item->getProductId());
        $plan = $product->getData('vindi_subscription_plan');

        $productItems = $this->api()->buildPlanItemsForSubscription($order);
        if(!$productItems){
            return false;
        }

        $body = [
            'customer_id'         => $customerId,
            'payment_method_code' => $this->getPaymentMethodCode(),
            'plan_id'             => $plan,
            'code'                => 'mag-' . $order->getIncrementId() . '-' . time(),
            'product_items'       => $productItems,
        ];

        if ($installments = $payment->getAdditionalInformation('installments')) {
            $body['installments'] = (int) $installments;
        }

        $paymentProfile = $payment->getPaymentProfile();

        if ($paymentProfile) {
            $body['payment_profile'] = [
                'id' => $paymentProfile['payment_profile']['id']
            ];
        }

        $subscription = $this->api()->createSubscription($body);

        $test = $payment->getAdditionalInformation();

        $this->log($test);

        if (! isset($subscription['id']) || empty($subscription['id'])) {
            $message = sprintf('Pagamento Falhou. (%s)', $this->api()->lastError);
            $this->log(sprintf('Erro no pagamento do pedido %s.\n%s', $order->getId(), $message));

            Mage::throwException($message);

            // TODO update order status?
            return false;
        }

        $order->setVindiSubscriptionId($subscription['id']);
        $order->setVindiBillId($subscription['bill']['id']);
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
        if (! $order) {
            return false;
        }

        foreach ($order->getAllVisibleItems() as $item) {
            if (($product = $item->getProduct()) && ($product->getTypeId() === 'subscription')) {
                return false;
            }
        }

        return true;
    }
}
