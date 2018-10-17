<?php

namespace Vindi\Navigators\Admin;

use Vindi\Actions\Admin\WaitForPageLoaded;
use Vindi\Navigators\BaseMenu;
use Vindi\Themes\Admin\ThemeConfiguration;
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
