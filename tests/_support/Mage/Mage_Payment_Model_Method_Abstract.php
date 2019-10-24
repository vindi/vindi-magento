<?php

class Mage_Payment_Model_Method_Abstract
{
    public function getInfoInstance()
    {
        return new Mage_Payment_Model_Method_Abstract();
    }

    public function getCcOwner()
    {
        return 'holder name';
    }

    public function getCcExpMonth()
    {
        return 12;
    }

    public function getCcExpYear()
    {
        return date('Y', strtotime('+4 years'));
    }

    public function getCcNumber()
    {
        return '4111111111111111';
    }

    public function getCcCid()
    {
        return 123;
    }

    public function getCcType()
    {
        return 'visa_electron';
    }

    public function setPaymentProfile()
    {
        return true;
    }
}
