<?php
namespace Vindi\Actions\Checkout\Steps;

interface StepInterface
{
    public function execute();

    public function nextAction();
}