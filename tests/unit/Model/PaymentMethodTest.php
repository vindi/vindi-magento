<?php
require_once 'app/code/community/Vindi/Subscription/Trait/PaymentProcessor.php';
require_once 'app/code/community/Vindi/Subscription/Trait/ExceptionMessenger.php';
require_once 'app/code/community/Vindi/Subscription/Model/PaymentMethod.php';
require_once 'app/code/community/Vindi/Subscription/Model/DebitCard.php';

class PaymentMethodTest extends \Codeception\Test\Unit
{
    // use Vindi_Subscription_Trait_PaymentProcessor;
    /**
     * @var \Payment
     */
    protected $payment;

    public function _before()
    {
        $this->payment = new Mage_Payment_Model_Method_Abstract();
    }

    public function testCreatePaymentProfileDebitCard()
    {
        $dummy = $this->make(
            'Vindi_Subscription_Model_DebitCard'
        );
        $dummy_class = $this->make(
            'Vindi_Subscription_Model_PaymentMethod',
            [
                'api' => true
            ],
            [
                'getInfoInstance' => $this->payment
            ],
            [
                'getPaymentMethodCode' => $dummy->vindiMethodCode
            ]
        );

        $this->assertTrue(
            $dummy_class->createPaymentProfile(123456)
        );
    }
}