<?php

namespace Magium\Magento\Actions\Checkout\Steps;

use Facebook\WebDriver\WebDriverExpectedCondition;
use Magium\AbstractTestCase;
use Magium\Magento\AbstractMagentoTestCase;
use Magium\Magento\Identities\Customer;
use Magium\Magento\Themes\OnePageCheckout\AbstractThemeConfiguration;
use Magium\WebDriver\ExpectedCondition;
use Magium\WebDriver\WebDriver;

class ShippingAddress implements StepInterface
{
    const ACTION = 'Checkout\Steps\ShippingAddress';

    protected $webdriver;
    protected $theme;
    protected $customerIdentity;
    protected $testCase;
    protected $bypassNextStep = false;

    protected $enterNewAddress = false;

    public function __construct(
        WebDriver                   $webdriver,
        AbstractThemeConfiguration          $theme,
        Customer                    $customerIdentity,
        AbstractMagentoTestCase     $testCase
    ) {
        $this->webdriver        = $webdriver;
        $this->theme            = $theme;
        $this->customerIdentity = $customerIdentity;
        $this->testCase         = $testCase;
    }

    public function enterNewAddress($newAddress = true)
    {
        $this->enterNewAddress = $newAddress;
    }

    protected function preExecute()
    {
        if ($this->enterNewAddress) {
            $this->webdriver->wait()->until(
                ExpectedCondition::visibilityOf(
                    $this->webdriver->byXpath($this->theme->getShippingNewAddressXpath())
                )
            );
            $this->webdriver->byXpath($this->theme->getShippingNewAddressXpath())->click();
            $this->webdriver->wait()->until(
                ExpectedCondition::visibilityOf(
                    $this->webdriver->byXpath($this->theme->getShippingFirstNameXpath())
                )
            );
        }
        // We will bypass ourself if the billing address is the same as the shipping address.
        if (!$this->webdriver->elementDisplayed($this->theme->getShippingFirstNameXpath(), AbstractTestCase::BY_XPATH)) {
            $this->bypassNextStep = true;
            return true;
        }
        return false;
    }

    protected function sendData($xpath, $data)
    {
        if ($xpath) {
            $this->testCase->byXpath($xpath)->clear();
            $this->testCase->byXpath($xpath)->sendKeys($data);
        }
    }

    public function execute()
    {
        if ($this->preExecute()) {
            return true;
        }

        $this->sendData($this->theme->getShippingFirstNameXpath(), $this->customerIdentity->getShippingFirstName());
        $this->sendData($this->theme->getShippingLastNameXpath(), $this->customerIdentity->getShippingLastName());
        $this->sendData($this->theme->getShippingCompanyXpath(), $this->customerIdentity->getShippingCompany());
        $this->sendData($this->theme->getShippingAddressXpath(), $this->customerIdentity->getShippingAddress());
        $this->sendData($this->theme->getShippingAddress2Xpath(), $this->customerIdentity->getShippingAddress2());
        $this->sendData($this->theme->getShippingCityXpath(), $this->customerIdentity->getShippingCity());


        $countryXpath = $this->theme->getShippingCountryIdXpath($this->customerIdentity->getShippingCountryId());
        if ($countryXpath) {
            $this->testCase->byXpath($countryXpath)->click();
        }

        $regionXpath = $this->theme->getShippingRegionIdXpath($this->customerIdentity->getShippingRegionId());
        if ($regionXpath) {
            $this->testCase->byXpath($regionXpath)->click();
        }

        $this->sendData($this->theme->getShippingPostCodeXpath(), $this->customerIdentity->getShippingPostCode());


        $this->sendData($this->theme->getShippingTelephoneXpath(), $this->customerIdentity->getShippingTelephone());
        $this->sendData($this->theme->getShippingFaxXpath(), $this->customerIdentity->getShippingFax());
        
        return true;
    }

    public function nextAction()
    {
        if ($this->bypassNextStep) {
            return true;
        }
        $this->testCase->byXpath($this->theme->getShippingContinueButtonXpath())->click();

        $this->webdriver->wait()->until(WebDriverExpectedCondition::not(WebDriverExpectedCondition::visibilityOf($this->webdriver->byXpath($this->theme->getShippingContinueCompletedXpath()))));
        return true;
    }
}