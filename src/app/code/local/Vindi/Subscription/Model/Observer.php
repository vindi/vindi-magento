<?php

class Vindi_Subscription_Model_Observer
{
    private $_helper;

    public function __construct()
    {
        $this->_helper = Mage::helper('vindi_subscription');
    }

    /**
     * @param Varien_Event_Observer $observer
     */
    public function checkQuantity($observer)
    {
        if (! $this->_helper->isModuleEnabled()) {
            return;
        }

        // TODO validate by product type
        $quote = Mage::getSingleton('checkout/session')->getQuote();

        if (($quote->getItemsCount() >= 1) || ($quote->getItemsSummaryQty() > 1)) {
            Mage::getSingleton('core/session')->addError('Você pode fazer apenas uma assinatura por vez.');
            Mage::app()->getFrontController()->getResponse()->setRedirect(Mage::getUrl('checkout/cart'));
            Mage::app()->getResponse()->sendResponse();
            exit;
        }
    }
}

