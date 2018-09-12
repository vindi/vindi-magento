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
    protected $_infoBlockType = 'vindi_subscription/info_cc';

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

        $info->setAdditionalInformation('installments', $data->getCcInstallments());

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

        $customerId      = $this->createCustomer($order, $customer);
        $customerVindiId = $customer->getVindiUserCode();

        if (! $payment->getAdditionalInformation('use_saved_cc')) {
            $this->createPaymentProfile($customerId);
        } else {
            $this->assignDataFromPreviousPaymentProfile($customerVindiId);
        }

        if ($this->isSingleOrder($order)) {
            $bill = $this->processSinglePayment($payment, $order, $customerId);
        } else {
            $bill = $this->processSubscription($payment, $order, $customerId);
        }

        if (! $bill || ! $order->getId() || ! $order->canInvoice()) {
            return false;
        }

        if ($bill['status'] === "paid") {
            $installments = $bill['installments'];
            $response_fields = $bill['charges'][0]['last_transaction']['gateway_response_fields'];
            $possible = ['nsu', 'proof_of_sale'];
            $nsu = '';
            foreach ($possible as $nsu_field) {
                if ($response_fields[$nsu_field]) {
                    $nsu = $response_fields[$nsu_field];
                }
            }

            $this->getInfoInstance()->setAdditionalInformation(
                [
                    'installments' => $installments,
                    'nsu' => $nsu
                ]
            );

            $this->createInvoice($order);
            $stateObject->setStatus(Mage_Sales_Model_Order::STATE_PROCESSING)->setState(Mage_Sales_Model_Order::STATE_PROCESSING); 
        }
    }

    protected function createInvoice($order)
    {
        $invoice = $order->prepareInvoice();
        $invoice->setRequestedCaptureCase(Mage_Sales_Model_Order_Invoice::CAPTURE_OFFLINE);
        $invoice->register();
        Mage::getModel('core/resource_transaction')->addObject($invoice)->addObject($invoice->getOrder())->save();
        $invoice->sendEmail(true);
        $order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, true,'O pagamento foi confirmado e o pedido está sendo processado.', true);   
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
            'payment_method_code'  =>  $this->getPaymentMethodCode()
        ];

        $paymentProfile = $this->api()->createCustomerPaymentProfile($creditCardData);

        if ($paymentProfile === false) {
            Mage::throwException('Erro ao informar os dados de cartão de crédito. Verifique os dados e tente novamente!');

            return false;
        }

        $verifyMethod = Mage::getStoreConfig('vindi_subscription/general/verify_method');

        if ($verifyMethod && !$this->verifyPaymentProfile($paymentProfile['payment_profile']['id'])) {
            Mage::throwException('Não foi possível realizar a verificação do seu cartão de crédito!');
            return false;
        }
        return $paymentProfile;    
    }

    /**
     * @param int $paymentProfileId
     *
     * @return array|bool
     */
    public function verifyPaymentProfile($paymentProfileId)
    {
        $verify_status = $this->api()->verifyCustomerPaymentProfile($paymentProfileId);
        return ($verify_status['transaction']['status'] === 'success');
    }
    
    /**
     * @param int $customerVindiId
     */
    protected function assignDataFromPreviousPaymentProfile($customerVindiId)
    {
        $api     = Mage::helper('vindi_subscription/api');
        $savedCc = $api->getCustomerPaymentProfile($customerVindiId);
        $info    = $this->getInfoInstance();

        $info->setCcType($savedCc['payment_company']['code'])
             ->setCcOwner($savedCc['holder_name'])
             ->setCcLast4($savedCc['card_number_last_four'])
             ->setCcNumber($savedCc['card_number_last_four'])
             ->setAdditionalInformation('use_saved_cc', true);
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

        $quote = $info->getQuote();

        $maxInstallmentsNumber = Mage::getStoreConfig('payment/vindi_creditcard/max_installments_number');

        if ($this->isSingleOrder($quote) && ($maxInstallmentsNumber > 1)) {
            if (! $installments = $info->getAdditionalInformation('installments')) {
                return $this->error('Você deve informar o número de parcelas.');
            }

            if ($installments > $maxInstallmentsNumber) {
                return $this->error('O número de parcelas selecionado é inválido.');
            }

            $minInstallmentsValue = Mage::getStoreConfig('payment/vindi_creditcard/min_installment_value');
            $installmentValue = ceil($quote->getGrandTotal() / $installments * 100) / 100;

            if (($installmentValue < $minInstallmentsValue) && ($installments > 1)) {
                return $this->error('O número de parcelas selecionado é inválido.');
            }
        }

        if ($info->getAdditionalInformation('use_saved_cc')) {
            return $this;
        }

        $availableTypes = $this->api()->getCreditCardTypes();

        $ccNumber = $info->getCcNumber();

        // remove credit card non-numbers
        $ccNumber = preg_replace('/\D/', '', $ccNumber);

        $info->setCcNumber($ccNumber);

        if (! array_key_exists($info->getCcType(), $availableTypes)) {
            return $this->error(Mage::helper('payment')->__('Credit card type is not allowed for this payment method.'));
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
        return 'credit_card';
    }
}
