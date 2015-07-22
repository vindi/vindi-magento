<?php

class Vindi_Subscription_Helper_API extends Mage_Core_Helper_Abstract
{

    /**
     * @const string API base path.
     */
    const BASE_PATH = 'https://app.vindi.com.br/api/v1/';

    /**
     * @var string
     */
    public $lastError = '';

    /**
     * @var string
     */
    private $version;

    /**
     * @var string
     */
    private $key;

    /**
     * @var bool
     */
    private $acceptBankSlip;

    public function __construct()
    {
        $this->version = (string) Mage::getConfig()->getModuleConfig('Vindi_Subscription')->version;
        $this->key = Mage::helper('vindi_subscription')->getKey();
    }

    /**
     * @param string   $message
     * @param int|null $level
     */
    private function log($message, $level = null)
    {
        Mage::log($message, $level, 'vindi_api.log');
    }

    /**
     * Build HTTP Query.
     *
     * @param array $data
     *
     * @return string
     */
    private function buildBody($data)
    {
        return json_encode($data);
    }

    /**
     * @param array $error
     * @param       $endpoint
     *
     * @return string
     */
    private function getErrorMessage($error, $endpoint)
    {
        return "Erro em $endpoint: {$error['id']}: {$error['parameter']} - {$error['message']}";
    }

    /**
     * @param array $response
     * @param       $endpoint
     *
     * @return bool
     */
    private function checkResponse($response, $endpoint)
    {
        if (isset($response['errors']) && ! empty($response['errors'])) {
            foreach ($response['errors'] as $error) {
                $message = $this->getErrorMessage($error, $endpoint);

                Mage::getSingleton('core/session')->addError($message);

                $this->lastError = $message;
            }

            return false;
        }

        $this->lastError = '';

        return true;
    }

