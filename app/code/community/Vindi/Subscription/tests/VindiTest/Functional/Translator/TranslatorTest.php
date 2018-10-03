<?php

namespace VindiTest\Functional\Block\Config;

use Magium\Magento\AbstractMagentoTestCase;
use Magium\Magento\Util\CsvFileIterator;


/**
 * Class TranslatorTest
 * Classe para testes de escopo de tradução
 *
 * @package VindiTest\Translator
 */
class TranslatorTest extends AbstractMagentoTestCase
{

    private $csv = __DIR__ . '/../../../../../../../../locale/pt_BR/Vindi_Subscription.csv';

    /**
     * Teste do arquivo de tradução /locale/pt_BR/Vindi_Subscription.csv
     *
     * @param $actual
     * @param $expected
     *
     * @dataProvider provider
     */
    public function testDefaultLocaleSet($actual, $expected)
    {
        $translator = $this->getTranslator();
        $translator->addTranslationCsvFile($this->csv, 'pt_BR');
        $translator->setLocale('pt_BR');
        self::assertEquals($expected, $translator->translatePlaceholders('{{' . $actual . '}}'));
    }

    /**
     * Método que lê .csv e retorna argumentos
     */
    public function provider()
    {
        return new CsvFileIterator($this->csv);
    }
}
