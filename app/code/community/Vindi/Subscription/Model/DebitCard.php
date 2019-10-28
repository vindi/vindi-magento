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
	public $vindiMethodCode = 'debit_card';

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
	 * @param mixed $info|$data
	 *
	 * @return Mage_Payment_Model_Method_Abstract|null
	 */
	public function loadAttributes($info, $data)
	{
		if ('saved' === $data->getDcChoice()) {
			$info->setAdditionalInformation('PaymentMethod', $this->_code);

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
			->setAdditionalInformation('PaymentMethod', $this->_code);
	}

	/**
	 * Validate payment method information object
	 *
	 * @return  Mage_Payment_Model_Method_Abstract
	 */
	public function validate()
	{
		$info = $this->getInfoInstance();

		if ($info->getAdditionalInformation('use_saved_dc')) {
			return $this;
		}

		$dcNumber = $info->getCcNumber();

		// remove debit card non-numbers
		$dcNumber = preg_replace('/\D/', '', $dcNumber);

		$info->setCcNumber($dcNumber);

		return $this;
	}
}
