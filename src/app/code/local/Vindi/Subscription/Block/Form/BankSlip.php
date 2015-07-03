<?php

class Vindi_Subscription_Block_Form_BankSlip extends Mage_Payment_Block_Form
{
    /**
     * Initialize block
     */
    public function _construct()
    {
        parent::_construct();
        $this->setTemplate('vindi_subscription/payment/form/bankslip.phtml');
    }

}