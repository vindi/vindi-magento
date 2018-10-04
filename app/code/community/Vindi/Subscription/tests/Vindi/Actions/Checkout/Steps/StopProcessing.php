<?php

namespace Vindi\Actions\Checkout\Steps;



class StopProcessing implements StepInterface
{
    const ACTION = 'Checkout\Steps\StopProcessing';

    public function execute()
    {
        return false;
    }

    public function nextAction()
    {
        return false;
    }
}