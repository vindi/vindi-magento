<?php

class VindiCheckoutWithBankSlipCest
{
    public function _before(AcceptanceTester $I)
    {
        // Caso o módulo não tenha sido configurado
        if (! $I->isModuleConfigured())
            $I->setConnectionConfig($I);
    }

    public function buyProduct(AcceptanceTester $I)
    {
        $I->setDefaultBankSlip($I);
        $I->loginAsUser($I);
        $I->addProductToCart($I);
        $I->click('Proceed to Checkout');
        $I->skipCheckoutForm($I);
        $I->waitForElement('#dt_method_vindi_bankslip', AcceptanceTester::TIME_TO_WAIT);
        $I->selectOption('dl#checkout-payment-method-load', 'Boleto Bancário');
        $I->click('Continue', '#payment-buttons-container');
        $I->waitForElement('#review-buttons-container', AcceptanceTester::TIME_TO_WAIT);
        $I->click('Place Order');
        $I->waitForElement('.main-container.col1-layout', AcceptanceTester::TIME_TO_WAIT);
        $I->seeInCurrentUrl(AcceptanceTester::SUCCESS_CHECKOUT_URL);
        $I->see('Your order has been received.');
    }

    public function buyProductWithDiscount(AcceptanceTester $I)
    {
        $I->setDefaultBankSlip($I);
        $I->loginAsUser($I);
        $I->addProductToCart($I);
        $I->addDiscountCode($I);
        $I->click('Proceed to Checkout');
        $I->skipCheckoutForm($I);
        $I->waitForElement('#dt_method_vindi_bankslip', AcceptanceTester::TIME_TO_WAIT);
        $I->selectOption('dl#checkout-payment-method-load', 'Boleto Bancário');
        $I->click('Continue', '#payment-buttons-container');
        $I->waitForElement('#review-buttons-container', AcceptanceTester::TIME_TO_WAIT);
        $I->see('Discount (desconto)');
        $I->click('Place Order');
        $I->waitForElement('.main-container.col1-layout', AcceptanceTester::TIME_TO_WAIT);
        $I->seeInCurrentUrl(AcceptanceTester::SUCCESS_CHECKOUT_URL);
        $bill = $I->getLastVindiBill();
        if ($bill['amount'] != '14.8')
            throw new \RuntimeException;
    }

    public function buySubscription(AcceptanceTester $I)
    {
        $I->setDefaultBankSlip($I);
        $I->loginAsUser($I);
        $I->addSubscriptionToCart($I);
        $I->click('Proceed to Checkout');
        $I->skipCheckoutForm($I);
        $I->waitForElement('#dt_method_vindi_bankslip', AcceptanceTester::TIME_TO_WAIT);
        $I->selectOption('dl#checkout-payment-method-load', 'Boleto Bancário');
        $I->click('Continue', '#payment-buttons-container');
        $I->waitForElement('#review-buttons-container', AcceptanceTester::TIME_TO_WAIT);
        $I->click('Place Order');
        $I->waitForElement('.main-container.col1-layout', AcceptanceTester::TIME_TO_WAIT);
        $I->seeInCurrentUrl(AcceptanceTester::SUCCESS_CHECKOUT_URL);
        $I->see('Your order has been received.');
    }
}
