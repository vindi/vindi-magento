<?php
require_once 'app/code/community/Vindi/Subscription/Helper/Api.php';

class ApiTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    /**
     * @var \Vindi_Subscription_Helper_API
     */
    protected $api;

    /**
     * @var \Responses
     */
    protected $response;

    protected function _before()
    {
        $this->api = new Vindi_Subscription_Helper_API();
        $this->response = new Responses();
    }

    public function testGetPaymentMethodBankSlip()
    {
      $dummy_class = $this->make(
          'Vindi_Subscription_Helper_API',
          [
              'request' => $this->response::ACTIVE_BANKSLIP
          ]
      );
      $success = array(
          'credit_card' => [],
          'debit_card' => [],
          'bank_slip'   => true
      );

      $this->assertEquals($dummy_class->getPaymentMethods(), $success);
  }

    public function testGetPaymentMethodOnlineBankSlip()
    {
    $dummy_class = $this->make(
        'Vindi_Subscription_Helper_API',
        [
            'request' => $this->response::ACTIVE_ONLINEBANKSLIP
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
                'request' => $this->response::ACTIVE_CREDITCARD
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
                'request' => $this->response::ACTIVE_DEBITCARD
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
                'request' => $this->response::DEFAULT
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
                'request' => $this->response::DEFAULTNULL
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
