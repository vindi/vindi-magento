<?php


/**
 * Inherited Methods
 * @method void wantToTest($text)
 * @method void wantTo($text)
 * @method void execute($callable)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($achieveValue)
 * @method void comment($description)
 * @method void pause()
 *
 * @SuppressWarnings(PHPMD)
*/
class AcceptanceTester extends \Codeception\Actor
{
    use _generated\AcceptanceTesterActions;

    public function isModuleConfigured()
    {
        return getenv('CONFIGURED') == true;
    }

    public function goToAdminPanel($I)
    {
        $I->amOnPage('/admin');

        try {
            $I->fillField('login[username]', 'admin');
            $I->fillField('login[password]', 'password123');
            $I->click('Login');
        } catch (Exception $e) { }
    }

    public function goToVindiSettings($I)
    {
        $I->click('System');
        $I->click('Configuration');

        try {
            $I->seeElement('#vindi_subscription_general_api_key');
        } catch (Exception $e) {
            $I->click('#vindi_subscription_general-head');
        }
    }

    public function setConnectionConfig($I)
    {
        $I->goToAdminPanel($I);
        $I->goToVindiSettings($I);
        $I->fillField('#vindi_subscription_general_api_key', getenv('VINDI_API_KEY'));
        $I->selectOption('#vindi_subscription_general_sandbox_mode', 'Sandbox');
        $I->click('Save Config');
        putenv("CONFIGURED=true");
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

    public function addProductToCart($I)
    {
        $I->amOnPage('/vindi-product.html');
        $I->click('Add to Cart');
        $I->click('Proceed to Checkout');
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
        $I->click('Continue', '#billing-buttons-container');
        $I->wait(1);
        $I->click('Continue', '#shipping-method-buttons-container');
        $I->wait(1);
    }
}
