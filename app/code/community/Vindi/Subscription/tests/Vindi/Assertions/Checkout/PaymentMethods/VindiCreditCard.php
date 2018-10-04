<?php

namespace Vindi\Assertions\Checkout\PaymentMethods;

use Magium\Assertions\AbstractAssertion;

class VindiCreditCard extends AbstractAssertion
{

    public function assert()
    {
        $this->getTestCase()->assertElementExists('vindi_creditcard_cc_owner');
        $this->getTestCase()->assertElementExists('vindi_creditcard_cc_type');
        $this->getTestCase()->assertEquals('select', strtolower($this->webDriver->byId('vindi_creditcard_cc_type')->getTagName()));
        $this->getTestCase()->assertElementExists('vindi_creditcard_cc_number');
        $this->getTestCase()->assertElementExists('vindi_creditcard_expiration');
        $this->getTestCase()->assertElementExists('vindi_creditcard_expiration_yr');
    }

}