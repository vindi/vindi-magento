<?php

class Mage_Payment_Model_Method_Abstract
{
    public function getInfoInstance()
    {
        return new Mage_Payment_Model_Method_Abstract();
    }

    public function getCcOwner()
    {
        return 'teste';
    }

    public function getCcExpMonth()
    {
        return 12;
    }

    public function getCcExpYear()
    {
        return 2025;
    }

    public function getCcNumber()
    {
        return '4111 1111 1111 1111';
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
        return 123;
    }
}
