<?php

class Vindi_Subscription_Model_CreditCard extends Vindi_Subscription_Model_PaymentMethod
{
	/**
	 * @var string
	 */
	protected $_code = 'vindi_creditcard';

	/**
	 * @var string
	 */
	protected $vindiMethodCode = 'credit_card';

	/**
	 * @var string
	 */
	protected $saveMethod = 'use_saved_cc';

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
	 * @param mixed $info|$data
	 *
	 * @return Mage_Payment_Model_Method_Abstract|null
	 */
	public function loadAttributes($info, $data)
	{
		if ('saved' === $data->getCcChoice()) {
			$info->setAdditionalInformation('PaymentMethod', $this->_code)
				->setAdditionalInformation($this->saveMethod, true);

			return $this;
		}

		$info->setCcType($data->getCcType())
			->setCcTypeName($data->getCcTypeName())
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
			->setAdditionalInformation($this->saveMethod, false);
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

		$ccNumber = $info->getCcNumber();

		// remove credit card non-numbers
		$ccNumber = preg_replace('/\D/', '', $ccNumber);

		$info->setCcNumber($ccNumber);

		return $this;
	}
}
