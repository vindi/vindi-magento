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

        $quote = Mage::getSingleton('checkout/session')->getQuote();
        if (! $this->countSubscriptions($quote)) {
            return;
        }

        $data = $observer->getEvent()->getInfo();

        foreach ($data as $itemId => $itemInfo) {
            $item = $quote->getItemById($itemId);
            if (! $item || ! $this->isSubscription($item->getProduct())) {
                continue;
            }

            if (! ($qty = isset($itemInfo['qty']) ? (float) $itemInfo['qty'] : false)) {
                continue;
            }

            if ($qty > 1) {
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

        $quote = Mage::getSingleton('checkout/session')->getQuote();
        /** @var Mage_Catalog_Model_Product $product */
        $product = $observer->getEvent()->getProduct();

        $itemsCount = $quote->getItemsCount();
        $itemsSummaryQty = $quote->getItemsSummaryQty();

        if (! $itemsCount && ($itemsSummaryQty === 1)) {
            return;
        }

        if ($this->isSubscription($product)) {
            if ($this->countSubscriptions($quote) > 1) {
                $this->addNotice('Você pode fazer apenas uma assinatura por vez.<br />
                             Conclua a compra da assinatura ou remova-a do carrinho.');
            }

            if (($itemsCount === 1) && ($itemsSummaryQty > 1)) {
                $this->addNotice('Você pode fazer apenas uma assinatura por vez.<br />
                             Por favor, tente novamente.');
            }

            $this->addNotice('Você não pode adicionar assinaturas e outros tipos de produtos em um mesmo carrinho.<br />
                            Conclua a compra dos produtos ou remova-os do carrinho.');

        }

        if ($this->countSubscriptions($quote)) {
            $this->addNotice('Você não pode adicionar assinaturas e outros tipos de produtos em um mesmo carrinho.<br />
                                     Conclua a compra da assinatura ou remova-a do carrinho.');
        }

        return;
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

