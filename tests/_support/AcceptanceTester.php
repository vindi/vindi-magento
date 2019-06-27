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
        $I->fillField('login[username]', 'admin');
        $I->fillField('login[password]', 'password123');
        $I->click('Login');
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
}
