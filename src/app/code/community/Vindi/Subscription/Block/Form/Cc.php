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
    public function getCcAvailableTypes()
    {
        return $this->api()->getCreditCardTypes();
    }

    /**
     * @return bool
     */
    public function getSavedCc()
    {
        $customer = $this->getCustomer();

        if (! $userCode = $customer->getVindiUserCode()) {
            return false;
        }

        return $this->api()->getCustomerPaymentProfile($userCode);
    }

    /**
     * @return boll
     */
    public function isInstallmentsAllowedInStore()
    {    
        $allowedStores = Mage::getStoreConfig('payment/vindi_creditcard/installments_per_store_view');
        $activeStore = Mage::app()->getStore()->getStoreId();
          
        return in_array($activeStore, explode(',', $allowedStores));
    }

    /**
     * @return bool|string
     */
    public function getInstallments()
    {
        $allowInstallments          = $this->isInstallmentsAllowedInStore();
        $maxInstallmentsNumber      = Mage::getStoreConfig('payment/vindi_creditcard/max_installments_number');
        $minInstallmentsValue       = Mage::getStoreConfig('payment/vindi_creditcard/min_installment_value');
        $quote                      = $this->getQuote();
        $installments               = false;
     
            if ($this->isSingleQuote($quote) && $maxInstallmentsNumber > 1 && $allowInstallments == true) {
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
