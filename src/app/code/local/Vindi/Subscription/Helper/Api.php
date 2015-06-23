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
        $this->version = (string)Mage::getConfig()->getModuleConfig('Vindi_Subscription')->version;
        $this->key = Mage::getStoreConfig('vindi_subscription/general/api_key');
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

                Mage::throwException($message);

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
    private function request($endpoint, $method = 'POST', $data = array(), $dataToLog = null)
    {
        $url = static::BASE_PATH . $endpoint;
        $body = $this->buildBody($data);

        $requestId = rand();

        $dataToLog = null !== $dataToLog ? $this->buildBody($dataToLog) : $body;

        $this->log(sprintf("[Request #%s]: Novo Request para a API.\n%s %s\n%s", $requestId, $method, $url,
            $dataToLog));

        $ch = curl_init();

        curl_setopt_array($ch, array(
            CURLOPT_HTTPHEADER     => array(
                'Content-Type: application/json',
            ),
            CURLOPT_TIMEOUT        => 60,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_HEADER         => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_USERAGENT      => 'Vindi-Magento/' . $this->version,
            CURLOPT_USERPWD        => $this->key . ':',
            CURLOPT_URL            => $url,
            CURLOPT_CUSTOMREQUEST  => $method,
            CURLOPT_POSTFIELDS     => $body,
        ));

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
     * Make an API request to retrive Payment Methods.
     *
     * @return array|bool
     */
    public function getPaymentMethods()
    {

        $paymentMethods = array(
            'credit_card' => array(),
            'bank_slip'   => false,
        );

        $response = $this->request('payment_methods', 'GET');

        if (false === $response) {
            return false;
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

        $this->acceptBankSlip = $paymentMethods['bank_slip'];

        return $paymentMethods;
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
        $list = array();
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
        $list = array();
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
        $list = array();

        foreach ($this->getPlanItems($planId) as $item) {
            $list[] = array(
                'product_id'     => $item,
                'pricing_schema' => array('price' => $orderTotal),
            );
            $orderTotal = 0;
        }

        return $list;
    }

    /**
     * @return array
     */
    public function getPlans()
    {
        $list = array();
        $response = $this->request('plans?query=status:active', 'GET');

        if ($plans = $response['plans']) {
            foreach ($plans as $plan) {
                $list[$plan['id']] = $plan['name'];
            }
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
    public function createProduct($body)
    {
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
    public function findProductByCode($code)
    {
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
            return $this->createProduct(array(
                'name'           => 'Pagamento Único (não remover)',
                'code'           => 'wc-pagtounico',
                'status'         => 'active',
                'pricing_schema' => array(
                    'price' => 0,
                ),
            ));
        }

        return $productId;
    }

    /**
     * Make an API request to retrieve informations about the Merchant.
     *
     * @return array|bool|mixed
     */
    public function getMerchant()
    {
        $response = $this->request('merchant', 'GET');

        if (! $response || ! $response['merchant']) {
            return false;
        }

        return $response['merchant'];
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
}