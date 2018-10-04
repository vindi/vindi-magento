<?php

namespace Vindi\Actions\Checkout\PaymentMethods;

use Facebook\WebDriver\WebDriverSelect;
use Vindi\AbstractMagentoTestCase;
use Vindi\Actions\Checkout\PaymentInformation;
use Vindi\Identities\Customer;
use Magium\WebDriver\WebDriver;

class VindiCreditCard implements PaymentMethodInterface
{

    const ACTION = 'Checkout\PaymentMethods\VindiCreditCard';

    protected $webDriver;
    protected $testCase;
    protected $paymentInformation;
    protected $customer;
    protected $assertion;

    public function __construct(
        WebDriver                   $webDriver,
        AbstractMagentoTestCase     $testCase,
        PaymentInformation          $paymentInformation,
        Customer                    $customer,
        \Vindi\Assertions\Checkout\PaymentMethods\VindiCreditCard $assertion
    ) {
        $this->webDriver    = $webDriver;
        $this->testCase     = $testCase;
        $this->paymentInformation = $paymentInformation;
        $this->customer = $customer;
        $this->assertion = $assertion;
    }


    public function getId()
    {
        return 'p_method_vindi_creditcard';
    }

    /**
     * Fills in the payment form, selecting it, if necessary
     *
     * @param $requirePayment
     */

    public function pay($requirePayment)
    {
        if ($requirePayment) {
            $this->testCase->assertElementExists($this->getId());
        }

        if ($this->webDriver->elementDisplayed($this->getId())) {
            $element = $this->webDriver->byId($this->getId());
            $this->webDriver->getMouse()->click($element->getCoordinates());
        }
        $this->assertion->assert();

        $select = new WebDriverSelect($this->webDriver->byXpath('//select[@name="payment[cc_installments]"]'));
        $select->selectByValue('1');

        $this->webDriver->byId('vindi_creditcard_cc_owner')->clear();
        $this->webDriver->byId('vindi_creditcard_cc_owner')->sendKeys(
            $this->customer->getBillingFirstName() . ' ' . $this->customer->getBillingLastName()
        );

        $select = new WebDriverSelect($this->webDriver->byId('vindi_creditcard_cc_type'));
        $select->selectByValue('visa');

        $this->webDriver->byId('vindi_creditcard_cc_number')->clear();
        $this->webDriver->byId('vindi_creditcard_cc_number')->sendKeys('4444444444444448');

        $select = new WebDriverSelect($this->webDriver->byId('vindi_creditcard_expiration'));
        $select->selectByValue('1');

        $select = new WebDriverSelect($this->webDriver->byId('vindi_creditcard_expiration_yr'));
        $select->selectByValue('2022');

        $this->webDriver->byId('vindi_creditcard_cc_cid')->clear();
        $this->webDriver->byId('vindi_creditcard_cc_cid')->sendKeys('123');
    }
}