<?php

trait PaymentMethod
{
    public function setDefaultBankSlip($I)
    {
        $I->goToAdminPanel($I);
        $I->goToBankSlipSettings($I);
        $I->selectOption('#payment_vindi_bankslip_active', 'Yes');
        $I->click('Save Config');
    }

    public function goToBankSlipSettings($I)
    {
        $I->click('System');
        $I->click('Configuration');
        $I->click('Payment Methods');

        try {
            $I->seeElement('#payment_vindi_bankslip_active');
        } catch (Exception $e) {
            $I->click('#payment_vindi_bankslip-head');
        }
    }

    public function setDefaultDebitCard($I)
    {
        $I->goToAdminPanel($I);
        $I->goToDebitCardSettings($I);
        $I->selectOption('#payment_vindi_debitcard_active', 'Yes');
        $I->click('Save Config');
    }

    public function goToDebitCardSettings($I)
    {
        $I->click('System');
        $I->click('Configuration');
        $I->click('Payment Methods');

        try {
            $I->seeElement('#payment_vindi_debitcard_active');
        } catch (Exception $e) {
            $I->click('#payment_vindi_debitcard-head');
        }
    }

    public function setDefaultCreditCard($I, $withInstallments = true, $maxInstallment = 12)
    {
        $I->goToAdminPanel($I);
        $I->goToCreditCardSettings($I);
        $I->selectOption('#payment_vindi_creditcard_active', 'Yes');
        $I->selectOption(
            '#payment_vindi_creditcard_enable_installments', $withInstallments ? 'Yes' : 'No'
        );
        $I->selectOption('#payment_vindi_creditcard_max_installments_number', "{$maxInstallment}x");
        $I->click('Save Config');
    }

    public function goToCreditCardSettings($I)
    {
        $I->click('System');
        $I->click('Configuration');
        $I->click('Payment Methods');

        try {
            $I->seeElement('#payment_vindi_creditcard_active');
        } catch (Exception $e) {
            $I->click('#payment_vindi_creditcard-head');
        }
    }
}
