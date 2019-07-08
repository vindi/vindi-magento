<?php

trait Settings
{
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
}
