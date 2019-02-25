<?php

class Vindi_Subscription_Model_PaymentMethod extends Mage_Payment_Model_Method_Abstract
{
	use Vindi_Subscription_Trait_PaymentProcessor;
	use Vindi_Subscription_Trait_ExceptionMessenger;

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
	 * @return  Mage_Payment_Model_Method_Abstract
	 */
	public function assignData($data)
	{
		if (! ($data instanceof Varien_Object)) {
			$data = new Varien_Object($data);
		}

		$info = $this->getInfoInstance();
		$quote = $info->getQuote();

		$this->loadAttributes($info, $data);

		return $this;
	}

	/**
	 * @return string
	 */
	protected function getPaymentMethodCode()
	{
		// TODO fix it to proper method code
		return $this->vindiMethodCode;
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
		&& Mage::helper('vindi_subscription')->getKey();
	}
}