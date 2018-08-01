<?php

class Vindi_Subscription_Model_Observer
{
    /**
     * @var \Vindi_Subscription_Helper_Data
     */
    private $_helper;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->_helper = Mage::helper('vindi_subscription');
    }

    /**
     * @param Varien_Event_Observer $observer
     */
    public function updateItems($observer)
    {
        if (! $this->_helper->isModuleEnabled()) {
            return;
        }
        
        if ($this->countSubscriptions($this->getCartItems()) <= 1) {
            return;
        }

        $this->validateOrder($observer);
    }

    public function getCartItems ()
    {
        return Mage::getSingleton('checkout/session')->getQuote();
    }

    public function validateOrder ($observer) 
    {
        $data = $observer->getEvent()->getInfo();
        $lastVindiPlanId  = null;

        foreach ($data as $itemId => $itemInfo) {
            $item = $this->getCartItems()->getItemById($itemId);

            if (!$item || !$this->getCartItems()->getItemsCount() || !$this->isSubscription($item->getProduct())) {
                continue;
            }

            $vindiPlan = Mage::getModel('catalog/product')->load($item)->getData('vindi_subscription_plan');

            if ($lastVindiPlanId == null) {
                $lastVindiPlanId = $vindiPlan;
                continue; 
            }

            if ($lastVindiPlanId != $vindiPlan) {
                $this->addNotice('Você pode fazer apenas uma assinatura por vez.<br />
                    Conclua a compra da assinatura ou remova-a do carrinho.');
            }
        }

    }

    /**
     * @param Varien_Event_Observer $observer
     */
    public function addToCart($observer)
    {
        if (! $this->_helper->isModuleEnabled()) {
            return;
        }

        if (!$this->getCartItems()->getItemsCount() && $this->getCartItems()->getItemsSummaryQty() === 1) {
            return;
        }

        $this->validateOrder($observer);
    }

    /**
     * @param Varien_Event_Observer $observer
     */
    public function validateAdminHtmlOrder($observer)
    {
        /** @var Mage_Adminhtml_Model_Sales_Order_Create $order */
        $order = $observer['order_create_model'];
        $quote = $order->getQuote();
        $quote->collectTotals();

        $itemsCount = $quote->getItemsCount();
        $itemsSummaryQty = $quote->getItemsSummaryQty();
        $subscriptionsCount = $this->countSubscriptions($quote);

        if (! $subscriptionsCount) {
            return;
        }

        if (($subscriptionsCount > 1)) {
            Mage::throwException('Você pode fazer apenas uma assinatura por vez.<br />
                             Conclua a compra de uma única assinatura ou remova os excedentes dos itens.');
        }

        if (($itemsCount === 1) && ($itemsSummaryQty > 1)) {
            Mage::throwException('Você pode fazer apenas uma assinatura por vez.<br />
                             Por favor, tente novamente.');
        }

        if ($itemsCount > 1) {
            Mage::throwException('Você não pode adicionar assinaturas e outros tipos de produtos em um mesmo pedido.<br />
                                     Conclua a compra da assinatura ou remova-a do carrinho.');
        }
    }

    /**
     * @param $message
     */
    private function addNotice($message)
    {
        Mage::getSingleton('core/session')->addNotice($message);
        Mage::app()->getFrontController()->getResponse()->setRedirect(Mage::getUrl('checkout/cart'));
        Mage::app()->getResponse()->sendResponse();
        exit;
    }

    /**
     * @param Mage_Sales_Model_Quote $quote
     *
     * @return int
     */
    private function countSubscriptions($quote)
    {
        $count = 0;

        foreach ($quote->getAllVisibleItems() as $item) {
            if (($product = $item->getProduct()) && $this->isSubscription($product)) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     *
     * @return bool
     */
    private function isSubscription($product)
    {
        return $product->getTypeId() === 'subscription';
    }
}

