<?php

class Vindi_Subscription_Model_PaymentMethod extends Mage_Payment_Model_Method_Abstract
{
	use Vindi_Subscription_Trait_PaymentProcessor;
	use Vindi_Subscription_Trait_ExceptionMessenger;
	use Vindi_Subscription_Trait_LogMessenger;

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
	 * Assign data to info model instance
	 *
	 * @param   mixed $data
	 *
	 * @return  Vindi_Subscription_Model_[CreditCard|DebitCard]
	 */
	public function assignData($data)
	{
		if (! ($data instanceof Varien_Object)) {
			$data = new Varien_Object($data);
		}

		$info = $this->getInfoInstance();
		$this->loadAttributes($info, $data);

		return $this;
	}

	/**
	 * @return string
	 */
	protected function getPaymentMethodCode()
	{
		return $this->vindiMethodCode;
	}

	/**
	 * @param int $customerId
	 *
	 * @return array|bool
	 */
	protected function createPaymentProfile($customerId)
	{
		$payment = $this->getInfoInstance();

		$cardData = array(
			'holder_name'          => $payment->getCcOwner(),
			'card_expiration'      => str_pad($payment->getCcExpMonth(), 2, '0', STR_PAD_LEFT)
				. '/' . $payment->getCcExpYear(),
			'card_number'          => $payment->getCcNumber(),
			'card_cvv'             => $payment->getCcCid() ?: '000',
			'customer_id'          => $customerId,
			'payment_company_code' => $payment->getCcType(),
			'payment_method_code'  =>  $this->getPaymentMethodCode()
		);

		$paymentProfile = $this->api()->createCustomerPaymentProfile($cardData);

		if ($paymentProfile === false) {
			$this->error(
				'Erro ao informar os dados do Cartão. Verifique os dados e tente novamente!'
			);

			return false;
		}

		return $paymentProfile;
	}

	protected function processCardInformation($payment, $customerId, $customerVindiId)
	{
		if ('bank_slip' == $this->vindiMethodCode || 'debit_card' == $this->vindiMethodCode) {
			return true;
		}


		if ($payment->getAdditionalInformation('use_saved_cc')) {
			$this->assignDataFromPreviousPaymentProfile($customerVindiId);
			return true;
		}

		$paymentProfile = $this->createPaymentProfile($customerId);
		if ($paymentProfile) {
			return $this->verifyPaymentProfile($paymentProfile);
		}

		return false;
	}

	public function processPaidReturn($bill, $order = null)
	{
		if ('paid' != $bill['status']) {
			return false;
		}
		$payment = $this->getPaymentSender($order);
		$charge = $bill['charges'][0];
		$additionalInfo = [];

		if ('PaymentMethod::CreditCard' == $charge['payment_method']['type']) {
			$additionalInfo['installments'] = $bill['installments'];
		}

		$nsu = $this->getAcquirerId($charge['last_transaction']['gateway_response_fields']);
		if ($nsu) {
			$additionalInfo['nsu'] = $nsu;
		}

		$payment->setAdditionalInformation($additionalInfo);
		return true;
	}

	public function getPaymentSender($order)
	{
		if (is_null($order))
			return $this->getInfoInstance();

		return $order->getPayment();
	}

	public function getAcquirerId($responseFields)
	{
		$possibles = array(
			'nsu',			// Cielo 1.5
			'proof_of_sale',  	// Cielo v3
			'NumSqn',               // E-rede SOAP
			'tid',            	// E-rede
			'Tid',			// Rede komerci
			'stone_id_tx_id_tx_ref' // Stone
		);

		$nsu = '';

		foreach ($possibles as $nsuField) {
			if ($responseFields[$nsuField]) {
				$nsu = $responseFields[$nsuField];
				break;
			}
		}

		return $nsu;
	}

	protected function verifyPaymentProfile($paymentProfile)
	{
		$isVerifyEnabled = Mage::getStoreConfig('vindi_subscription/general/verify_method');

		if ($isVerifyEnabled && 'credit_card' == $this->_code) {
			if (!$this->verifyCreditCard($paymentProfile['payment_profile']['id'])) {
				$this->error('Não foi possível realizar a validação do seu Cartão!');
				return false;
			}
		}

		return true; 
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
		return Mage::getStoreConfig('payment/' . $this->_code . '/active')
		&& Mage::helper('vindi_subscription')->getKey()
		&& ! is_null($quote);
	}
}
