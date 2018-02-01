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
    private function isAdmin()
    {
        return Mage::app()->getStore()->isAdmin();
    }

    /**
     * @return \Mage_Customer_Model_Customer
     */
    private function getCustomer()
    {
        if ($this->isAdmin()) {
            return Mage::getSingleton('adminhtml/session_quote')->getCustomer();
        }

        return Mage::getSingleton('customer/session')->getCustomer();
    }

    /**
     * @return \Mage_Sales_Model_Quote
     */
    private function getQuote()
    {
        if ($this->isAdmin()) {
            return Mage::getSingleton('adminhtml/session_quote')->getQuote();
        }

        return Mage::getSingleton('checkout/session')->getQuote();
    }

    /**
     * Retrieve available credit card types
     *
     * @return array
     */
    public function getDcAvailableTypes()
    {
        return $this->api()->getCreditCardTypes();
    }
    /**
     * @return bool
     */
    public function getSavedDc()
    {
        $customer = $this->getCustomer();
        if (! $userCode = $customer->getVindiUserCode()) {
            return false;
        }
        return $this->api()->getCustomerPaymentProfile($userCode);
    }
    /**
     * @return int
     */
    public function installmentsOnSubscription()
    {
        $quote  = $this->getQuote();

        foreach($quote->getAllVisibleItems() as $item){
            $product = Mage::getModel('catalog/product')->load($item->getProductId());
            $plan = $product->getData('vindi_subscription_plan');
        }

        $installments = $this->api()->getPlanInstallments($plan);

        if(! $this->isSingleQuote($quote)) {

            return $installments;
        }
    }
    /**
     * @return bool
     */
    public function isInstallmentsAllowedInStore()
    {    
        return Mage::getStoreConfig('payment/vindi_creditcard/enable_installments');
    }
    /**
     * @return bool|string
     */
    public function getInstallments()
    {
        $allowInstallments          = $this->isInstallmentsAllowedInStore();
        $maxInstallmentsNumber      = $this->getMaxInstallmentsNumber();
        $minInstallmentsValue       = Mage::getStoreConfig('payment/vindi_creditcard/min_installment_value');
        $quote                      = $this->getQuote();
        $installments               = false;
     
            if ($maxInstallmentsNumber > 1 && $allowInstallments == true) {
                $total             = $quote->getGrandTotal();
                $installmentsTimes = floor($total / $minInstallmentsValue);
                $installments      = '<option value="">' . Mage::helper('catalog')->__('-- Please Select --') . '</option>';
                
                    for ($i = 1; $i <= $maxInstallmentsNumber; $i++) {
                        $value = ceil($total / $i * 100) / 100;
                        $price = Mage::helper('core')->currency($value, true, false);
                        $installments .= '<option value="' . $i . '">' . sprintf('%dx de %s', $i, $price) . '</option>';
                        if(($i + 1) > $installmentsTimes)
                            break;
                    }
            }
            
        return $installments;
    }
    /**
     *  @return int
     */    
    public function getMaxInstallmentsNumber()
    {
        $quote                      = $this->getQuote();
        $maxInstallmentsNumber = Mage::getStoreConfig('payment/vindi_creditcard/max_installments_number');
        $subscriptionInstallments   = $this->installmentsOnSubscription();

        if($this->isSingleQuote($quote)){
            return $maxInstallmentsNumber;
        } else{
            return $subscriptionInstallments;
        }    
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
    private function api()
    {
        return Mage::helper('vindi_subscription/api');
    }
}
