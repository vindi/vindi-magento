<?php

namespace Vindi\Actions\Checkout;

use Vindi\Actions\Checkout\Steps\CustomerBillingAddress;
use Vindi\Actions\Checkout\Steps\LogInCustomer;
use Vindi\Actions\Checkout\Steps\PaymentMethod;
use Vindi\Actions\Checkout\Steps\PlaceOrder;
use Vindi\Actions\Checkout\Steps\ReviewOrder;
use Vindi\Actions\Checkout\Steps\SelectCustomerCheckout;
use Vindi\Actions\Checkout\Steps\ShippingAddress;
use Vindi\Actions\Checkout\Steps\ShippingMethod;
use Vindi\Extractors\Checkout\CartSummary;
use Vindi\Extractors\Checkout\OrderId;
use Vindi\Navigators\Checkout\Checkout;
use Vindi\Navigators\Checkout\CheckoutStart;
use Vindi\Themes\OnePageCheckout\AbstractThemeConfiguration;

class CustomerCheckout extends AbstractCheckout
{
    const ACTION = 'Checkout\CustomerCheckout';

    public function __construct(
        CheckoutStart             $navigator,
        AbstractThemeConfiguration    $theme,
        LogInCustomer           $logInCustomer,
        CustomerBillingAddress  $billingAddress,
        ShippingAddress         $shippingAddress,
        ShippingMethod          $shippingMethod,
        PaymentMethod           $paymentMethod,
        CartSummary             $cartSummary,
        PlaceOrder              $placeOrder,
        OrderId                 $orderIdExtractor
    )
    {
        $this->addStep($navigator);
        $this->addStep($logInCustomer);
        $this->addStep($billingAddress);
        $this->addStep($shippingAddress);
        $this->addStep($shippingMethod);
        $this->addStep($paymentMethod);
        $this->addStep($cartSummary);
        $this->addStep($placeOrder);
        $this->addStep($orderIdExtractor);

    }

}