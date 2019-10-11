<?php
require_once 'app/code/community/Vindi/Subscription/Helper/Connector.php';
require_once 'app/code/community/Vindi/Subscription/Helper/Api.php';

class ConnectorTest extends \Codeception\Test\Unit
{
    public function testEncryption()
    {
        $dummy_class = $this->make(
            'Vindi_Subscription_Helper_Connector',
            [
                'encrypt' => Logger::ENCRYPTED_PAYMENT_PROFILE_CARD
            ]
        );
        $this->assertEquals(
            $dummy_class->encrypt(
                RequestBody::UNENCRYPTED_PAYMENT_PROFILE_REQUEST, 'payment_profiles'
            ),
            Logger::ENCRYPTED_PAYMENT_PROFILE_CARD
        );
    }

    // public function testResponseChecker()
    // {
    //     $dummy_class = $this->make(
    //         'Vindi_Subscription_Helper_Connector',
    //         [
    //             'checkResponse' => Responses::GENERAL_PAYMENT_METHODS
    //         ]
    //     );
    //     $this->assertEquals(
    //         $dummy_class->checkResponse(

    //         )
    //     )
    // }
}