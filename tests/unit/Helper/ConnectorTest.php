<?php
require_once 'app/code/community/Vindi/Subscription/Helper/Connector.php';

class ConnectorTest extends \Codeception\Test\Unit
{
    public function testEncryptyOnCreditCards()
    {
        $connector = new Vindi_Subscription_Helper_Connector();

        $this->assertEquals(
            $connector->encrypt(
                RequestBody::UNENCRYPTED_PAYMENT_PROFILE_REQUEST, 'payment_profiles'
            ),
            Logger::ENCRYPTED_PAYMENT_PROFILE_CARD
        );
    }

    public function testResponseCheckerOnValidBody()
    {
        $connector = new Vindi_Subscription_Helper_Connector();

        $this->assertTrue(
            $connector->checkResponse(
                [
                    Responses::SAMPLE_SUBSCRIPTION_RESPONSE
                ],
                'subscriptions'
                )
            );
    }

    public function testResponseCheckerOnInvalidBody()
    {
        $connector = new Vindi_Subscription_Helper_Connector();

        $this->assertFalse(
            $connector->checkResponse(
                [
                    "errors" => [
                        [
                            'id' => 'invalid_parameter',
                            'parameter' => 'plan',
                            'message' => 'inválido'
                        ]
                    ]
                ],
                'subscriptions'
                )
            );
    }

    public function testGetErrorMessage()
    {
        $connector = new Vindi_Subscription_Helper_Connector();

        $this->assertEquals(
            $connector->getErrorMessage(
                [
                    'id' => 'invalid_parameter',
                    'parameter' => 'plan',
                    'message' => 'inválido'
                ],
                'subscriptions'
            ),
            Responses::INVALID_SUBSCRIPTION_RESPONSE
        );
    }
}
