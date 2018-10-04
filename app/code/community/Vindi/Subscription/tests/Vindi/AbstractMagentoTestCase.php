<?php

namespace Vindi;

use Magium\AbstractTestCase;
use Magium\InvalidConfigurationException;
use Vindi\Actions\Checkout\PaymentMethods\PaymentMethodInterface;
use Vindi\Actions\Checkout\ShippingMethods\ShippingMethodInterface;
use Vindi\Themes\AbstractThemeConfiguration;
use Magium\Util\TestCase\RegistrationListener;

abstract class AbstractMagentoTestCase extends AbstractTestCase
{

    protected function setUp()
    {
        self::addBaseNamespace('Vindi');
        RegistrationListener::addCallback(new Registration(), 100);
        parent::setUp();
    }

    /**
     * @param $method
     * @return PaymentMethodInterface
     */

    public function setPaymentMethod($method)
    {

        // If we are passed just the class name we will prepend it with Vindi\Actions\Checkout\PaymentMethods
        if (strpos($method, '\\') === false) {
            $method = 'Checkout\PaymentMethods\\' . $method;
            $method = self::resolveClass($method, 'Actions');
        }
        $reflection = new \ReflectionClass($method);
        if ($reflection->implementsInterface('Vindi\Actions\Checkout\PaymentMethods\PaymentMethodInterface')) {

            $this->setTypePreference('Vindi\Actions\Checkout\PaymentMethods\PaymentMethodInterface', $method);
        } else {
            throw new InvalidConfigurationException('The payment method must implement Vindi\Actions\Checkout\PaymentMethods\PaymentMethodInterface');
        }
    }

    /**
     * @return PaymentMethodInterface
     */

    public function getPaymentMethod()
    {
        return $this->get('Vindi\Actions\Checkout\PaymentMethods\PaymentMethodInterface');
    }

    /**
     * @return \Vindi\Actions\Checkout\PaymentInformation
     */

    public function getPaymentInformation()
    {
        return $this->get('Vindi\Actions\Checkout\PaymentInformation');
    }

    /**
     * This is more of a helper for code completion
     *
     * @param null $theme
     * @return AbstractThemeConfiguration
     */

    public function getTheme($theme = null)
    {
        return parent::getTheme($theme);
    }

    /**
     * @param $method
     * @return \Vindi\Actions\Checkout\ShippingMethods\ShippingMethodInterface
     */

    public function setShippingMethod($method)
    {

        // When just the class name is passed we will prepend it with Vindi\Actions\Checkout\PaymentMethods
        if (strpos($method, '\\') === false) {
            $method = 'Vindi\Actions\Checkout\ShippingMethods\\' . $method;
        }
        $reflection = new \ReflectionClass($method);
        if ($reflection->implementsInterface('Vindi\Actions\Checkout\ShippingMethods\ShippingMethodInterface')) {
            $this->setTypePreference('Vindi\Actions\Checkout\ShippingMethods\ShippingMethodInterface', $method);
        } else {
            throw new InvalidConfigurationException('The payment method must implement Vindi\Actions\Checkout\ShippingMethods\ShippingMethodInterface');
        }
    }

    /**
     * @return ShippingMethodInterface
     */

    public function getShippingMethod()
    {
        return $this->get('Vindi\Actions\Checkout\ShippingMethods\ShippingMethodInterface');
    }

    public function switchThemeConfiguration($fullyQualifiedClassName)
    {
        $reflection = new \ReflectionClass($fullyQualifiedClassName);
        if ($reflection->isSubclassOf('Vindi\Themes\NavigableThemeInterface')) {
            // Not entirely sure of hardcoding the various interface types.  May make this configurable
            parent::switchThemeConfiguration($fullyQualifiedClassName);
            $this->setTypePreference('Vindi\Themes\AbstractThemeConfiguration',$fullyQualifiedClassName);
            $this->setTypePreference('Vindi\Themes\NavigableThemeInterface',$fullyQualifiedClassName);
            $this->setTypePreference('Magium\Themes\BaseThemeInterface',$fullyQualifiedClassName);

            $this->setTypePreference('Vindi\Themes\Customer\AbstractThemeConfiguration',$this->getTheme()->getCustomerThemeClass());
            $this->setTypePreference('Vindi\Themes\OnePageCheckout\AbstractThemeConfiguration',$this->getTheme()->getCheckoutThemeClass());
        } else {
            throw new InvalidConfigurationException('The theme configuration extend Vindi\Themes\NavigableThemeInterface');
        }

    }

}
