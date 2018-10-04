<?php

namespace Vindi\Actions\Checkout\Steps;

use Vindi\AbstractMagentoTestCase;
use Vindi\Identities\Customer;
use Vindi\Themes\OnePageCheckout\AbstractThemeConfiguration;
use Magium\WebDriver\WebDriver;

class NewCustomerPassword implements StepInterface
{

    const ACTION = 'Checkout\Steps\NewCustomerPassword';

    protected $webdriver;
    protected $theme;
    protected $customerIdentity;
    protected $testCase;

    protected $bypass = [];

    public function __construct(
        WebDriver                   $webdriver,
        AbstractThemeConfiguration          $theme,
        Customer            $customerIdentity,
        AbstractMagentoTestCase     $testCase
    ) {
        $this->webdriver        = $webdriver;
        $this->theme            = $theme;
        $this->customerIdentity = $customerIdentity;
        $this->testCase         = $testCase;
    }

    public function execute()
    {
        $this->testCase->assertElementDisplayed($this->theme->getPasswordInputXpath(), WebDriver::BY_XPATH);
        $this->testCase->assertElementDisplayed($this->theme->getConfirmPasswordInputXpath(), WebDriver::BY_XPATH);
        $this->webdriver->byXpath($this->theme->getPasswordInputXpath())->sendKeys($this->customerIdentity->getPassword());
        $this->webdriver->byXpath($this->theme->getConfirmPasswordInputXpath())->sendKeys($this->customerIdentity->getPassword());
        return true;
    }

    public function nextAction()
    {
        // Don't need to do anything here
        return true;
    }

}