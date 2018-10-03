<?php

namespace Magium\Magento\Actions\Checkout\Steps;

use Magium\AbstractTestCase;
use Magium\Magento\AbstractMagentoTestCase;
use Magium\Magento\Themes\OnePageCheckout\AbstractThemeConfiguration;
use Magium\WebDriver\ExpectedCondition;
use Magium\WebDriver\WebDriver;

class PlaceOrder implements StepInterface
{
    const ACTION = 'Checkout\Steps\PlaceOrder';
    protected $webdriver;
    protected $theme;
    protected $testCase;

    public function __construct(
        WebDriver                   $webdriver,
        AbstractThemeConfiguration          $theme,
        AbstractMagentoTestCase     $testCase
    ) {
        $this->webdriver    = $webdriver;
        $this->theme        = $theme;
        $this->testCase     = $testCase;
    }


    public function execute()
    {
        $this->testCase->assertElementExists($this->theme->getPlaceOrderButtonXpath(), AbstractTestCase::BY_XPATH);
        $this->testCase->assertElementDisplayed($this->theme->getPlaceOrderButtonXpath(), AbstractTestCase::BY_XPATH);

        return true;
    }

    public function nextAction()
    {
        $this->webdriver->byXpath($this->theme->getPlaceOrderButtonXpath())->click();
        $this->webdriver->wait(120)->until(
            ExpectedCondition::elementExists(
                $this->theme->getOrderReceivedCompleteXpath(),
                AbstractTestCase::BY_XPATH
            )
        );
        return true;
    }
}
