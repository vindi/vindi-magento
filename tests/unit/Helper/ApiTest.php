<?php
require_once 'app/code/community/Vindi/Subscription/Helper/Connector.php';
require_once 'app/code/community/Vindi/Subscription/Helper/Api.php';

class ApiTest extends \Codeception\Test\Unit
{
    /**
     * @var \Responses
     */
    protected $response;

    protected function _before()
    {
        $this->response = new Responses();
    }

    public function testGetPaymentMethodBankSlip()
    {
        $dummy_class = $this->make(
            'Vindi_Subscription_Helper_API',
            [
                'get' => $this->response::ACTIVE_BANK_SLIP
            ]
            );
        $success = array(
          'credit_card' => [],
          'debit_card' => [],
          'bank_slip'   => true
      );

        $this->assertEquals($dummy_class->getPaymentMethods(), $success);
    }

    public function testGetPaymentMethodCreditCard()
    {
        $dummy_class = $this->make(
            'Vindi_Subscription_Helper_API',
            [
                'get' => $this->response::ACTIVE_CREDIT_CARD
            ]
        );
        $success = array(
            'credit_card' => array(
                array(
                    'name' => 'Mastercard',
                    'code' => 'mastercard'
                ),
                array(
                    'name' => 'Visa',
                    'code' => 'visa'
                ),
                array(
                    'name' => 'American Express',
                    'code' => 'american_express'
                )
            ),
            'debit_card' => [],
            'bank_slip'   => false
        );

        $this->assertEquals($dummy_class->getPaymentMethods(), $success);
    }

    public function testGetPaymentMethodDebitCard()
    {
        $dummy_class = $this->make(
            'Vindi_Subscription_Helper_API',
            [
                'get' => $this->response::ACTIVE_DEBIT_CARD
            ]
        );
        $success = array(
            'credit_card' => [],
            'debit_card' => array(
                array(
                    'name' => 'Maestro',
                    'code' => 'maestro'
                ),
                array(
                    'name' => 'Visa Electron',
                    'code' => 'visa_electron'
                ),
                array(
                    'name' => 'Elo',
                    'code' => 'elo_debit'
                )
            ),
            'bank_slip'   => false
        );

        $this->assertEquals($dummy_class->getPaymentMethods(), $success);
    }

    public function testGetDefaultPaymentMethods()
    {
        $dummy_class = $this->make(
            'Vindi_Subscription_Helper_API',
            [
                'get' => $this->response::GENERAL_PAYMENT_METHODS
            ]

        );
        $success = array(
            'credit_card' => array(
                array(
                    'name' => 'Mastercard',
                    'code' => 'mastercard'
                ),
                array(
                    'name' => 'Visa',
                    'code' => 'visa'
                ),
                array(
                    'name' => 'American Express',
                    'code' => 'american_express'
                )
            ),
            'debit_card' => array(
                array(
                    'name' => 'Maestro',
                    'code' => 'maestro'
                ),
                array(
                    'name' => 'Visa Electron',
                    'code' => 'visa_electron'
                ),
                array(
                    'name' => 'Elo',
                    'code' => 'elo_debit'
                )
            ),
            'bank_slip'   => true
        );

        $this->assertEquals($dummy_class->getPaymentMethods(), $success);
    }

    public function testGetDefaultPaymentMethodsNull()
    {
        $dummy_class = $this->make(
            'Vindi_Subscription_Helper_API',
            [
                'get' => $this->response::EMPTY_PAYMENT_METHODS
            ]

        );
        $success = array(
            'credit_card' => [],
            'debit_card'  => [],
            'bank_slip'   => false
        );

        $this->assertEquals($dummy_class->getPaymentMethods(), $success);
    }
}
