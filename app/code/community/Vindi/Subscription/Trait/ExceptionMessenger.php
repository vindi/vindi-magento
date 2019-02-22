<?php

trait Vindi_Subscription_Trait_ExceptionMessenger
{
    /**
     * @param string $errorMsg
     *
     * @return bool
     * @throws \Mage_Core_Exception
     */
    public function error($errorMsg)
    {
        Mage::throwException($errorMsg);
        return false;
    }
}
