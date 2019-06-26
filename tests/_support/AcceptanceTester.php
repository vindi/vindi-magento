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

    public function goToAdminPanel($I)
    {
        $I->amOnPage('/admin');
        $I->fillField('login[username]', 'admin');
        $I->fillField('login[password]', 'admin123');
        $I->click('Login');
    }

    public function goToVindiSettings($I)
    {
        $this->goToAdminPanel($I);
        $I->click('System');
        $I->click('Configuration');
        $I->click('Configuração');
    }
}
