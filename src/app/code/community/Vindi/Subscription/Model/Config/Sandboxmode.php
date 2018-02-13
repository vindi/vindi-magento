<?php

class Vindi_Subscription_Model_Config_Sandboxmode
{
    public function toOptionArray()
    {
        return array(
            array('value' => 'https://app.vindi.com.br/api/v1/', 'label'=>Mage::helper('adminhtml')->__("Production")),
            array('value' => 'https://sandbox-app.vindi.com.br/api/v1/', 'label'=>Mage::helper('adminhtml')->__('Sandbox')),
        );
    }
}