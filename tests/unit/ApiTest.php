<?php
require_once 'app/code/community/Vindi/Subscription/Helper/Api.php';
require_once 'app/code/community/Vindi/Subscription/Helper/Connector.php';

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

    /**
     * @var \Connector
     */
    protected $connector;

    protected function _before()
    {
        $this->api = new Vindi_Subscription_Helper_API();
        $this->response = new Responses();
    }

    public function testGetPaymentMethodBankSlip()
    {
      $dummy_connector_class = $this->make(
          'Vindi_Subscription_Helper_Connector',
          [
              'get' => $this->response::ACTIVE_BANK_SLIP
          ]
      );
      $dummy_class = $this->make(
          'Vindi_Subscription_Helper_API',
          [
              'connector' => $dummy_connector_class
          ],

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
    $dummy_connector_class = $this->make(
        'Vindi_Subscription_Helper_Connector',
        [
            'get' => $this->response::ACTIVE_ONLINE_BANK_SLIP
        ]
    );
    $dummy_class = $this->make(
        'Vindi_Subscription_Helper_API',
        [
            'connector' => $dummy_connector_class
        ],

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
        $dummy_connector_class = $this->make(
            'Vindi_Subscription_Helper_Connector',
            [
                'get' => $this->response::ACTIVE_CREDIT_CARD
            ]
        );
        $dummy_class = $this->make(
            'Vindi_Subscription_Helper_API',
            [
                'connector' => $dummy_connector_class
            ],

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
        $dummy_connector_class = $this->make(
            'Vindi_Subscription_Helper_Connector',
            [
                'get' => $this->response::ACTIVE_DEBIT_CARD
            ]
        );
        $dummy_class = $this->make(
            'Vindi_Subscription_Helper_API',
            [
                'connector' => $dummy_connector_class
            ],

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
        $dummy_connector_class = $this->make(
            'Vindi_Subscription_Helper_Connector',
            [
                'get' => $this->response::GENERAL_PAYMENT_METHODS
            ]
        );
        $dummy_class = $this->make(
            'Vindi_Subscription_Helper_API',
            [
                'connector' => $dummy_connector_class
            ],

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
        $dummy_connector_class = $this->make(
            'Vindi_Subscription_Helper_Connector',
            [
                'get' => $this->response::EMPTY_PAYMENT_METHODS
            ]
        );
        $dummy_class = $this->make(
            'Vindi_Subscription_Helper_API',
            [
                'connector' => $dummy_connector_class
            ],

        );
        $success = array(
            'credit_card' => [],
            'debit_card'  => [],
            'bank_slip'   => false
        );

        $this->assertEquals($dummy_class->getPaymentMethods(), $success);
    }

}
