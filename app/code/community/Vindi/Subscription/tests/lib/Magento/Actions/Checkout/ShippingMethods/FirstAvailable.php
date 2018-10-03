<?php

namespace Magium\Magento\Actions\Checkout\ShippingMethods;

use Facebook\WebDriver\WebDriverBy;
use Magium\AbstractTestCase;
use Magium\Magento\AbstractMagentoTestCase;
use Magium\Magento\Themes\OnePageCheckout\AbstractThemeConfiguration;
use Magium\WebDriver\ExpectedCondition;
use Magium\WebDriver\WebDriver;

class FirstAvailable implements ShippingMethodInterface
{
    const ACTION = 'Checkout\ShippingMethods\FirstAvailable';
    protected $webDriver;
    protected $theme;
    protected $testCase;

    public function __construct(
        WebDriver               $webDriver,
        AbstractThemeConfiguration      $theme,
        AbstractMagentoTestCase $testCase
    ) {
        $this->webDriver        = $webDriver;
        $this->theme            = $theme;
        $this->testCase         = $testCase;
    }

    public function choose($required)
    {
        $this->webDriver->wait()->until(
            ExpectedCondition::elementExists(
                $this->theme->getShippingMethodFormXpath(), AbstractTestCase::BY_XPATH
            )
        );

        // Some products, such as virtual products, do not get shipped
        if ($required) {
            $this->testCase->assertElementExists($this->theme->getDefaultShippingXpath(), AbstractTestCase::BY_XPATH);
            $this->testCase->assertElementDisplayed($this->theme->getDefaultShippingXpath(), AbstractTestCase::BY_XPATH);
        }

        if ($this->webDriver->elementDisplayed($this->theme->getDefaultShippingXpath(), AbstractTestCase::BY_XPATH)) {
            $xpath = $this->theme->getDefaultShippingXpath();
            $element = $this->webDriver->byXpath($xpath);
            $this->webDriver->action()->moveToElement($element);
            $this->webDriver->wait()->until(ExpectedCondition::elementToBeClickable(WebDriverBy::xpath($xpath)));
            $this->webDriver->byXpath($xpath)->click();
        }
    }

}
