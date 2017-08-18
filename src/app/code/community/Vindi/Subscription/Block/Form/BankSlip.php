<?php

class Vindi_Subscription_Block_Form_BankSlip extends Mage_Payment_Block_Form
{
    /**
     * Initialize block
     */
    public function _construct()
    {
        parent::_construct();
        $this->setTemplate('vindi_subscription/payment/form/bankslip.phtml');
    }
    /**
    * @return string
    */
    protected function getQuoteType()
    {
    	$quote = Mage::getSingleton('checkout/session')->getQuote();

    	foreach ($quote->getAllVisibleItems() as $item) {
            if (($product = $item->getProduct()) && ($product->getTypeId() === 'subscription')) {
                return 'subscription';
            }
        }

        return 'bill';
    }
    /**
    * @return string
    */
    public function setMessageBankSlip()
    {
    	if('subscription' == $this->getQuoteType($quote))
    		return 'enviado mensalmente';	
   
    		return 'enviado';
    }
}