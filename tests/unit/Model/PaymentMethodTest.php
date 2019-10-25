<?php
require_once 'app/code/community/Vindi/Subscription/Trait/PaymentProcessor.php';
require_once 'app/code/community/Vindi/Subscription/Trait/ExceptionMessenger.php';
require_once 'app/code/community/Vindi/Subscription/Model/PaymentMethod.php';
require_once 'app/code/community/Vindi/Subscription/Model/DebitCard.php';

class PaymentMethodTest extends \Codeception\Test\Unit
{
    use Api;

    /**
     * @var \Payment
     */
    protected $payment;

    public function _before()
    {
        $this->payment = new Mage_Payment_Model_Method_Abstract();
    }

    public function testCreateValidPaymentProfileDebitCard()
    {
        $dummy_class = $this->make(
            'Vindi_Subscription_Model_PaymentMethod',
            [
                'api' => $this
            ],
            [
                'getInfoInstance' => $this->payment
            ]
        );

        $dummy_class->vindiMethodCode = 'debit_card';
        $valid_customer_id = 62;
        $json_response = $dummy_class->createPaymentProfile($valid_customer_id);
        
        $this->assertArrayNotHasKey('errors', $json_response);
        $this->assertArrayHasKey('payment_profile', $json_response);
    }

    public function testCreateInvalidPaymentProfileDebitCard()
    {
        $dummy_class = $this->make(
            'Vindi_Subscription_Model_PaymentMethod',
            [
                'api' => $this
            ],
            [
                'getInfoInstance' => $this->payment
            ]
        );

        $dummy_class->vindiMethodCode = 'debit_card';
        $invalid_customer_id = 63;
        $json_response = $dummy_class->createPaymentProfile($invalid_customer_id);

        $this->assertArrayHasKey('errors', $json_response);
    }
}
