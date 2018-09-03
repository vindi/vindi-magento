<?php

namespace VindiTest\Translator;

use Magium\Magento\AbstractMagentoTestCase;
use Vindi\Util\CsvFileIterator;


class TranslatorTest extends AbstractMagentoTestCase
{

    private $csv = __DIR__ . '/../../../../../../../locale/pt_BR/Vindi_Subscription.csv';

    /**
     * @dataProvider provider
     */
    public function testDefaultLocaleSet($actual, $expected)
    {
        $translator = $this->getTranslator();
        $translator->addTranslationCsvFile($this->csv, 'pt_BR');
        $translator->setLocale('pt_BR');
        self::assertEquals($expected, $translator->translatePlaceholders('{{' . $actual . '}}'));
    }

    public function provider()
    {
        return new CsvFileIterator($this->csv);
    }
}