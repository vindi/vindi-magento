<?php 

class InstallmentSettingsCest
{
    public function _before(AcceptanceTester $I)
    {
        if (getenv('CONFIGURED') != true)
            SetupVindiModuleCest::setConnectionConfig($I);
    }

    // tests
    public function tryToTest(AcceptanceTester $I)
    {
        // $I->amOnPage('/vindi-product.html');
        // $I->click('Add to Cart');
        // $I->amOnPage('/checkout/onepage/');
        // $I->fillField('login[username]', 'comunidade@vindi.com.br');
        // $I->fillField('login[password]', 'password123');
        // $I->click('Login');

        // $I->click('Configuration' , \Codeception\Util\Locator::elementAt('.last.level1', -1));
        // $I->fillField('#vindi_subscription_general_api_key', getenv('VINDI_API_KEY'));
        // $I->selectOption('#vindi_subscription_general_sandbox_mode', 'Sandbox');
        // // $I->click('Save Config', \Codeception\Util\Locator::elementAt('.scalable.save', -1));
        // // $I->executeJS('configForm.submit()');
        // // $I->click('Save Config' , \Codeception\Util\Locator::elementAt('.scalable.save', -1));
        // $I->click('button[type="submit"]');
    }
}
