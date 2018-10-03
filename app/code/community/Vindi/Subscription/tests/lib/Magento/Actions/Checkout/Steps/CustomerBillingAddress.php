<?php

namespace Magium\Magento\Actions\Checkout\Steps;


class CustomerBillingAddress extends BillingAddress
{
    const ACTION = 'Checkout\Steps\CustomerBillingAddress';

    public function execute()
    {
        $this->bypassElement($this->theme->getBillingEmailAddressXpath());
        return parent::execute();
    }

}