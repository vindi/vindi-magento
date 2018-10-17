<?php

namespace Vindi\Actions\Admin;

use Vindi\Themes\Admin\ThemeConfiguration;
use Magium\WebDriver\WebDriver;

class WaitForPageLoaded extends \Magium\Actions\WaitForPageLoaded
{
    public function __construct(WebDriver $webDriver, ThemeConfiguration $themeConfiguration)
    {
        parent::__construct($webDriver, $themeConfiguration);
    }

}
