<?php

namespace Vindi;

use Magium\AbstractTestCase;
use Magium\Util\TestCase\RegistrationCallbackInterface;

class Registration implements RegistrationCallbackInterface
{

    public function register(AbstractTestCase $testCase)
    {
        $testCase->setTypePreference(
            'Vindi\Actions\Checkout\PaymentMethods\PaymentMethodInterface',
            'Vindi\Actions\Checkout\PaymentMethods\NoPaymentMethod'
        );

        $testCase->setTypePreference(
            'Vindi\Actions\Checkout\ShippingMethods\ShippingMethodInterface',
            'Vindi\Actions\Checkout\ShippingMethods\FirstAvailable'
        );

        $testCase->setTypePreference(
            'Magium\Themes\ThemeConfigurationInterface',
            'Vindi\Themes\AbstractThemeConfiguration'
        );

        $testCase->switchThemeConfiguration('Vindi\Themes\Magento19\ThemeConfiguration');
    }

}