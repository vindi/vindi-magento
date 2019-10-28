<?php

class VindiCheckoutWithDebitCardCest
{
    public function _before(AcceptanceTester $I)
    {
        // Caso o módulo não tenha sido configurado
        if (! $I->isModuleConfigured())
            $I->setConnectionConfig($I);
    }

    public function buyProduct(AcceptanceTester $I)
    {
        $I->setDefaultDebitCard($I);
        $I->loginAsUser($I);
        $I->addProductToCart($I);
        $I->click('Proceed to Checkout');
        $I->skipCheckoutForm($I);
        $I->waitForElement('#dt_method_vindi_debitcard', 30);
        $I->selectOption('dl#checkout-payment-method-load', 'Cartão de Débito');
        $I->fillDebitCardInfo($I);
        $I->click('Continue', '#payment-buttons-container');
        $I->waitForElement('#review-buttons-container', 30);
        $I->click('Place Order');
        $I->waitForElement('.main-container.col1-layout', 30);
        $I->seeInCurrentUrl('/checkout/onepage/success');
        $I->click('Click here to approve');
        $I->dontSeeInCurrentUrl('/checkout/onepage/success');
    }

    public function buyProductWithDiscount(AcceptanceTester $I)
    {
        $I->setDefaultDebitCard($I);
        $I->loginAsUser($I);
        $I->addProductToCart($I);
        $I->addDiscountCode($I);
        $I->click('Proceed to Checkout');
        $I->skipCheckoutForm($I);
        $I->waitForElement('#dt_method_vindi_debitcard', 30);
        $I->selectOption('dl#checkout-payment-method-load', 'Cartão de Débito');
        $I->fillDebitCardInfo($I);
        $I->click('Continue', '#payment-buttons-container');
        $I->waitForElement('#review-buttons-container', 30);
        $I->see('Discount (desconto)');
        $I->click('Place Order');
        $I->waitForElement('.main-container.col1-layout', 30);
        $I->seeInCurrentUrl('/checkout/onepage/success');
        $I->click('Click here to approve');
        $I->dontSeeInCurrentUrl('/checkout/onepage/success');

        $bill = $I->getLastVindiBill();
        if ($bill['amount'] != '14.8')
            throw new \RuntimeException;
    }

    public function buySubscription(AcceptanceTester $I)
    {
        $I->setDefaultDebitCard($I);
        $I->loginAsUser($I);
        $I->addSubscriptionToCart($I);
        $I->click('Proceed to Checkout');
        $I->skipCheckoutForm($I);
        $I->waitForElement('#dt_method_vindi_debitcard', 30);
        $I->selectOption('dl#checkout-payment-method-load', 'Cartão de Débito');
        $I->fillDebitCardInfo($I);
        $I->click('Continue', '#payment-buttons-container');
        $I->waitForElement('#review-buttons-container', 30);
        $I->click('Place Order');
        $I->waitForElement('.main-container.col1-layout', 30);
        $I->seeInCurrentUrl('/checkout/onepage/success');
        $I->click('Click here to approve');
        $I->dontSeeInCurrentUrl('/checkout/onepage/success');
    }
}
