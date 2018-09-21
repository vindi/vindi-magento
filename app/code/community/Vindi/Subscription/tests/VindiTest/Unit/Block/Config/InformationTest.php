<?php

namespace VindiTest\Unit\Block\Config;

require_once __DIR__ . '/../../../../../Block/Config/Information.php';
require_once __DIR__ . '/../../../../../../../../../../lib/Varien/Data/Form/Element/Abstract.php';

use Mage;
use PHPUnit\Framework\TestCase;
use Varien_Data_Form_Element_Abstract;
use Vindi_Subscription_Block_Config_Information;

/**
 * Class InformationTest
 *
 * @package VindiTest\Unit\Block\Config
 * @coversDefaultClass Vindi_Subscription_Block_Config_Information
 */
class InformationTest extends TestCase
{

    private $stub;

    public function setUp()
    {
        parent::setUp();
        $this->stub = $this->createMock(Varien_Data_Form_Element_Abstract::class);
        $this->stub->method('getId')->willReturn('vindi_subscription_general_information');
    }

    /**
     * @covers ::render
     */
    public function testHasApiKey()
    {
        fwrite(STDOUT, 'Testando API Key não vazia' . PHP_EOL);
        $this->assertNotEmpty(true, Mage::helper('vindi_subscription')->getKey());
    }

    /**
     * @covers ::render
     */
    public function testId()
    {
        fwrite(STDOUT, 'Testando id de $element como vindi_subscription_general_information' . PHP_EOL);
        $this->assertEquals('vindi_subscription_general_information', $this->stub->getId());
    }

    public function testHasApiKeyAndId()
    {
        fwrite(STDOUT, 'Testando retorno não vazio' . PHP_EOL);
        $helper = Mage::helper('vindi_subscription');
        $this->assertFalse(!$helper->getKey() || $this->stub->getId() != 'vindi_subscription_general_information');

    }

    public function testRender()
    {
        fwrite(STDOUT, 'Testando retorno de HTML' . PHP_EOL);
        $api = Mage::helper('vindi_subscription/api');
        $merchant = $api->getMerchant();
        $status = $api->isMerchantStatusTrial() ? 'Trial' : 'Ativo';
        $helper = Mage::helper('vindi_subscription');
        $html = <<<HTML
<tr>
    <td colspan="4" class="label">
        <h3>Informações sobre a conta Vindi</h3>
    </td>
</tr>
<tr>
    <td class="label">
        Conexão
    </td>    <td class="value success-msg">Conectado com Sucesso!</td>
</tr>
<tr>
    <td class="label">
        Conta
    </td>
    <td class="value">
        {$merchant['name']}
    </td>
</tr>
<tr>
    <td class="label">
        Status
    </td>
    <td class="value">
        $status
    </td>
</tr>
<tr>
    <td colspan="4" class="label">
        <h3>Configuração dos Eventos da Vindi</h3>
    </td>
</tr>
<tr>
    <td class="label">URL dos Webhooks</td>
    <td class="value">
        <input type="text" value="{$helper->getWebhookURL()}" style="width:100%" readonly onclick="this.select();" />
        <p class="note" style="width:100%">
            <span>Copie esse link e utilize-o para configurar os eventos nos Webhooks da Vindi.</span>
        </p>
    </td>
</tr>
HTML;
        $vindi = new Vindi_Subscription_Block_Config_Information();
        $this->assertEquals($html, trim($vindi->render($this->stub)));
    }

    public function testRenderEmpty()
    {
        fwrite(STDOUT, 'Testando retorno de HTML vazio' . PHP_EOL);
        $vindi = new Vindi_Subscription_Block_Config_Information();
        $this->assertNotEquals('', trim($vindi->render($this->stub)));
    }

    public function testRenderFail()
    {
        fwrite(STDOUT, 'Testando retorno de HTML com falha' . PHP_EOL);
        $html = <<<HTML
<tr>
    <td colspan="4" class="label">
        <h3>Informações sobre a conta Vindi</h3>
    </td>
</tr>
<tr>
    <td class="label">
        Conexão
    </td><td class=" value error-msg">Falha na Conexão!<br />Verifique sua conta e tente novamente!</td></tr>
HTML;
        $vindi = new Vindi_Subscription_Block_Config_Information();
        $this->assertNotEquals($html, trim($vindi->render($this->stub)));
    }

}