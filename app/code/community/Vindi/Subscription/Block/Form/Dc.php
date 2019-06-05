<?php

class Vindi_Subscription_Block_Form_Dc extends Mage_Payment_Block_Form_Cc
{
    /**
     * Initialize block
     */
    public function _construct()
    {
        parent::_construct();
        $this->setTemplate('vindi_subscription/payment/form/dc.phtml');
    }

    /**
     * @return bool
     */
    protected function isAdmin()
    {
        return Mage::app()->getStore()->isAdmin();
    }

    /**
     * @return \Mage_Customer_Model_Customer
     */
    protected function getCustomer()
    {
        if ($this->isAdmin()) {
            return Mage::getSingleton('adminhtml/session_quote')->getCustomer();
        }

        return Mage::getSingleton('customer/session')->getCustomer();
    }

    /**
     * @return \Mage_Sales_Model_Quote
     */
    protected function getQuote()
    {
        if ($this->isAdmin()) {
            return Mage::getSingleton('adminhtml/session_quote')->getQuote();
        }

        return Mage::getSingleton('checkout/session')->getQuote();
    }

    /**
     * Retrieve available debit card types
     *
     * @return array
     */
    public function getDcAvailableTypes()
    {
        return $this->api()->getDebitCardTypes();
    }
    /**
     * @return bool
     */
    public function getSavedDc()
    {
        $customer = $this->getCustomer();
        if (!$userCode = $customer->getVindiUserCode()) {
            return false;
        }
        return $this->api()->getCustomerPaymentProfile($userCode, Vindi_Subscription_Model_DebitCard::$METHOD);
    }

    /**
     * @param Mage_Sales_Model_Quote $quote
     *
     * @return bool
     */
    protected function isSingleQuote($quote)
    {
        foreach ($quote->getAllVisibleItems() as $item) {
            if (($product = $item->getProduct()) && ($product->getTypeId() === 'subscription')) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return Vindi_Subscription_Helper_API
     */
    protected function api()
    {
        return Mage::helper('vindi_subscription/api');
    }
}
