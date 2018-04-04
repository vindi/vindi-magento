<?php

/**
 * Credit card generic payment info
 */
class Vindi_Subscription_Block_Info_Cc extends Mage_Payment_Block_Info_Cc
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('vindi_subscription/payment/info/vindi_subscription.phtml');
    }

    protected function getInstallments()
    {
        return $this->getInfo()->getAdditionalInformation('installments');
    }

    protected function getNsu()
    {
        return $this->getInfo()->getAdditionalInformation('nsu');
    }
}
