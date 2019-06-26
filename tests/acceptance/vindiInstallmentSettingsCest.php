<?php 

class VindiInstallmentSettingsCest
{
    public function _before(AcceptanceTester $I)
    {
        // Caso o módulo não tenha sido configurado
        if (! $I->isModuleConfigured())
            VindiModuleConfigCest::setConnectionConfig($I);
    }

    public function enableCreditCardWithoutInstallments(AcceptanceTester $I)
    {
        $I->goToCreditCardSettings($I);
        $I->selectOption('#payment_vindi_creditcard_active', 'Yes');
        $I->selectOption('#payment_vindi_creditcard_enable_installments', 'No');
        $I->click('Save Config');
    }

    public function enableCreditCardWithInstallments(AcceptanceTester $I)
    {
        $I->goToCreditCardSettings($I);
        $I->selectOption('#payment_vindi_creditcard_active', 'Yes');
        $I->selectOption('#payment_vindi_creditcard_enable_installments', 'Yes');
        $I->selectOption('#payment_vindi_creditcard_max_installments_number', '12x');
        $I->click('Save Config');
    }

}
