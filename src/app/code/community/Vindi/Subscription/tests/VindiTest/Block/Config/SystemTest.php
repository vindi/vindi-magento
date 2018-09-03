<?php

namespace VindiTest\Block\Config;

use Magium\Assertions\Browser\CurrentUrlIsHttps;
use Magium\Magento\AbstractMagentoTestCase;
use Magium\Magento\Actions\Admin\Login\Login;
use Magium\Magento\Navigators\Admin\AdminMenu;
use Magium\Magento\Navigators\Admin\SystemConfiguration;


class SystemTest extends AbstractMagentoTestCase
{
    public function testEnablePaymentMethod()
    {
        $this->getLogger()->notice('Testando a ativação do módulo');
        $this->commandOpen($this->getTheme('Admin\ThemeConfiguration')->getBaseUrl());
        $this->getAction(Login::ACTION)->login();
        $this->getNavigator(AdminMenu::NAVIGATOR)->navigateTo('System/Configuration');
        $this->getNavigator(SystemConfiguration::NAVIGATOR)->navigateTo('Payment Methods/Vindi - Cartão de Crédito');
        self::assertEquals(1, $this->byId('payment_vindi_creditcard_active')->getAttribute('value'));
    }


    public function testAPiKeyRegistered()
    {
        $this->getLogger()->notice('Testando o registro da API Key no módulo');
        $this->getAction(Login::ACTION)->login();
        $this->getNavigator(AdminMenu::NAVIGATOR)->navigateTo('System/Configuration');
        $this->getNavigator(SystemConfiguration::NAVIGATOR)->navigateTo('Vindi Assinaturas/Configuração');
        $this->assertPageHasText('Conectado com Sucesso!');
    }

    public function testHttpsAssertionFront()
    {
        $this->commandOpen($this->getTheme()->getBaseUrl());
        $assertion = $this->getAssertion(CurrentUrlIsHttps::ASSERTION);
        $assertion->assert();
    }

    public function testHttpsAssertionBack()
    {
        $this->commandOpen($this->getTheme('Admin\ThemeConfiguration')->getBaseUrl());
        $assertion = $this->getAssertion(CurrentUrlIsHttps::ASSERTION);
        $assertion->assert();
    }

}
