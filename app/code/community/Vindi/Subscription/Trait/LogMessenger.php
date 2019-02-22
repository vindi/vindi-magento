<?php

trait Vindi_Subscription_Trait_LogMessenger
{
    /**
     * Grava o histórico de Webhooks recebidos
     *
     * @param string   $message, int|null $level
     */
    public function log($message, $local = 'vindi_exception.log', $level = null)
    {
        Mage::log($message, $level, $local);

        $this->manageWebhook($level);
    }

    private function manageWebhook($level)
    {
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