    /**
     * Perform request to API.
     *
     * @param string $endpoint
     * @param string $method
     * @param array  $data
     * @param null   $dataToLog
     *
     * @return array|bool|mixed
     */
    private function request($endpoint, $method = 'POST', $data = [], $dataToLog = null)
    {
        if (! $this->key) {
            return false;
        }

        $url = static::BASE_PATH . $endpoint;
        $body = $this->buildBody($data);

        $requestId = rand();

        $dataToLog = null !== $dataToLog ? $this->buildBody($dataToLog) : $body;

        $this->log(sprintf("[Request #%s]: Novo Request para a API.\n%s %s\n%s", $requestId, $method, $url,
            $dataToLog));

        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
            ],
            CURLOPT_TIMEOUT        => 60,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_HEADER         => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_USERAGENT      => 'Vindi-Magento/' . $this->version,
            CURLOPT_USERPWD        => $this->key . ':',
            CURLOPT_URL            => $url,
            CURLOPT_CUSTOMREQUEST  => $method,
            CURLOPT_POSTFIELDS     => $body,
        ]);

        $response = curl_exec($ch);

        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $body = substr($response, curl_getinfo($ch, CURLINFO_HEADER_SIZE));

        if (curl_errno($ch) || $response === false) {
            $this->log(sprintf("[Request #%s]: Erro ao fazer request!\n%s", $requestId, print_r($response, true)));

            return false;
        }

        curl_close($ch);

        $status = "HTTP Status: $statusCode";
        $this->log(sprintf("[Request #%s]: Nova Resposta da API.\n%s\n%s", $requestId, $status, $body));

        $responseBody = json_decode($body, true);

        if (! $responseBody) {
            $this->log(sprintf('[Request #%s]: Erro ao recuperar corpo do request! %s', $requestId,
                print_r($body, true)));

            return false;
        }

        if (! $this->checkResponse($responseBody, $endpoint)) {
            return false;
        }

        return $responseBody;
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
        if ($response = $this->request('customers', 'POST', $body)) {
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
        $response = $this->request("customers/search?code={$code}", 'GET');

        if ($response && (1 === count($response['customers'])) && isset($response['customers'][0]['id'])) {
            return $response['customers'][0]['id'];
        }

        return false;
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
        // Protect credit card number.
        $dataToLog = $body;
        $dataToLog['card_number'] = '**** *' . substr($dataToLog['card_number'], -3);
        $dataToLog['card_cvv'] = '***';

        return $this->request('payment_profiles', 'POST', $body, $dataToLog);
    }

    /**
     * @param $userCode
     *
     * @return bool
     */
    public function getCustomerPaymentProfile($userCode)
    {
        $customerId = $this->findCustomerByCode($userCode);

        if (false === $customerId) {
            return false;
        }
        $endpoint = 'payment_profiles?query=customer_id%3D' . $customerId
            . '%20status%3Dactive%20type%3DPaymentProfile%3A%3ACreditCard';

        $response = $this->request($endpoint, 'GET');

        if ($response && $response['payment_profiles'] && count($response['payment_profiles'])) {
            return $response['payment_profiles'][0];
        }

        return false;
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
        if (($response = $this->request('subscriptions', 'POST', $body)) && isset($response['subscription']['id'])) {

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
        $cache = Mage::app()->getCache();

        $paymentMethods = $cache->load('vindi_payment_methods');

        if ($paymentMethods === false) {

            $paymentMethods = [
                'credit_card' => [],
                'bank_slip'   => false,
            ];

            $response = $this->request('payment_methods', 'GET');

            if (false === $response) {
                return $this->acceptBankSlip = false;
            }

            foreach ($response['payment_methods'] as $method) {
                if ('active' !== $method['status']) {
                    continue;
                }

                if ('PaymentMethod::CreditCard' === $method['type']) {
                    $paymentMethods['credit_card'] = array_merge($paymentMethods['credit_card'],
                        $method['payment_companies']);
                } else {
                    if ('PaymentMethod::BankSlip' === $method['type']) {
                        $paymentMethods['bank_slip'] = true;
                    }
                }
            }

            $cache->save(serialize($paymentMethods), 'vindi_payment_methods', ['vindi_cache'],
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
        if ($response = $this->request('bills', 'POST', $body)) {
            return $response['bill']['id'];
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
        $response = $this->request("bills/{$billId}", 'GET');

        if (false === $response || ! isset($response['bill'])) {
            return false;
        }

        $bill = $response['bill'];

        if ('review' !== $bill['status']) {
            return true;
        }

        return $this->request("bills/{$billId}/approve", 'POST');
    }

    /**
     * @param $billId
     *
     * @return string
     */
    public function getBankSlipDownload($billId)
    {
        $response = $this->request("bills/{$billId}", 'GET');

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
        $response = $this->request('products?query=status:active', 'GET');

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
        $response = $this->request("plans/{$id}", 'GET');

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
     * @param int   $planId
     * @param float $orderTotal
     *
     * @return array
     */
    public function buildPlanItemsForSubscription($planId, $orderTotal)
    {
        $list = [];

        foreach ($this->getPlanItems($planId) as $item) {
            $list[] = [
                'product_id'     => $item,
                'pricing_schema' => ['price' => $orderTotal],
            ];
            $orderTotal = 0;
        }

        return $list;
    }

    /**
     * @return array
     */
    public function getPlans()
    {
        $cache = Mage::app()->getCache();

        $list = $cache->load('vindi_plans');

        if ($list === false) {

            $list = [];
            $response = $this->request('plans?query=status:active', 'GET');

            if ($plans = $response['plans']) {
                foreach ($plans as $plan) {
                    $list[$plan['id']] = $plan['name'];
                }
            }
            $cache->save(serialize($list), 'vindi_plans', ['vindi_cache'], 10 * 60); // 10 minutes
        } else {
            $list = unserialize($list);
        }

        return $list;
    }

    /**
     * Make an API request to create a Product.
     *
     * @param array $body (name, code, status, pricing_schema (price))
     *
     * @return array|bool|mixed
     */
    public function createProduct(
        $body
    ) {
        if ($response = $this->request('products', 'POST', $body)) {
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
    public function findProductByCode(
        $code
    ) {
        $response = $this->request("products?query=code%3D{$code}", 'GET');

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
    public function findOrCreateUniquePaymentProduct()
    {
        $productId = $this->findProductByCode('wc-pagtounico');

        if (false === $productId) {
            return $this->createProduct([
                'name'           => 'Pagamento Único (não remover)',
                'code'           => 'wc-pagtounico',
                'status'         => 'active',
                'pricing_schema' => [
                    'price' => 0,
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
        $cache = Mage::app()->getCache();

        $merchant = $cache->load('vindi_merchant');

        if ($merchant === false) {

            $response = $this->request('merchant', 'GET');

            if (! $response || ! isset($response['merchant'])) {
                return false;
            }

            $merchant = $response['merchant'];

            $cache->save(serialize($merchant), 'vindi_merchant', ['vindi_cache'], 1 * 60 * 60); // 1 hour
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
        $response = $this->request("bills/{$billId}", 'GET');

        if (! $response || ! isset($response['bill'])) {
            return false;
        }

        return $response['bill'];
    }
}