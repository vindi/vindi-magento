<?php

namespace Magium\Magento\Navigators\Admin;

use Magium\Magento\AbstractMagentoTestCase;
use Magium\Magento\Actions\Admin\Login\Login;
use Magium\Magento\Actions\Admin\Tables\ClearTableFilters;
use Magium\Magento\Actions\Admin\Tables\ClickButton;
use Magium\Magento\Actions\Admin\WaitForLoadingMask;
use Magium\Magento\Themes\Admin\ThemeConfiguration;
use Magium\Navigators\NavigatorInterface;
use Magium\WebDriver\ExpectedCondition;
use Magium\WebDriver\WebDriver;

class Order implements NavigatorInterface
{
    const NAVIGATOR = 'Admin\Order';

    protected $webDriver;
    protected $themeConfiguration;
    protected $adminLogin;
    protected $adminMenuNavigator;
    protected $clearTableFilters;
    protected $clickButton;
    protected $waitForLoadingMask;
    protected $testCase;

    public function __construct(
        WebDriver                   $webDriver,
        ThemeConfiguration     $themeConfiguration,
        Login                       $adminLogin,
        AdminMenu          $adminMenuNavigator,
        ClearTableFilters           $clearTableFilters,
        ClickButton                 $clickButton,
        WaitForLoadingMask          $waitForLoadingMask,
        AbstractMagentoTestCase     $testCase
    )
    {
        $this->webDriver                = $webDriver;
        $this->themeConfiguration       = $themeConfiguration;
        $this->adminLogin               = $adminLogin;
        $this->adminMenuNavigator       = $adminMenuNavigator;
        $this->clearTableFilters        = $clearTableFilters;
        $this->clickButton              = $clickButton;
        $this->waitForLoadingMask       = $waitForLoadingMask;
        $this->testCase                 = $testCase;
    }

    public function navigateTo($orderId, $doLoginIfNeeded = false)
    {
        if ($doLoginIfNeeded) {
            $this->adminLogin->login();
        }
        $this->adminMenuNavigator->navigateTo('Sales/Orders');

        $this->clearTableFilters->clear();

        $element = $this->webDriver->byId('sales_order_grid_filter_real_order_id');
        $element->sendKeys($orderId);

        $this->clickButton->click($this->themeConfiguration->getSearchButtonText());
        $this->testCase->sleep('100ms');
        $this->waitForLoadingMask->wait();

        $selectXpath = $this->themeConfiguration->getSelectOrderXpath($orderId);

        $this->testCase->assertElementDisplayed($selectXpath, WebDriver::BY_XPATH);
        $element = $this->webDriver->byXpath($selectXpath);

        $element->click();

        $this->webDriver->wait()->until(ExpectedCondition::titleContains($orderId));

    }

}