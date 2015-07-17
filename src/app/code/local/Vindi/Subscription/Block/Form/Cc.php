<?php

class Vindi_Subscription_Block_Form_Cc extends Mage_Payment_Block_Form_Cc
{
    /**
     * Initialize block
     */
    public function _construct()
    {
        parent::_construct();
        $this->setTemplate('vindi_subscription/payment/form/cc.phtml');
    }

    /**
     * Retrieve available credit card types
     *
     * @return array
     */
    public function getCcAvailableTypes()
    {
        return $this->api()->getCreditCardTypes();
    }

    public function getSavedCc()
    {
        $customer = Mage::getSingleton('customer/session')->getCustomer();

        if (! $userCode = $customer->getVindiUserCode()) {
            return false;
        }

        return $this->api()->getCustomerPaymentProfile($userCode);
    }

    /**
     * @return Vindi_Subscription_Helper_API
     */
    private function api()
    {
        return Mage::helper('vindi_subscription/api');
    }

}
