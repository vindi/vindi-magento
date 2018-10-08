<?php

namespace VindiTest\Functional\Initialize;

use Vindi\AbstractMagentoTestCase;
use Vindi\Actions\Admin\Configuration\Enabler;
use Vindi\Actions\Admin\Login\Login;

class EnablerTest extends AbstractMagentoTestCase
{

    public function testHabilitarCartoesDeCreditoEDebitoEBoletoBancario()
    {
        $adminThemeConfiguration = $this->getTheme('Admin\ThemeConfiguration');
        /* @var $adminThemeConfiguration \Vindi\Themes\Admin\ThemeConfiguration */

        $this->getAction(Login::ACTION)->login();
        $enabler = $this->getAction(Enabler::ACTION);

        $enabler->enable('Payment Methods/Vindi - Cartão de Crédito');
        $settingXpath = $adminThemeConfiguration->getSystemConfigToggleEnableXpath('Vindi - Cartão de Crédito', 1);
        $element = $this->webdriver->byXpath($settingXpath);
        self::assertNotNull($element->getAttribute('selected'));

        $enabler->enable('Payment Methods/Vindi - Cartão de Débito');
        $settingXpath = $adminThemeConfiguration->getSystemConfigToggleEnableXpath('Vindi - Cartão de Débito', 1);
        $element = $this->webdriver->byXpath($settingXpath);
        self::assertNotNull($element->getAttribute('selected'));

        $enabler->enable('Payment Methods/Vindi - Boleto Bancário');
        $settingXpath = $adminThemeConfiguration->getSystemConfigToggleEnableXpath('Vindi - Boleto Bancário', 1);
        $element = $this->webdriver->byXpath($settingXpath);
        self::assertNotNull($element->getAttribute('selected'));

    }
}