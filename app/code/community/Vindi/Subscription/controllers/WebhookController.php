<?php

class Vindi_Subscription_WebhookController extends Mage_Core_Controller_Front_Action
{
    /**
     * The route that webhooks will use.
     */
    public function indexAction()
    {
        /** @var Vindi_Subscription_Helper_WebhookHandler $handler */
        $handler = Mage::helper('vindi_subscription/webhookHandler');
        $logger = Mage::helper('vindi_subscription/logger');

        if (! $this->validateRequest()) {
            $ip = Mage::helper('core/http')->getRemoteAddr();

            $logger->log(sprintf('Invalid webhook attempt from IP %s', $ip), Zend_Log::WARN);
            $this->norouteAction();

            return false;
        }

        $body = file_get_contents('php://input');
        $logger->log(sprintf("Novo evento dos webhooks!\n%s", $body));

        return $handler->handle($body);
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

