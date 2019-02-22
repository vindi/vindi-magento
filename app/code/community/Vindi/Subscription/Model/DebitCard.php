<?php

class Vindi_Subscription_Model_DebitCard extends Vindi_Subscription_Model_PaymentMethod
{
    /**
     * @var string
     */
    public static $METHOD = "DebitCard";
    /**
     * @var string
     */
    protected $_code = 'vindi_debitcard';

    /**
     * @var string
     */
    protected $vindiMethodCode = 'debit_card';

    /**
     * @var string
     */
    protected $save_method = 'use_saved_dc';

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
            $bill = $this->processSinglePayment($payment, $order, $customerId);
        } else {
            $bill = $this->processSubscription($payment, $order, $customerId);
        }

        if (! $bill) {
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

        if (! array_key_exists($info->getCcType(), $availableTypes)) {
            return $this->error(Mage::helper('payment')->__('Debit card type is not allowed for this payment method.'));
        }

        return $this;
    }
}
