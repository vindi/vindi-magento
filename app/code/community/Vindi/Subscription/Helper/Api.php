<?php

class Vindi_Subscription_Helper_API
{
    /**
     * @var string
     */
    public $lastError = '';

    /**
     * @var bool
     */
    protected $acceptBankSlip;

    protected $connector;

    public function __construct()
    {
        $this->connector = new Vindi_Subscription_Helper_Connector();
    }

    /**
     * @return \Zend_Cache_Core
     */
    protected function cache()
    {
        return Mage::app()->getCache();
    }

    /**
     * Make an API request to create a Customer.
     *
     * @param array $body (name, email, code)
     *
     * @return array|bool|mixed
     */
    public function createCustomer($body)
    {

        if ($response = $this->connector->post('customers', $body)) {
            return $response['customer']['id'];
        }

        return false;
    }

    /**
     * Make an API request to retrieve an existing Customer.
     *
     * @param string $code
     *
     * @return array|bool|mixed
     */
    public function findCustomerByCode($code)
    {
        $customerId = $this->cache()->load("vindi_customer_by_code_{$code}");

        if ($customerId === false) {
            $response = $this->connector->get("customers/search?code={$code}");

            if ($response && (1 === count($response['customers'])) && isset($response['customers'][0]['id'])) {
                $customerId = $response['customers'][0]['id'];

                $this->cache()->save(serialize($customerId), "vindi_customer_by_code_{$code}", ['vindi_cache'],
                    5 * 60); // 5 minutes
            }
        } else {
            $customerId = unserialize($customerId);
        }

        return $customerId;
    }

    /**
     * Make an API request to retrieve an existing Customer or to create one if not found.
     *
     * @param array $body (name, email, code)
     *
     * @return array|bool|mixed
     */
    public function findOrCreateCustomer($body)
    {
        $customerId = $this->findCustomerByCode($body['code']);
        // TODO update information

        if (false === $customerId) {
            return $this->createCustomer($body);
        }

        return $customerId;
    }

    /**
     * Make an API request to create a Payment Profile to a Customer.
     *
     * @param $body (holder_name, card_expiration, card_number, card_cvv, customer_id)
     *
     * @return array|bool|mixed
     */
    public function createCustomerPaymentProfile($body)
    {
        $this->log(json_encode($dataToLog), 'vindi_creditcard.log');
        $customerId = $body['customer_id'];
        $this->cache()->remove("vindi_payment_profile_{$customerId}");

        return $this->connector->post('payment_profiles', $body);
    }

    /**
     * Make an API request to verify a Payment Profile to a Customer.
     *
     * @param $id integer
     *
     * @return array|bool|mixed
     */
    public function verifyCustomerPaymentProfile($id)
    {
        return $this->connector->post('payment_profiles/' . $id . '/verify');
    }

    /**
     * @param $userCode
     *
     * @return bool
     */
    public function getCustomerPaymentProfile($userCode, $type = "CreditCard")
    {
        $customerId = $this->findCustomerByCode($userCode);

        if (false === $customerId) {
            return false;
        }

        $paymentProfile = $this->cache()->load("vindi_payment_profile_{$customerId}");

        if ($paymentProfile === false || strpos($paymentProfile, $type) === false) {
            $endpoint = 'payment_profiles?query=customer_id%3D' . $customerId
                . '%20status%3Dactive%20type%3DPaymentProfile%3A%3A'. $type;

            $response = $this->connector->get($endpoint);

            if ($response && $response['payment_profiles'] && count($response['payment_profiles'])) {
                $paymentProfile = $response['payment_profiles'][0];

                $this->cache()->save(serialize($paymentProfile), "vindi_payment_profile_{$customerId}", ['vindi_cache'],
                    5 * 60); // 5 minutes
            }else{
                $paymentProfile = false;
            }
        } else {
            $paymentProfile = unserialize($paymentProfile);
        }

        return $paymentProfile;
    }

