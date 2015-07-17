<?php

class Vindi_Subscription_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * Return Vindi API Key from config or false otherwhise.
     *
     * @return string|bool
     */
    public function getKey()
    {
        return Mage::getStoreConfig('vindi_subscription/general/api_key') ?: false;
    }

    /**
     * Generate an URL for webhooks to use.
     *
     * @return string
     */
    public function getWebhookURL()
    {
        $key = $this->getHashKey();

        return Mage::getUrl('vindi_subscription/webhook', compact('key'));
    }

    /**
     * Generate an uniform salted hash for using as webhook's security validation.
     *
     * @return string
     */
    public function getHashKey()
    {
        return Mage::helper('core')->getHash('vindi-magento');
    }
}