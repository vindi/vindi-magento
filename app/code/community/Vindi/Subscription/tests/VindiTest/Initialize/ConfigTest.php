<?php

namespace VindiTest\Functional\Block\Config;

use Magium\Magento\AbstractMagentoTestCase;
use Magium\Magento\Actions\Admin\Configuration\SettingModifier;
use Magium\Magento\Actions\Admin\Login\Login;
use Magium\Magento\Navigators\Admin\AdminMenu;
use Magium\Magento\Navigators\Admin\SystemConfiguration;

/**
 * Class SystemTest
 *
 * @package VindiTest\Block\Config
 */
class ConfigTest extends AbstractMagentoTestCase
{

    /**
     * Teste do registro da API Key da Vindi
     */
    public function testRegisterAddApiKey()
    {
        $this->getLogger()->notice('Testando a ativação do módulo');
        $this->commandOpen($this->getTheme('Admin\ThemeConfiguration')->getBaseUrl());
        $this->getAction(Login::ACTION)->login();
        $this->getNavigator(AdminMenu::NAVIGATOR)->navigateTo('System/Configuration');
        $this->getAction(SettingModifier::ACTION)->set(
            'Vindi Assinaturas/Configuração::label=Chave da API',
            getenv('API_KEY'),
            true
        );
    }

    /**
     * Teste de mudança de ambiente
     */
    public function testSetSandboxEnvironment()
    {
        $this->getLogger()->notice('Testando a mudança de ambiente de produção');
        $this->commandOpen($this->getTheme('Admin\ThemeConfiguration')->getBaseUrl());
        $this->getAction(Login::ACTION)->login();
        $this->getNavigator(AdminMenu::NAVIGATOR)->navigateTo('System/Configuration');
        $this->getAction(SettingModifier::ACTION)->set(
            'Vindi Assinaturas/Configuração::label=Modo',
            'https://sandbox-app.vindi.com.br/api/v1/',
            true
        );
    }

    /**
     * Teste de ativação de cartão de crédito da Vindi
     */
    public function testEnableCreditCardPaymentMethod()
    {
        $this->getLogger()->notice('Testando a ativação do cartão de crédito');
        $this->commandOpen($this->getTheme('Admin\ThemeConfiguration')->getBaseUrl());
        $this->getAction(Login::ACTION)->login();
        $this->getNavigator(AdminMenu::NAVIGATOR)->navigateTo('System/Configuration');
        $this->getNavigator(SystemConfiguration::NAVIGATOR)->navigateTo('Vindi Assinaturas/Configuração');
        $this->getAction(SettingModifier::ACTION)->set(
            'Payment Methods/Vindi - Cartão de Crédito::label=Ativo',
            '1',
            true
        );
    }

    /**
     * Teste de ativação de boleto bancário da Vindi
     */
    public function testEnableBankPaySlipPaymentMethod()
    {
        $this->getLogger()->notice('Testando a configuração máxima de parcelamento');
        $this->commandOpen($this->getTheme('Admin\ThemeConfiguration')->getBaseUrl());
        $this->getAction(Login::ACTION)->login();
        $this->getNavigator(AdminMenu::NAVIGATOR)->navigateTo('System/Configuration');
        $this->getNavigator(SystemConfiguration::NAVIGATOR)->navigateTo('Vindi Assinaturas/Configuração');
        $this->getAction(SettingModifier::ACTION)->set(
            'Payment Methods/Vindi - Boleto Bancário::label=Ativo',
            '1',
            true
        );
    }

    /**
     * Teste de ativação de cartão de débito da Vindi
     */
    public function testEnableDebitCardPaymentMethod()
    {
        $this->getLogger()->notice('Testando a configuração máxima de parcelamento');
        $this->commandOpen($this->getTheme('Admin\ThemeConfiguration')->getBaseUrl());
        $this->getAction(Login::ACTION)->login();
        $this->getNavigator(AdminMenu::NAVIGATOR)->navigateTo('System/Configuration');
        $this->getNavigator(SystemConfiguration::NAVIGATOR)->navigateTo('Vindi Assinaturas/Configuração');
        $this->getAction(SettingModifier::ACTION)->set(
            'Payment Methods/Vindi - Cartão de Débito::label=Ativo',
            '1',
            true
        );
    }

    /**
     * Teste de ativação de parcelamento da Vindi
     */
    public function testEnableInstallment()
    {
        $this->getLogger()->notice('Testando a ativação do parcelamento');
        $this->commandOpen($this->getTheme('Admin\ThemeConfiguration')->getBaseUrl());
        $this->getAction(Login::ACTION)->login();
        $this->getNavigator(AdminMenu::NAVIGATOR)->navigateTo('System/Configuration');
        $this->getNavigator(SystemConfiguration::NAVIGATOR)->navigateTo('Vindi Assinaturas/Configuração');
        $this->getAction(SettingModifier::ACTION)->set(
            'Payment Methods/Vindi - Cartão de Crédito::label=Habilitar parcelamento',
            '1',
            true
        );
    }

    /**
     * Teste de configuração de número máximo de parcelamento da Vindi
     */
    public function testSetMaximumNumberOfInstallmentsOf12()
    {
        $this->getLogger()->notice('Testando a configuração máxima de parcelamento');
        $this->commandOpen($this->getTheme('Admin\ThemeConfiguration')->getBaseUrl());
        $this->getAction(Login::ACTION)->login();
        $this->getNavigator(AdminMenu::NAVIGATOR)->navigateTo('System/Configuration');
        $this->getNavigator(SystemConfiguration::NAVIGATOR)->navigateTo('Vindi Assinaturas/Configuração');
        $this->getAction(SettingModifier::ACTION)->set(
            'Payment Methods/Vindi - Cartão de Crédito::label=Número máximo de parcelas',
            '12',
            true
        );
    }
}