    /**
     * Make an API request to create a Subscription.
     *
     * @param $body (plan_id, customer_id, payment_method_code, product_items[{product_id}])
     *
     * @return array
     */
    public function createSubscription($body)
    {
        if (($response = $this->connector->post('subscriptions', $body)) && isset($response['subscription']['id'])) {

            $subscription = $response['subscription'];
            $subscription['bill'] = $response['bill'];

            return $subscription;
        }

        return false;
    }

    /**
     * Make an API request to retrieve Payment Methods.
     *
     * @return array|bool
     */
    public function getPaymentMethods()
    {
        $paymentMethods = $this->cache()->load('vindi_payment_methods');

        if ($paymentMethods === false) {

            $paymentMethods = [
                'credit_card' => [],
                'debit_card' => [],
                'bank_slip'   => false,
            ];

            $response = $this->connector->get('payment_methods');

            if (false === $response) {
                return $this->acceptBankSlip = false;
            }
            foreach ($response['payment_methods'] as $method) {
                if ('active' !== $method['status']) {
                    continue;
                }

                if ('PaymentMethod::CreditCard' === $method['type']) {
                    $paymentMethods['credit_card'] = array_merge(
                        $paymentMethods['credit_card'],
                        $method['payment_companies']
                    );
                }elseif('PaymentMethod::DebitCard' === $method['type']) {
                    $paymentMethods['debit_card'] = array_merge(
                        $paymentMethods['debit_card'],
                        $method['payment_companies']
                    );
                } elseif ('PaymentMethod::BankSlip' === $method['type'] ||
                          'PaymentMethod::OnlineBankSlip' === $method['type']) {
                    $paymentMethods['bank_slip'] = true;
                }
            }

            $this->cache()->save(serialize($paymentMethods), 'vindi_payment_methods', ['vindi_cache'],
                12 * 60 * 60); // 12 hours
        } else {
            $paymentMethods = unserialize($paymentMethods);
        }

        $this->acceptBankSlip = $paymentMethods['bank_slip'];

        return $paymentMethods;
    }

    /**
     * Retrieve Credit Card Types from Payment Methods.
     *
     * @return array
     */
    public function getCreditCardTypes()
    {
        $methods = $this->getPaymentMethods();
        $types = [];

        foreach ($methods['credit_card'] as $type) {
            $types[$type['code']] = $type['name'];
        }

        return $types;
    }

    /**
     * Retrieve Debit Card Types from Payment Methods.
     *
     * @return array
     */
    public function getDebitCardTypes()
    {
        $methods = $this->getPaymentMethods();
        $types = [];

        foreach ($methods['debit_card'] as $type) {
            $types[$type['code']] = $type['name'];
        }

        return $types;
    }

    /**
     * @return bool|null
     */
    public function acceptBankSlip()
    {
        if (null === $this->acceptBankSlip) {
            $this->getPaymentMethods();
        }

        return $this->acceptBankSlip;
    }

    /**
     * @param array $body
     *
     * @return int|bool
     */
    public function createBill($body)
    {
        if ($response = $this->connector->post('bills', $body)) {
            return $response['bill'];
        }

        return false;
    }

    /**
     * @param $billId
     *
     * @return array|bool|mixed
     */
    public function approveBill($billId)
    {
        $response = $this->connector->get("bills/{$billId}");

        if (false === $response || ! isset($response['bill'])) {
            return false;
        }

        $bill = $response['bill'];

        if ('review' !== $bill['status']) {
            return true;
        }

        return $this->connector->post("bills/{$billId}/approve");
    }

    /**
     * Cancela assinaturas e faturas na Vindi.
     *
     * @param $vindiId
     */
    public function cancelPurchase($vindiId, $type)
    {
        $this->connector->delete("{$type}/{$vindiId}");
    }

    /**
     * @param $billId
     *
     * @return string
     */
    public function getBankSlipDownload($billId)
    {
        $response = $this->connector->get("bills/{$billId}");

        if (false === $response) {
            return false;
        }

        return $response['bill']['charges'][0]['print_url'];
    }

    /**
     * @return array
     */
    public function getProducts()
    {
        $list = [];
        $response = $this->connector->get('products?query=status:active');

        if ($products = $response['products']) {
            foreach ($products as $product) {
                $list[$product['id']] = "{$product['name']} ({$product['pricing_schema']['short_format']})";
            }
        }

        return $list;
    }

