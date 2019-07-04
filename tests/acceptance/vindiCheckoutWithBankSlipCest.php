<?php 

class VindiCheckoutWithBankSlipCest
{
    public function _before(AcceptanceTester $I)
    {
        // Caso o módulo não tenha sido configurado
        if (! $I->isModuleConfigured())
            $I->setConnectionConfig($I);
    }

    public function buyAnProduct(AcceptanceTester $I)
    {
        $I->setDefaultBankSlip($I);
        $I->loginAsUser($I);
        $I->addProductToCart($I);
        $I->click('Proceed to Checkout');
        $I->skipCheckoutForm($I);
        $I->waitForElement('#dt_method_vindi_bankslip', 30);
        $I->selectOption('dl#checkout-payment-method-load', 'Boleto Bancário');
        $I->click('Continue', '#payment-buttons-container');
        $I->waitForElement('#review-buttons-container', 30);
        $I->click('Place Order');
        $I->waitForElement('.main-container.col1-layout', 30);
        $I->seeInCurrentUrl('/checkout/onepage/success');
        $I->see('Your order has been received.');
    }

    public function buyAnProductWithDiscount(AcceptanceTester $I)
    {
        $I->setDefaultBankSlip($I);
        $I->loginAsUser($I);
        $I->addProductToCart($I);
        $I->addDiscountCode($I);
        $I->click('Proceed to Checkout');
        $I->skipCheckoutForm($I);
        $I->waitForElement('#dt_method_vindi_bankslip', 30);
        $I->selectOption('dl#checkout-payment-method-load', 'Boleto Bancário');
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
