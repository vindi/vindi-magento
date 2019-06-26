<?php 

class SetupVindiModuleCest
{
    public static function setConnectionConfig(AcceptanceTester $I)
    {
        // Caso o módulo já tenha sido configurado
        if (getenv('CONFIGURED'))
            return;

        $I->goToVindiSettings($I);
        $I->fillField('#vindi_subscription_general_api_key', getenv('VINDI_API_KEY'));
        $I->selectOption('#vindi_subscription_general_sandbox_mode', 'Sandbox');
        $I->click('Save Config');
        putenv("CONFIGURED=true");
    }

    public function setDefaultShippingMethod(AcceptanceTester $I)
    {
        $I->goToVindiSettings($I);
        $I->selectOption('#vindi_subscription_general_default_shipping_method', 'Flat Rate');
    }

    public function sendCustomerVat(AcceptanceTester $I, Bool $enabled = false)
    {
        $I->goToVindiSettings($I);
        $I->selectOption('#vindi_subscription_general_send_nfe_information', 'Yes');
    }

    public function showBankSlipOnOrder(AcceptanceTester $I, Bool $enabled = false)
    {
        $I->goToVindiSettings($I);
        $I->selectOption('#vindi_subscription_general_bankslip_link_in_order_comment', 'Yes');
    }

    public function setVerifyTransactionStatus(AcceptanceTester $I, Bool $enabled = false)
    {
        $I->goToVindiSettings($I);
        $I->selectOption('#vindi_subscription_general_verify_method', 'Yes');
    }
}
