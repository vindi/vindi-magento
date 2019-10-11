<?php
require_once 'app/code/community/Vindi/Subscription/Helper/Connector.php';

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

    public function testResponseChecker()
    {
        $dummy_class = $this->make(
            'Vindi_Subscription_Helper_Connector',
            [
                'checkResponse' => RequestBody::SUBSCRIPTION_REQUEST
            ]
        );
        $this->assertEquals(
            $dummy_class->checkResponse(
                RequestBody::SUBSCRIPTION_REQUEST, 'subscriptions'
            ),
            Responses::SUBSCRIPTION_RESPONSE
        );
    }

    public function testGetErrorMessage()
    {
        $dummy_class = $this->make(
            'Vindi_Subscription_Helper_Connector',
            [
                'getErrorMessage' => RequestBody::INVALID_SUBSCRIPTION_REQUEST
            ]
        );
        $this->assertEquals(
            $dummy_class->checkResponse(
                RequestBody::INVALID_SUBSCRIPTION_REQUEST, 'subscriptions'
            ),
            true
        );
    }
}
