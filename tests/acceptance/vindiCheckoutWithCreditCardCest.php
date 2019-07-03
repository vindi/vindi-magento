<?php 

class VindiCheckoutWithCreditCardCest
{
    public function _before(AcceptanceTester $I)
    {
        // Caso o módulo não tenha sido configurado
        if (! $I->isModuleConfigured())
            $I->setConnectionConfig($I);
    }

    public function buyAnProductInInstallment(AcceptanceTester $I)
    {
        $I->setDefaultCreditCard($I, true);
        $I->loginAsUser($I);
        $I->addProductToCart($I);
        $I->skipCheckoutForm($I);
        $I->waitForElement('#dt_method_vindi_creditcard', 30);
        $I->selectOption('dl#checkout-payment-method-load', 'Cartão de Crédito');
        $I->waitForElement('#vindi_cc_installments', 30);
        $I->selectOption('#vindi_cc_installments', '2');

        try
        {
            $I->fillField('#vindi_creditcard_cc_owner', 'Vindi Magento');
            $I->selectOption('#vindi_creditcard_cc_type', 'mastercard');
            $I->fillField('#vindi_creditcard_cc_number', '5555555555555557');
            $I->selectOption('select#vindi_creditcard_expiration.month', '12');
            $I->selectOption('select#vindi_creditcard_expiration_yr.year', strval(date('Y') + 5));
            $I->fillField('#vindi_creditcard_cc_cid', '123');
        } catch(Exception $e) { }

        $I->click('Continue', '#payment-buttons-container');
        $I->waitForElement('#review-buttons-container', 30);
        $I->click('Place Order');
        $I->waitForElement('.main-container.col1-layout', 30);
        $I->seeInCurrentUrl('/checkout/onepage/success');
        $I->see('Your order has been received.');
    }

    public function buyAnProductWithoutInstallment(AcceptanceTester $I)
    {
        $I->setDefaultCreditCard($I, false);
        $I->loginAsUser($I);
        $I->addProductToCart($I);
        $I->skipCheckoutForm($I);
        $I->waitForElement('#dt_method_vindi_creditcard', 30);
        $I->selectOption('dl#checkout-payment-method-load', 'Cartão de Crédito');
        $I->dontSeeElement('select.required-entry');

        try
        {
            $I->fillField('#vindi_creditcard_cc_owner', 'Vindi Magento');
            $I->selectOption('#vindi_creditcard_cc_type', 'mastercard');
            $I->fillField('#vindi_creditcard_cc_number', '5555555555555557');
            $I->selectOption('select#vindi_creditcard_expiration.month', '12');
            $I->selectOption('select#vindi_creditcard_expiration_yr.year', strval(date('Y') + 5));
            $I->fillField('#vindi_creditcard_cc_cid', '123');
        } catch(Exception $e) { }

        $I->click('Continue', '#payment-buttons-container');
        $I->waitForElement('#review-buttons-container', 30);
        $I->click('Place Order');
        $I->waitForElement('.main-container.col1-layout', 30);
        $I->seeInCurrentUrl('/checkout/onepage/success');
        $I->see('Your order has been received.');
    }
}
