<?php

require 'Mage/Mage_Core_Helper_Abstract.php';
require 'Mage/Mage_Payment_Model_Method_Abstract.php';
require 'Mage/Mage.php';
require 'app/code/community/Vindi/Subscription/Trait/LogMessenger.php';

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
class UnitTester extends \Codeception\Actor
{
    use _generated\UnitTesterActions;
}
