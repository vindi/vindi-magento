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
        $I->waitForElement('#dt_method_vindi_debitcard', AcceptanceTester::TIME_TO_WAIT);
        $I->selectOption('dl#checkout-payment-method-load', 'Cartão de Débito');
        $I->fillDebitCardInfo($I);
        $I->click('Continue', '#payment-buttons-container');
        $I->waitForElement('#review-buttons-container', AcceptanceTester::TIME_TO_WAIT);
        $I->click('Place Order');
        $I->waitForElement('.main-container.col1-layout', AcceptanceTester::TIME_TO_WAIT);
        $I->seeInCurrentUrl(AcceptanceTester::SUCCESS_CHECKOUT_URL);
        $I->click('Click here to approve');
        $I->switchToNextTab();
        $I->dontseeInCurrentUrl(AcceptanceTester::SUCCESS_CHECKOUT_URL);
    }

    public function buyProductWithDiscount(AcceptanceTester $I)
    {
        $I->setDefaultDebitCard($I);
        $I->loginAsUser($I);
        $I->addProductToCart($I);
        $I->addDiscountCode($I);
        $I->click('Proceed to Checkout');
        $I->skipCheckoutForm($I);
        $I->waitForElement('#dt_method_vindi_debitcard', AcceptanceTester::TIME_TO_WAIT);
        $I->selectOption('dl#checkout-payment-method-load', 'Cartão de Débito');
        $I->fillDebitCardInfo($I);
        $I->click('Continue', '#payment-buttons-container');
        $I->waitForElement('#review-buttons-container', AcceptanceTester::TIME_TO_WAIT);
        $I->see('Discount (desconto)');
        $I->click('Place Order');
        $I->waitForElement('.main-container.col1-layout', AcceptanceTester::TIME_TO_WAIT);
        $I->seeInCurrentUrl(AcceptanceTester::SUCCESS_CHECKOUT_URL);
        $I->click('Click here to approve');
        $I->switchToNextTab();
        $I->dontseeInCurrentUrl(AcceptanceTester::SUCCESS_CHECKOUT_URL);

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
        $I->waitForElement('#dt_method_vindi_debitcard', AcceptanceTester::TIME_TO_WAIT);
        $I->selectOption('dl#checkout-payment-method-load', 'Cartão de Débito');
        $I->fillDebitCardInfo($I);
        $I->click('Continue', '#payment-buttons-container');
        $I->waitForElement('#review-buttons-container', AcceptanceTester::TIME_TO_WAIT);
        $I->click('Place Order');
        $I->waitForElement('.main-container.col1-layout', AcceptanceTester::TIME_TO_WAIT);
        $I->seeInCurrentUrl(AcceptanceTester::SUCCESS_CHECKOUT_URL);
        $I->click('Click here to approve');
        $I->switchToNextTab();
        $I->dontseeInCurrentUrl(AcceptanceTester::SUCCESS_CHECKOUT_URL);
    }
}
