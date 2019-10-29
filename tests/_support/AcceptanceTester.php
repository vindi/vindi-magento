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
    use Settings;
    use Api;
    use PaymentMethod;
    use Shop;

    const SUCCESS_CHECKOUT_URL = '/checkout/onepage/success/';

    const TIME_TO_WAIT = 30;

    public function isModuleConfigured()
    {
        return getenv('CONFIGURED') == true;
    }
}
