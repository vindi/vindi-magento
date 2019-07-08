<?php

trait Shop
{
    public function addProductToCart($I)
    {
        $I->amOnPage('/vindi-product.html');
        $I->click('Add to Cart');
    }

    public function addDiscountCode($I)
    {
        $I->fillField('#coupon_code', 'desconto');
        $I->click('Apply');
    }

    public function loginAsUser($I)
    {
        $I->wait(1);
        $I->amOnPage('/customer/account/login');
        $I->fillField('login[username]', 'comunidade@vindi.com.br');
        $I->fillField('login[password]', 'password123');
        $I->click('Login');
    }

    public function skipCheckoutForm($I)
    {
        $I->click('#billing:use_for_shipping_yes');
        $I->click('Continue', '#billing-buttons-container');
        $I->wait(1);
        $I->click('Continue', '#shipping-method-buttons-container');
        $I->wait(1);
    }

    public function fillCreditCardInfo($I, $installments = false)
    {
        $I->fillField('#vindi_creditcard_cc_owner', 'Vindi Magento');
        $I->selectOption('#vindi_creditcard_cc_type', 'mastercard');
        $I->fillField('#vindi_creditcard_cc_number', '5555555555555557');
        $I->selectOption('select#vindi_creditcard_expiration.month', '12');
        $I->selectOption('select#vindi_creditcard_expiration_yr.year', strval(date('Y') + 5));
        $I->fillField('#vindi_creditcard_cc_cid', '123');

        if ($installments)
            $I->selectOption('#vindi_cc_installments', $installments);
    }
}
