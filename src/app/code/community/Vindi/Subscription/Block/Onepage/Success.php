<?php

class Vindi_Subscription_Block_Onepage_Success extends Mage_Checkout_Block_Onepage_Success
{
    public function getBankSlipDownload()
    {
        $order = Mage::getModel('sales/order')->loadByIncrementId($this->getOrderId());
        if ($order->getPayment()->getMethod() == "vindi_bankslip") {
            /** @var Vindi_Subscription_Helper_API $api */
            $api = Mage::helper('vindi_subscription/api');
            return $api->getBankSlipDownload($order->getVindiBillId());
        }

        return null;
    }
}