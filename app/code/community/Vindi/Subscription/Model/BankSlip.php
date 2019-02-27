<?php

class Vindi_Subscription_Model_BankSlip extends Vindi_Subscription_Model_PaymentMethod
{
	/**
	 * @var string
	 */
	protected $_code = 'vindi_bankslip';

	/**
	 * @var string
	 */
	protected $vindiMethodCode = 'bank_slip';

	/**
	 * @var bool
	 */
	protected $_canSaveCc = false;

	/**
	 * @var string
	 */
	protected $_formBlockType = 'vindi_subscription/form_bankSlip';

	/**
	 * Assign data to info model instance
	 *
	 * @param   mixed $data
	 *
	 * @return  Mage_Payment_Model_Method_Abstract
	 */
	public function assignData($data)
	{
		$info = $this->getInfoInstance();
		$info->setAdditionalInformation('PaymentMethod', $this->_code)
			->setAdditionalInformation('installments', 1);

		return $this;
	}
}
