<?php

namespace Magium\Magento\Navigators\Admin;

use Magium\Magento\Actions\Admin\WaitForPageLoaded;
use Magium\Magento\Navigators\BaseMenu;
use Magium\Magento\Themes\Admin\ThemeConfiguration;
use Magium\Util\Log\Logger;
use Magium\WebDriver\WebDriver;

class AdminMenu extends BaseMenu
{
    const NAVIGATOR = 'Admin\AdminMenu';

    public function __construct(
        ThemeConfiguration $theme,
        WebDriver $webdriver,
        WaitForPageLoaded $loaded,
        Logger $logger)
    {
        parent::__construct($theme, $webdriver, $loaded, $logger);
    }
    
    
    
}
