<?php

class Vindi_Subscription_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * Return Vindi API Key from config or false otherwhise.
     *
     * @return bool
     */
    public function getKey()
    {
        return Mage::getStoreConfig('vindi_subscription/general/api_key') ?: false;
    }

}