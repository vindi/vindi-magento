<?php

namespace Vindi\Actions\Checkout\ShippingMethods;

interface ShippingMethodInterface
{

    public function choose($required);

}