<?php

class Vindi_Subscription_WebhookController extends Mage_Core_Controller_Front_Action
{
    /**
     * @param string   $message
     * @param int|null $level
     */
    private function log($message, $level = null)
    {
        Mage::log($message, $level, 'vindi_webhooks.log');
    }

    /**
     * The route that webhooks will use.
     */
    public function indexAction()
    {
        if (! $this->validateRequest()) {
            $ip = Mage::helper('core/http')->getRemoteAddr();

            $this->log(sprintf('Invalid webhook attempt from IP %s', $ip), Zend_Log::WARN);
            $this->norouteAction();

            return;
        }

        $body = file_get_contents('php://input');
        $this->log("Novo evento dos webhooks!\n{$body}");
    }

    /**
     * Validate the webhook for security reasons.
     *
     * @return bool
     */
    private function validateRequest()
    {
        $systemKey = Mage::helper('vindi_subscription')->getHashKey();
        $requestKey = $this->getRequest()->getParam('key');

        return $systemKey === $requestKey;
    }
}

