<?php

class Vindi_Subscription_Helper_Logger
{
    /**
     * @param string   $message
     * @param int|null $level
     */
    public function log($message, $level = null)
    {
        Mage::log($message, $level, 'vindi_webhooks.log');

        switch ($level) {
            case 4:
                http_response_code(422);
                return false;
                break;
            case 5:
                return false;
                break;
            default:
                return true;
                break;
        }
    }
}