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

        try
        {
            $I->fillCreditCardInfo($I, 2);
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
            $I->fillCreditCardInfo($I);
        } catch(Exception $e) { }

        $I->click('Continue', '#payment-buttons-container');
        $I->waitForElement('#review-buttons-container', 30);
        $I->click('Place Order');
        $I->waitForElement('.main-container.col1-layout', 30);
        $I->seeInCurrentUrl('/checkout/onepage/success');
        $I->see('Your order has been received.');
    }
}
