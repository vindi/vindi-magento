<?php

namespace Vindi\Actions\Checkout\PaymentMethods;

interface PaymentMethodInterface
{
    public function getId();

    public function pay($requirePayment);
}