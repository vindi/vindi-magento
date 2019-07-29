<?php 

class VindiCheckoutWithCreditCardCest
{
    public function _before(AcceptanceTester $I)
    {
        // Caso o módulo não tenha sido configurado
        if (! $I->isModuleConfigured())
            $I->setConnectionConfig($I);
    }

    public function buyAProductInInstallment(AcceptanceTester $I)
    {
        $I->setDefaultCreditCard($I, true);
        $I->loginAsUser($I);
        $I->addProductToCart($I);
        $I->click('Proceed to Checkout');
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

    public function buyAProductWithoutInstallment(AcceptanceTester $I)
    {
        $I->setDefaultCreditCard($I, false);
        $I->loginAsUser($I);
        $I->addProductToCart($I);
        $I->click('Proceed to Checkout');
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
    public function buyAProductWithInstallmentsOne(AcceptanceTester $I)
    {
        $I->setDefaultCreditCard($I, false, 1);
        $I->loginAsUser($I);
        $I->addProductToCart($I);
        $I->click('Proceed to Checkout');
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


    public function buyAProductWithDiscount(AcceptanceTester $I)
    {
        $I->setDefaultCreditCard($I, false);
        $I->loginAsUser($I);
        $I->addProductToCart($I);
        $I->addDiscountCode($I);
        $I->click('Proceed to Checkout');
        $I->skipCheckoutForm($I);
        $I->waitForElement('#dt_method_vindi_creditcard', 30);
        $I->selectOption('dl#checkout-payment-method-load', 'Cartão de Crédito');

        try
        {
            $I->fillCreditCardInfo($I);
        } catch(Exception $e) { }

        $I->click('Continue', '#payment-buttons-container');
        $I->waitForElement('#review-buttons-container', 30);
        $I->see('Discount (desconto)');
        $I->click('Place Order');
        $I->waitForElement('.main-container.col1-layout', 30);
        $I->seeInCurrentUrl('/checkout/onepage/success');
        $bill = $I->getLastVindiBill();
        if ($bill['amount'] != '14.8')
            throw new \RuntimeException;
    }
}
