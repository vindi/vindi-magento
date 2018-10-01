<?php

namespace VindiTest\Functional\Block\Config;

use Magium\Magento\AbstractMagentoTestCase;
use Magium\Magento\Actions\Admin\Configuration\SettingModifier;
use Magium\Magento\Actions\Admin\Login\Login;
use Magium\Magento\Navigators\Admin\AdminMenu;

/**
 * Class SystemTest
 *
 * @package VindiTest\Block\Config
 */
class InitializeFunctionalTest extends AbstractMagentoTestCase
{

    /**
     * Teste do registro da API Key da Vindi
     */
    public function testAddApiKeyRegistered()
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
}

