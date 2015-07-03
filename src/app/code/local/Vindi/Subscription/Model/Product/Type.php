<?php

class Vindi_Subscription_Model_Product_Type extends Mage_Catalog_Model_Product_Type_Virtual
{
    /** @todo validate add to cart */
//    protected function _prepareProduct(Varien_Object $buyRequest, $product, $processMode)
//    {
//        if ($this->_isStrictProcessMode($processMode)) {
//            return Mage::helper('vindi_subscription')->__(
//                'Subscription %s cannot be added to cart. ',
//                $product->getName()
//            );
//        }
//
//        return parent::_prepareProduct($buyRequest, $product, $processMode);
//    }
}