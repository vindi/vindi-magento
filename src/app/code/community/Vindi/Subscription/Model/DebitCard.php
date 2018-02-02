<?php

class Vindi_Subscription_Model_DebitCard extends Mage_Payment_Model_Method_Cc
{
    use Vindi_Subscription_Trait_PaymentMethod;

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

        $info->setAdditionalInformation('installments', $data->getDcInstallments());

        if ($data->getDcChoice() === 'saved') {
            $info->setAdditionalInformation('PaymentMethod', $this->_code)
                ->setAdditionalInformation('use_saved_dc', true);

            return $this;
        }

        $info->setDcType($data->getDcType())
            ->setDcOwner($data->getDcOwner())
            ->setDcLast4(substr($data->getDcNumber(), -4))
            ->setDcNumber($data->getDcNumber())
            ->setDcCid($data->getDcCid())
            ->setDcExpMonth($data->getDcExpMonth())
            ->setDcExpYear($data->getDcExpYear())
            ->setDcSsIssue($data->getDcSsIssue())
            ->setDcSsStartMonth($data->getDcSsStartMonth())
            ->setDcSsStartYear($data->getDcSsStartYear())
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
            $result = $this->processSinglePayment($payment, $order, $customerId);
        } else {
            $result = $this->processSubscription($payment, $order, $customerId);
        }

        if (! $result) {
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
            'holder_name'          => $payment->getDcOwner(),
            'card_expiration'      => str_pad($payment->getDcExpMonth(), 2, '0', STR_PAD_LEFT)
                . '/' . $payment->getDcExpYear(),
            'card_number'          => $payment->getDcNumber(),
            'card_cvv'             => $payment->getDcCid() ?: '000',
            'customer_id'          => $customerId,
            'payment_company_code' => $payment->getDcType(),
            'payment_method_code'  =>  $this->getPaymentMethodCode()
        ];

        $paymentProfileId = $this->api()->createCustomerPaymentProfile($debitCardData);

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

        $info->setDcType($savedDc['payment_company']['name'])
             ->setDcOwner($savedDc['holder_name'])
             ->setDcLast4($savedDc['card_number_last_four'])
             ->setDcNumber($savedDc['card_number_last_four'])
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

        $maxInstallmentsNumber = Mage::getStoreConfig('payment/vindi_debitcard/max_installments_number');

        if ($this->isSingleOrder($quote) && ($maxInstallmentsNumber > 1)) {
            if (! $installments = $info->getAdditionalInformation('installments')) {
                return $this->error('Você deve informar o número de parcelas.');
            }

            if ($installments > $maxInstallmentsNumber) {
                return $this->error('O número de parcelas selecionado é inválido.');
            }

            $minInstallmentsValue = Mage::getStoreConfig('payment/vindi_debitcard/min_installment_value');
            $installmentValue = ceil($quote->getGrandTotal() / $installments * 100) / 100;

            if (($installmentValue < $minInstallmentsValue) && ($installments > 1)) {
                return $this->error('O número de parcelas selecionado é inválido.');
            }
        }

        if ($info->getAdditionalInformation('use_saved_dc')) {
            return $this;
        }

        $availableTypes = $this->api()->getDebitCardTypes();

        $dcNumber = $info->getDcNumber();

        // remove debit card non-numbers
        $dcNumber = preg_replace('/\D/', '', $dcNumber);

        $info->setDcNumber($dcNumber);

        if (! $this->_validateExpDate($info->getDcExpYear(), $info->getDcExpMonth())) {
            return $this->error(Mage::helper('payment')->__('Incorrect debit card expiration date.'));
        }

        if (! array_key_exists($info->getDcType(), $availableTypes)) {
            return $this->error(Mage::helper('payment')->__('Debit card type is not allowed for this payment method.'));
        }

        return $this;
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
