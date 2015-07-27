<?php

class Vindi_Subscription_Model_CreditCard extends Mage_Payment_Model_Method_Cc
{
    use Vindi_Subscription_Trait_PaymentMethod;

    /**
     * @var string
     */
    protected $_code = 'vindi_creditcard';

    /**
     * @var bool
     */
    protected $_isGateway = true;

    /**
     * @var bool
     */
    protected $_canAuthorize = true;

    /**
     * @var bool
     */
    protected $_canCapture = true;

    /**
     * @var bool
     */
    protected $_canCapturePartial = false;

    /**
     * @var bool
     */
    protected $_canRefund = false;

    /**
     * @var bool
     */
    protected $_canVoid = false;

    /**
     * @var bool
     */
    protected $_canUseInternal = true;

    /**
     * @var bool
     */
    protected $_canUseCheckout = true;

    /**
     * @var bool
     */
    protected $_canUseForMultishipping = false;

    /**
     * @var bool
     */
    protected $_isInitializeNeeded = true;

    /**
     * @var bool
     */
    protected $_canSaveCc = false;

    /**
     * @var string
     */
    protected $_formBlockType = 'vindi_subscription/form_cc';

    /**
     * @var string
     */
    protected $_infoBlockType = 'payment/info_cc';

    /**
     * Assign data to info model instance
     *
     * @param   mixed $data
     *
     * @return  Mage_Payment_Model_Method_Abstract
     */
    public function assignData($data)
    {
        if (! ($data instanceof Varien_Object)) {
            $data = new Varien_Object($data);
        }
        $info = $this->getInfoInstance();

        if ($data->getCcChoice() === 'saved') {
            $info->setAdditionalInformation('PaymentMethod', $this->_code)
                ->setAdditionalInformation('use_saved_cc', true);

            return $this;
        }

        $info->setCcType($data->getCcType())
            ->setCcOwner($data->getCcOwner())
            ->setCcLast4(substr($data->getCcNumber(), -4))
            ->setCcNumber($data->getCcNumber())
            ->setCcCid($data->getCcCid())
            ->setCcExpMonth($data->getCcExpMonth())
            ->setCcExpYear($data->getCcExpYear())
            ->setCcSsIssue($data->getCcSsIssue())
            ->setCcSsStartMonth($data->getCcSsStartMonth())
            ->setCcSsStartYear($data->getCcSsStartYear())
            ->setAdditionalInformation('PaymentMethod', $this->_code)
            ->setAdditionalInformation('use_saved_cc', false);

        return $this;
    }

    /**
     * @param string $paymentAction
     * @param object $stateObject
     *
     * @return bool|Mage_Payment_Model_Method_Abstract
     */
    protected function processNewOrder($paymentAction, $stateObject)
    {
        $payment = $this->getInfoInstance();
        $order = $payment->getOrder();

        $customer = Mage::getModel('customer/customer');

        $customerId = $this->createCustomer($order, $customer);

        if (! $payment->getAdditionalInformation('use_saved_cc')) {
            $this->createPaymentProfile($customerId);
        }

        if ($this->isSingleOrder($order)) {
            $result = $this->processSinglePayment($payment, $order, $customerId);
        } else {
            $result = $this->processSubscription($payment, $order, $customerId);
        }

        if (! $result) {
            return false;
        }

        $stateObject->setStatus(Mage_Sales_Model_Order::STATE_PAYMENT_REVIEW)
            ->setState(Mage_Sales_Model_Order::STATE_PAYMENT_REVIEW);

        return $this;
    }

    /**
     * @param int $customerId
     *
     * @return array|bool
     */
    protected function createPaymentProfile($customerId)
    {
        $payment = $this->getInfoInstance();

        $creditCardData = [
            'holder_name'          => $payment->getCcOwner(),
            'card_expiration'      => str_pad($payment->getCcExpMonth(), 2, '0', STR_PAD_LEFT)
                . '/' . $payment->getCcExpYear(),
            'card_number'          => $payment->getCcNumber(),
            'card_cvv'             => $payment->getCcCid() ?: '000',
            'customer_id'          => $customerId,
            'payment_company_code' => $payment->getCcType(),
        ];

        $paymentProfileId = $this->api()->createCustomerPaymentProfile($creditCardData);

        if ($paymentProfileId === false) {
            Mage::throwException('Erro ao informar os dados de cartão de crédito. Verifique os dados e tente novamente!');

            return false;
        }

        return $paymentProfileId;
    }

    /**
     * Check whether payment method can be used
     *
     * @param Mage_Sales_Model_Quote|null $quote
     *
     * @return bool
     */
    public function isAvailable($quote = null)
    {
        return Mage::getStoreConfig('payment/vindi_creditcard/active')
        && Mage::helper('vindi_subscription')->getKey();
    }

    /**
     * Validate payment method information object
     *
     * @return  Mage_Payment_Model_Method_Abstract
     */
    public function validate()
    {
        $info = $this->getInfoInstance();
        $errorMsg = false;

        if ($info->getAdditionalInformation('use_saved_cc')) {
            return $this;
        }

        $availableTypes = $this->api()->getCreditCardTypes();

        $ccNumber = $info->getCcNumber();

        // remove credit card non-numbers
        $ccNumber = preg_replace('/\D/', '', $ccNumber);

        $info->setCcNumber($ccNumber);

        if (! $this->_validateExpDate($info->getCcExpYear(), $info->getCcExpMonth())) {
            $errorMsg = Mage::helper('payment')->__('Incorrect credit card expiration date.');
        }

        if (! array_key_exists($info->getCcType(), $availableTypes)) {
            $errorMsg = Mage::helper('payment')->__('Credit card type is not allowed for this payment method.');
        }

        if ($errorMsg) {
            Mage::throwException($errorMsg);

            return false;
        }

        return $this;
    }

    /**
     * @return string
     */
    protected function getPaymentMethodCode()
    {
        // TODO fix it to proper method code
        return 'credit_card';
    }
}