    /**
     * @param int $id
     *
     * @return array
     */
    public function getPlanItems($id)
    {
        $list = [];
        $response = $this->connector->get("plans/{$id}");

        if ($plan = $response['plan']) {
            foreach ($plan['plan_items'] as $item) {
                if (isset($item['product'])) {
                    $list[] = $item['product']['id'];
                }
            }
        }

        return $list;
    }

    /**
     * @param Mage_Sales_Model_Order $order
     *
     * @return array
     */
    public function buildPlanItemsForSubscription($order)
    {
        $list = array();
        $orderItems = $order->getItemsCollection();
        $orderSubtotal = $order->getQuote()->getSubtotal();
        $orderDiscount = $order->getDiscountAmount() * -1;

        $discount = null;
        if(! empty($orderDiscount)) {
            $discountPercentage = $orderDiscount * 100 / $orderSubtotal;
            $discountPercentage = number_format(floor($discountPercentage*100)/100, 2);

            $discount = array(array(
                        'discount_type' => 'percentage',
                        'percentage' => $discountPercentage
                    ));
        }

        foreach ($orderItems as $item) {
            $cycles         = null;
            if (Mage::getModel('catalog/product')->load($item->getProductId())->getTypeID()
                !== 'subscription') {
                $cycles = 1;
            }

            array_push($list, array(
                'product_id'          => $this->findOrCreateProduct(
                    array(
                    	'sku'         => $item->getSku(),
                    	'name'        => $item->getName()
                    )
                ),
                'cycles'              => $cycles,
                'quantity'            => $item->getQtyOrdered(),
                'pricing_schema'      => array(
                    'price'           => $item->getPrice(),
                    'schema_type'     => 'per_unit'
                ),
                'discounts'           => $discount,
            ));
        }
        $list = $this->buildTax($list, $order);
        $list = $this->buildShipping($list, $order);
        return $list;
    }

    /**
     * Carrega Frete para a Fatura Vindi
     *
     * @param array $items, Mage_Sales_Model_Order $order
     *
     * @return array
     */
    public function buildShipping($items, $order)
    {
        if ($order->getShippingAmount() > 0) {
            array_push($items, array(
                'product_id'     => $this->findOrCreateProduct(
                    array(
                        'sku'    => 'frete',
                        'name'   => 'Frete'
                    )
                ),
                'quantity'       => 1,
                'pricing_schema' => array('price' => $order->getShippingAmount()),
            ));
        }
        return $items;
    }

    /**
     * Carrega cupom de desconto para a Faturas Avulsas
     *
     * @param array $items, Mage_Sales_Model_Order $order
     *
     * @return array
     */
    public function buildSingleDiscount($items, $order)
    {
        if ($order->getDiscountAmount() < 0) {
            array_push($items, array(
                'product_id'     => $this->findOrCreateProduct(
                    array(
                        'sku'    => 'cupom',
                        'name'   => 'Cupom de Desconto'
                    )
                ),
                'amount' =>  $order->getDiscountAmount(),
            ));
        }
        return $items;
    }

    /**
     * Carrega Taxas para a Fatura Vindi
     *
     * @param array $items, Mage_Sales_Model_Order $order
     *
     * @return array
     */
    public function buildTax($items, $order)
    {
        if (array_key_exists('tax', $order->getQuote()->getTotals())) {
            array_push($items, array(
                'product_id'     => $this->findOrCreateProduct(
                    array(
                        'sku'    => 'taxa',
                        'name'   => 'Taxa'
                    )
                ),
                'quantity'       => 1,
                'pricing_schema' => array(
                    'price'      => $order->getQuote()->getTotals()['tax']->getData('value')
                ),
            ));
        }
        return $items;
    }

    /**
     * @return array
     */
    public function getPlans()
    {
        $list = $this->cache()->load('vindi_plans');

        if (($list === false) || ! count($list = unserialize($list))) {

            $list = [];
            $response = $this->connector->get('plans?query=status:active');

            if ($plans = $response['plans']) {
                foreach ($plans as $plan) {
                    $list[$plan['id']] = $plan['name'];
                }
            }
            $this->cache()->save(serialize($list), 'vindi_plans', ['vindi_cache'], 10 * 60); // 10 minutes
        }

        return $list;
    }

