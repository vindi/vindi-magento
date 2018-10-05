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
    public function testRegistrarApiKey()
    {
        $this->getLogger()->notice('Testando a ativação do módulo');
        $this->commandOpen($this->getTheme('Admin\ThemeConfiguration')->getBaseUrl());
        $this->getAction(Login::ACTION)->login();
        $this->getNavigator(AdminMenu::NAVIGATOR)->navigateTo('System/Configuration');
        $this->getAction(SettingModifier::ACTION)->set(
            'Vindi Assinaturas/Configuração::label=Modo',
            'https://sandbox-app.vindi.com.br/api/v1/',
            true
        );
        $this->getAction(SettingModifier::ACTION)->set(
            'Vindi Assinaturas/Configuração::label=Chave da API',
            getenv('API_KEY'),
            true
        );
        $this->getAction(SettingModifier::ACTION)->set(
            'Payment Methods/Vindi - Cartão de Crédito::label=Habilitar parcelamento',
            '1',
            true
        );
        $this->getAction(SettingModifier::ACTION)->set(
            'Payment Methods/Vindi - Cartão de Crédito::label=Número máximo de parcelas',
            '12',
            true
        );
    }

}

