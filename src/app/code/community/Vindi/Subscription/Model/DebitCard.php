<?php

class Vindi_Subscription_Model_DebitCard extends Mage_Payment_Model_Method_Cc
{
    use Vindi_Subscription_Trait_PaymentMethod;

    public static $METHOD = "DebitCard";
    /**
     * @var string
     */
    protected $_code = 'vindi_debitcard';

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
    protected $_canSaveDc = false;

    /**
     * @var string
     */
    protected $_formBlockType = 'vindi_subscription/form_dc';

    /**
     * @var string
     */
    protected $_infoBlockType = 'vindi_subscription/info_dc';

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
        $quote = $info->getQuote();

        if ($data->getDcChoice() === 'saved') {
            $info->setAdditionalInformation('PaymentMethod', $this->_code)
                ->setAdditionalInformation('use_saved_dc', true);

            return $this;
        }

        $info->setCcType($data->getDcType())
            ->setCcTypeName($data->getDcTypeName())
            ->setCcOwner($data->getDcOwner())
            ->setCcLast4(substr($data->getDcNumber(), -4))
            ->setCcNumber($data->getDcNumber())
            ->setCcCid($data->getDcCid())
            ->setCcExpMonth($data->getDcExpMonth())
            ->setCcExpYear($data->getDcExpYear())
            ->setCcSsIssue($data->getDcSsIssue())
            ->setCcSsStartMonth($data->getDcSsStartMonth())
            ->setCcSsStartYear($data->getDcSsStartYear())
            ->setAdditionalInformation('PaymentMethod', $this->_code)
            ->setAdditionalInformation('use_saved_dc', false);

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

        $customerId      = $this->createCustomer($order, $customer);
        $customerVindiId = $customer->getVindiUserCode();

        if (! $payment->getAdditionalInformation('use_saved_dc')) {
            $this->createPaymentProfile($customerId);
        } else {
            $this->assignDataFromPreviousPaymentProfile($customerVindiId);
        }

        if ($this->isSingleOrder($order)) {
            $billId = $this->processSinglePayment($payment, $order, $customerId);
        } else {
            $billId = $this->processSubscription($payment, $order, $customerId);
        }

        if (! $billId) {
            return false;
        }

        $stateObject->setStatus(Mage_Sales_Model_Order::STATE_PENDING_PAYMENT)
            ->setState(Mage_Sales_Model_Order::STATE_PENDING_PAYMENT);

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

        $debitCardData = [
            'holder_name'          => $payment->getCcOwner(),
            'card_expiration'      => str_pad($payment->getCcExpMonth(), 2, '0', STR_PAD_LEFT)
                . '/' . $payment->getCcExpYear(),
            'card_number'          => $payment->getCcNumber(),
            'card_cvv'             => $payment->getCcCid() ?: '000',
            'customer_id'          => $customerId,
            'payment_company_code' => $payment->getCcType(),
            'payment_method_code'  =>  $this->getPaymentMethodCode()
        ];

        $paymentProfileId = $this->api()->createCustomerPaymentProfile($debitCardData);
        $payment->setPaymentProfile($paymentProfileId);

        if ($paymentProfileId === false) {
            Mage::throwException('Erro ao informar os dados de cartão de crédito. Verifique os dados e tente novamente!');

            return false;
        }

        return $paymentProfileId;
    }

    /**
     * @param int $customerVindiId
     */
    protected function assignDataFromPreviousPaymentProfile($customerVindiId)
    {
        $api     = Mage::helper('vindi_subscription/api');
        $savedDc = $api->getCustomerPaymentProfile($customerVindiId);
        $info    = $this->getInfoInstance();

        $info->setCcType($savedDc['payment_company']['name'])
             ->setCcOwner($savedDc['holder_name'])
             ->setCcLast4($savedDc['card_number_last_four'])
             ->setCcNumber($savedDc['card_number_last_four'])
             ->setAdditionalInformation('use_saved_dc', true);
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
        return Mage::getStoreConfig('payment/vindi_debitcard/active')
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

        $quote = $info->getQuote();

        if ($info->getAdditionalInformation('use_saved_dc')) {
            return $this;
        }

        $availableTypes = $this->api()->getDebitCardTypes();

        $dcNumber = $info->getCcNumber();

        // remove debit card non-numbers
        $dcNumber = preg_replace('/\D/', '', $dcNumber);

        $info->setCcNumber($dcNumber);

        if (! $this->_validateExpDate($info->getCcExpYear(), $info->getCcExpMonth())) {
            return $this->error(Mage::helper('payment')->__('Incorrect debit card expiration date.'));
        }

        if (! array_key_exists($info->getCcType(), $availableTypes)) {
            return $this->error(Mage::helper('payment')->__('Debit card type is not allowed for this payment method.'));
        }

        return $this;
    }

    protected function _validateExpDate($expYear, $expMonth)
    {
        $date = Mage::app()->getLocale()->date();
        if (!$expYear || !$expMonth || ($date->compareYear($expYear) == 1)
            || ($date->compareYear($expYear) == 0 && ($date->compareMonth($expMonth) == 1))
        ) {
            return false;
        }
        return true;
    }

    /**
     * @param string $errorMsg
     *
     * @return bool
     * @throws \Mage_Core_Exception
     */
    private function error($errorMsg)
    {
        Mage::throwException($errorMsg);

        return false;
    }

    /**
     * @return string
     */
    protected function getPaymentMethodCode()
    {
        // TODO fix it to proper method code
        return 'debit_card';
    }
}
