<?php

namespace VindiTest\Functional\Block\Config;

use Magium\Actions\SetFormValue;
use Magium\Assertions\Browser\CurrentUrlIsHttps;
use Magium\Magento\AbstractMagentoTestCase;
use Magium\Magento\Actions\Admin\Login\Login;
use Magium\Magento\Navigators\Admin\AdminMenu;
use Magium\Magento\Navigators\Admin\SystemConfiguration;
use \Mage;
use Magium\Magento\Actions\Admin\Configuration\SettingModifier;

/**
 * Class SystemTest
 *
 * @package VindiTest\Block\Config
 */
class InformationFunctionalTest extends AbstractMagentoTestCase
{

    /**
     * Teste do registro da API Key da Vindi
     */
    public function testAPiKeyRegistered()
    {
        $this->getLogger()->notice('Testando a ativação do módulo');
        $this->commandOpen($this->getTheme('Admin\ThemeConfiguration')->getBaseUrl());
        $this->getAction(Login::ACTION)->login();
        $this->getNavigator(AdminMenu::NAVIGATOR)->navigateTo('System/Configuration');
        $this->getAction(SettingModifier::ACTION)->set(
            'Vindi Assinaturas/Configuração::label=Chave da API',
            getenv('API_KEY')
        );
        $this->getNavigator(SystemConfiguration::NAVIGATOR)->navigateTo('Vindi Assinaturas/Configuração');
        $this->assertPageHasText('Conectado com Sucesso!');
        $this->assertPageHasText(Mage::helper('vindi_subscription/api')->getMerchant()['name']);
        $this->assertEquals(Mage::helper('vindi_subscription')->getKey(),
            $this->byId('vindi_subscription_general_api_key')->getAttribute('value'));
    }

    /**
     * Teste da ativação do módulo da Vindi
     */
    public function testEnablePaymentMethod()
    {
        $this->getLogger()->notice('Testando a ativação do módulo');
        $this->commandOpen($this->getTheme('Admin\ThemeConfiguration')->getBaseUrl());
        $this->getAction(Login::ACTION)->login();
        $this->getNavigator(AdminMenu::NAVIGATOR)->navigateTo('System/Configuration');
        $this->getNavigator(SystemConfiguration::NAVIGATOR)->navigateTo('Payment Methods/Vindi - Cartão de Crédito');
        $this->assertEquals(1, $this->byId('payment_vindi_creditcard_active')->getAttribute('value'));
    }

    /**
     * Teste de SSL do front da loja do magento
     */
    public function testHttpsAssertionFront()
    {
        $this->commandOpen($this->getTheme()->getBaseUrl());
        $assertion = $this->getAssertion(CurrentUrlIsHttps::ASSERTION);
        $assertion->assert();
    }

    /**
     * Teste de SSL da administração da loja do magento
     */
    public function testHttpsAssertionBack()
    {
        $this->commandOpen($this->getTheme('Admin\ThemeConfiguration')->getBaseUrl());
        $assertion = $this->getAssertion(CurrentUrlIsHttps::ASSERTION);
        $assertion->assert();
    }
}