    public function getPlanInstallments($id)
    {
        $response = $this->connector->get("plans/{$id}");
        $plan = $response['plan'];
        $installments = $plan['installments'];

        return $installments;
    }


    /**
     * Make an API request to create a Product.
     *
     * @param array $body (name, code, status, pricing_schema (price))
     *
     * @return array|bool|mixed
     */
    public function createProduct($body)
    {
        if ($response = $this->connector->post('products', $body)) {
            return $response['product']['id'];
        }

        return false;
    }

    /**
     * Make an API request to retrieve an existing Product.
     *
     * @param string $code
     *
     * @return array|bool|mixed
     */
    public function findProductByCode($code)
    {
        $response = $this->connector->get("products?query=code%3D{$code}");

        if ($response && (1 === count($response['products'])) && isset($response['products'][0]['id'])) {
            return $response['products'][0]['id'];
        }

        return false;
    }

    /**
     * Make an API request to retrieve the Unique Payment Product or to create it if not found.
     *
     * @return array|bool|mixed
     */
    public function findOrCreateUniquePaymentProduct($order)
    {
        $billItems = array();
        foreach ($order->getItemsCollection() as $item) {
            $productId = $this->findOrCreateProduct(
                array(
                    'sku' => $item->getSku(),
                    'name' => $item->getName()
                )
            );

            array_push($billItems, array(
                'product_id'      => $productId,
                'quantity'        => $item->getQtyOrdered(),
                'pricing_schema'     => array(
                    'price'       => $item->getPrice(),
                    'schema_type' => 'per_unit'
                )
            ));
        }

        $billItems = $this->buildTax($billItems, $order);
        $billItems = $this->buildSingleDiscount($billItems, $order);
        $billItems = $this->buildShipping($billItems, $order);
        return $billItems;
    }

    /**
     * Make an API request to retrieve a Product or to create it if not found.
     * @param array $product
     *
     * @return array|bool|mixed
     */
    public function findOrCreateProduct($product)
    {
        $productId = $this->findProductByCode($product['sku']);

        if (false === $productId) {
            return $this->createProduct([
                'name'           => $product['name'],
                'code'           => $product['sku'],
                'status'         => 'active',
                'pricing_schema' => [
                    'price' => 0,
                    'schema_type' => 'per_unit'
                ],
            ]);
        }

        return $productId;
    }

    /**
     * Make an API request to retrieve information about the Merchant.
     *
     * @return array|bool|mixed
     */
    public function getMerchant()
    {
        $merchant = $this->cache()->load('vindi_merchant');

        if ($merchant === false) {

            $response = $this->connector->get('merchant');

            if (! $response || ! isset($response['merchant'])) {
                return false;
            }

            $merchant = $response['merchant'];

            $this->cache()->save(serialize($merchant), 'vindi_merchant', ['vindi_cache'], 1 * 60 * 60); // 1 hour
        } else {
            $merchant = unserialize($merchant);
        }

        return $merchant;
    }

    /**
     * Check to see if Merchant Status is Trial.
     *
     * @return boolean
     */
    public function isMerchantStatusTrial()
    {
        if ($merchant = $this->getMerchant()) {
            return 'trial' === $merchant['status'];
        }

        return false;
    }

    /**
     * @param $billId
     *
     * @return array|bool
     */
    public function getBill($billId)
    {
        $response = $this->connector->get("bills/{$billId}");

        if (! $response || ! isset($response['bill'])) {
            return false;
        }

        return $response['bill'];
    }

    public function getDebitCardRedirectUrl($billId)
    {

        $bill = $this->connector->get('bills/'.$billId);

        $chargeId = $bill['bill']['charges'][0]['id'];
        $charged = $this->connector->post('charges/'.$chargeId.'/charge', [
            'id' => $bill['bill']['payment_profile']['id']
        ]);

        return $charged['charge']['last_transaction']['gateway_response_fields']['redirect_url'];

    }
}
