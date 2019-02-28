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
				->setAdditionalInformation('use_saved_cc', true)
				->setAdditionalInformation('installments', $data->getCcInstallments());

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
			->setAdditionalInformation('use_saved_cc', false)
			->setAdditionalInformation('installments', $data->getCcInstallments());
	}

	/**
	 * @param int $paymentProfileId
	 *
	 * @return array|bool
	 */
	public function verifyCreditCard($paymentProfileId)
	{
		$verifyStatus = $this->api()->verifyCustomerPaymentProfile($paymentProfileId);
		return ($verifyStatus['transaction']['status'] === 'success');
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

		if ($this->isSingleOrder($info->getQuote())
			&& 'valid' != ($result = $this->validateInstallments($info))) {
			return $this->error($result);
		}

		if ($info->getAdditionalInformation('use_saved_cc')) {
			return $this;
		}

		// remove credit card non-numbers
		$ccNumber = preg_replace('/\D/', '', $info->getCcNumber());
		$info->setCcNumber($ccNumber);
		return $this;
	}

	public function validateInstallments($info)
	{
		$installments = $info->getAdditionalInformation('installments');

		if (! $installments) {
			return 'Você deve informar o número de parcelas.';
		}

		if ($installments != 1) { 
			$maxInstallmentNumber = Mage::getStoreConfig('payment/vindi_creditcard/max_installments_number');

			if ($installments > $maxInstallmentNumber) {
				return 'O número de parcelas selecionado é inválido.';
			}

			$minInstallmentsValue = Mage::getStoreConfig('payment/vindi_creditcard/min_installment_value');
			$installmentValue = ceil($info->getQuote()->getGrandTotal() / $installments * 100) / 100;

			if (($installmentValue < $minInstallmentsValue) && ($installments > 1)) {
				return 'O número de parcelas selecionado é inválido.';
			}
		}

		return 'valid';
	}
}
