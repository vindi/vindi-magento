<?php

namespace Magium\Magento\Actions\Admin\Configuration;

use Magium\AbstractTestCase;
use Magium\Magento\AbstractMagentoTestCase;
use Magium\Magento\Navigators\Admin\AdminMenu;
use Magium\Magento\Navigators\Admin\SystemConfiguration;
use Magium\Magento\Themes\Admin\ThemeConfiguration;
use Magium\WebDriver\WebDriver;

class Enabler
{
    const ACTION = 'Admin\Configuration\Enabler';

    protected $webDriver;
    protected $adminMenuNavigator;
    protected $systemConfigurationNavigator;
    protected $adminThemeConfiguration;
    protected $testCase;
    protected $save;

    protected $tab;
    protected $section;

    public function __construct(
        WebDriver                       $webDriver,
        AdminMenu              $adminMenuNavigator,
        SystemConfiguration     $systemConfigurationNavigator,
        ThemeConfiguration         $adminThemeConfiguration,
        AbstractMagentoTestCase         $testCase,
        Save                            $save
    ) {
        $this->webDriver                    = $webDriver;
        $this->adminMenuNavigator           = $adminMenuNavigator;
        $this->systemConfigurationNavigator = $systemConfigurationNavigator;
        $this->adminThemeConfiguration      = $adminThemeConfiguration;
        $this->testCase                     = $testCase;
        $this->save                         = $save;
    }

    protected function setting($setting)
    {
        $parts = explode('/', $setting, 2);
        $this->testCase->assertCount(2, $parts);
        $this->tab  = $parts[0];
        $this->section  = $parts[1];
    }

    protected function navigateToSystemConfiguration($setting)
    {
        $tabDisplayXpath = $this->adminThemeConfiguration->getSystemConfigTabsXpath($this->tab);
        if (!$this->webDriver->elementDisplayed($tabDisplayXpath, AbstractTestCase::BY_XPATH)) {
            $this->adminMenuNavigator->navigateTo('System/Configuration');
        }

        $sectionDisplayXpath = $this->adminThemeConfiguration->getSystemConfigSectionDisplayCheckXpath($this->section);
        if (!$this->webDriver->elementDisplayed($sectionDisplayXpath, AbstractTestCase::BY_XPATH)) {
            $this->systemConfigurationNavigator->navigateTo($setting);
        }
    }

    public function enable($setting, $save = true)
    {
        // @TODO Note that much of this functionality may end up being moved to a more general settings modification class
        $this->setting($setting);
        $this->navigateToSystemConfiguration($setting);
        $settingXpath = $this->adminThemeConfiguration->getSystemConfigToggleEnableXpath($this->section, 1);
        $element = $this->webDriver->byXpath($settingXpath);
        if ($element->getAttribute('selected') === null) {
            $element->click();
            if ($save) {
                $this->save->save();
            }
        }

    }

    public function disable($setting, $save = true)
    {
        // @TODO Note that much of this functionality may end up being moved to a more general settings modification class
        $this->setting($setting);
        $this->navigateToSystemConfiguration($setting);
        $settingXpath = $this->adminThemeConfiguration->getSystemConfigToggleEnableXpath($this->section, 0);
        $element = $this->webDriver->byXpath($settingXpath);
        if ($element->getAttribute('selected') === null) {
            $element->click();
            if ($save) {
                $this->save->save();
            }
        }

    }

}
