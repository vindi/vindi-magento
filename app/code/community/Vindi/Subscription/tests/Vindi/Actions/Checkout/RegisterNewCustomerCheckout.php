<?php

namespace Vindi\Actions\Checkout;

use Vindi\Actions\Checkout\Steps\BillingAddress;
use Vindi\Actions\Checkout\Steps\NewCustomerPassword;
use Vindi\Actions\Checkout\Steps\PaymentMethod;
use Vindi\Actions\Checkout\Steps\PlaceOrder;
use Vindi\Actions\Checkout\Steps\SelectRegisterNewCustomerCheckout;
use Vindi\Actions\Checkout\Steps\ShippingAddress;
use Vindi\Actions\Checkout\Steps\ShippingMethod;
use Vindi\Extractors\Checkout\CartSummary;
use Vindi\Extractors\Checkout\OrderId;
use Vindi\Navigators\Checkout\CheckoutStart;
use Vindi\Themes\OnePageCheckout\AbstractThemeConfiguration;

class RegisterNewCustomerCheckout extends AbstractCheckout
{
    const ACTION = 'Checkout\RegisterNewCustomerCheckout';

    public function __construct(
        CheckoutStart             $navigator,
        AbstractThemeConfiguration    $theme,
        SelectRegisterNewCustomerCheckout           $registerNewCustomerCheckout,
        BillingAddress  $billingAddress,
        ShippingAddress         $shippingAddress,
        ShippingMethod          $shippingMethod,
        PaymentMethod           $paymentMethod,
        CartSummary             $cartSummary,
        PlaceOrder              $placeOrder,
        OrderId                 $orderIdExtractor,
        NewCustomerPassword     $newCustomerPassword
    )
    {
        $this->addStep($navigator);
        $this->addStep($registerNewCustomerCheckout);
        $this->addStep($newCustomerPassword);
        $this->addStep($billingAddress);
        $this->addStep($shippingAddress);
        $this->addStep($shippingMethod);
        $this->addStep($paymentMethod);
        $this->addStep($cartSummary);
        $this->addStep($placeOrder);
        $this->addStep($orderIdExtractor);

    }

}