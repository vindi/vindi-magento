<?php

namespace VindiTest\Functional\Block\Config;

use Mage;
use Magium\Assertions\Browser\CurrentUrlIsHttps;
use Magium\Magento\AbstractMagentoTestCase;
use Magium\Magento\Actions\Admin\Login\Login;
use Magium\Magento\Navigators\Admin\AdminMenu;
use Magium\Magento\Navigators\Admin\SystemConfiguration;

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
    public function testChecaApiKeyEMerchantRegistradoComSucesso()
    {
        $this->getLogger()->notice('Testando a ativação do módulo');
        $this->commandOpen($this->getTheme('Admin\ThemeConfiguration')->getBaseUrl());
        $this->getAction(Login::ACTION)->login();
        $this->getNavigator(AdminMenu::NAVIGATOR)->navigateTo('System/Configuration');
        $this->getNavigator(SystemConfiguration::NAVIGATOR)->navigateTo('Vindi Assinaturas/Configuração');
        $this->assertPageHasText('Conectado com Sucesso!');
        $this->assertPageHasText(Mage::helper('vindi_subscription/api')->getMerchant()['name']);
        $this->assertEquals(Mage::helper('vindi_subscription')->getKey(),
            $this->byId('vindi_subscription_general_api_key')->getAttribute('value'));
    }

    /**
     * Teste da ativação de cartão de crédito da Vindi
     */
    public function testChecaCartaoDeCreditoHabilitado()
    {
        $this->getLogger()->notice('Testando a ativação de cartão de crédito');
        $this->commandOpen($this->getTheme('Admin\ThemeConfiguration')->getBaseUrl());
        $this->getAction(Login::ACTION)->login();
        $this->getNavigator(AdminMenu::NAVIGATOR)->navigateTo('System/Configuration');
        $this->getNavigator(SystemConfiguration::NAVIGATOR)->navigateTo('Payment Methods/Vindi - Cartão de Crédito');
        $this->assertEquals(1, $this->byId('payment_vindi_creditcard_active')->getAttribute('value'));
    }

    /**
     * Teste de SSL do front da loja do magento
     */
    public function testChecaSslNoFront()
    {
        $this->commandOpen($this->getTheme()->getBaseUrl());
        $assertion = $this->getAssertion(CurrentUrlIsHttps::ASSERTION);
        $assertion->assert();
    }

    /**
     * Teste de SSL da administração da loja do magento
     */
    public function testChecaSslNoBack()
    {
        $this->commandOpen($this->getTheme('Admin\ThemeConfiguration')->getBaseUrl());
        $assertion = $this->getAssertion(CurrentUrlIsHttps::ASSERTION);
        $assertion->assert();
    }
}

