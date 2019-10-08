<?php
require_once 'app/code/community/Vindi/Subscription/Trait/LogMessenger.php';
require_once 'app/code/community/Vindi/Subscription/Helper/Bill.php';
require_once 'app/code/community/Vindi/Subscription/Helper/Order.php';

class ValidatorTest extends \Codeception\Test\Unit
{
    /**
     * @var \Webhook
     */
    protected $webhooks;

    /**
     * @var \Vindi_Subscription_Helper_Validator
     */
    protected $validator;

    protected function _before()
    {
        $this->validator = new Vindi_Subscription_Helper_Validator();
        $this->webhooks = new Webhooks();
    }

    public function testSuccessValidateChargeWebhook()
    {
        $dummy_order_class = $this->makeEmpty(
            'Vindi_Subscription_Helper_Order',
            [
                'getOrderFromVindi' => json_decode(
                    $this->webhooks::BILL_CREATED_WEBHOOK, true
                )['event']['data']['bill']
            ],
            [
                'getOrder' => ['order' => ['id' => 1]]
            ],
            [
                'getOrderFromMagento' => ['order' => ['id' => 1]]
            ]
        );

        $dummy_validator_class = $this->make(
            'Vindi_Subscription_Helper_Validator',
            [
                'orderHandler' => $dummy_order_class
            ]
        );

        \Codeception\Stub\Expected::once($dummy_order_class);
        $dummy_validator_class->validateChargeWebhook(
            json_decode($this->webhooks::CHARGE_REJECTED_WEBHOOK, true
        )['event']['data']);
    }

    public function testFailureValidateChargeWebhookWithoutBill()
    {
        $dummy_order_class = $this->make(
            'Vindi_Subscription_Helper_Order',
            [
                'getOrderFromVindi' => null
            ]
        );

        $dummy_validator_class = $this->make(
            'Vindi_Subscription_Helper_Validator',
            [
                'orderHandler' => $dummy_order_class
            ]
        );

        \Codeception\Stub\Expected::once($dummy_order_class);
        $this->assertFalse($dummy_validator_class->validateChargeWebhook(
            json_decode($this->webhooks::CHARGE_REJECTED_WEBHOOK, true
        )['event']['data']));
    }

    public function testFailureValidateChargeWebhookWithoutOrder()
    {
        $dummy_order_class = $this->makeEmpty(
            'Vindi_Subscription_Helper_Order',
            [
                'getOrderFromVindi' => json_decode(
                    $this->webhooks::BILL_CREATED_WEBHOOK, true
                )['event']['data']['bill']
            ],
            [
                'getOrderFromMagento' => null
            ],
            [
                'getOrder' => null
            ]
        );

        $dummy_validator_class = $this->make(
            'Vindi_Subscription_Helper_Validator',
            [
                'orderHandler' => $dummy_order_class
            ],
            [
                'logWebhook' => \Codeception\Stub\Expected::once(
                    'Pedido não encontrado.'
                )
            ]

        );

        \Codeception\Stub\Expected::once($dummy_order_class);
        $this->assertFalse($dummy_validator_class->validateChargeWebhook(
            json_decode($this->webhooks::CHARGE_REJECTED_WEBHOOK, true
        )['event']['data']));
    }

    public function testSuccessValidateChargeWebhookButCanNotInvoice()
    {
        $dummy_order_class = $this->makeEmpty(
            'Vindi_Subscription_Helper_Order',
            [
                'getOrderFromVindi' => json_decode(
                    $this->webhooks::BILL_CREATED_WEBHOOK, true
                )['event']['data']['bill']
            ],
            [
                'getOrderFromMagento' => ['order' => ['id' => 1]]
            ],
            [
                'getOrder' => ['order' => ['id' => 1]]
            ],
            [
                'canInvoice' => false
            ]
        );

        $dummy_validator_class = $this->make(
            'Vindi_Subscription_Helper_Validator',
            [
                'orderHandler' => $dummy_order_class
            ],
            [
                'logWebhook' => \Codeception\Stub\Expected::atLeastOnce(
                    'Evento não processado!'
                )
            ]
        );

        \Codeception\Stub\Expected::once($dummy_order_class);
        $dummy_validator_class->validateChargeWebhook(
            json_decode($this->webhooks::CHARGE_REJECTED_WEBHOOK, true
        )['event']['data']);
    }

    public function testSuccessValidateBillCreatedWebhookWithPaidStatus()
    {
        $dummy_order_class = $this->makeEmpty(
            'Vindi_Subscription_Helper_Order',
            [
                'getOrderFromVindi' => json_decode(
                    $this->webhooks::SINGLE_BILL_PAID_WEBHOOK, true
                )['event']['data']['bill']
            ],
            [
                'getOrder' => ['order' => ['id' => 1]]
            ],
            [
                'getOrderFromMagento' => ['order' => ['id' => 1]]
            ]
        );

        $dummy_validator_class = $this->makeEmpty(
            'Vindi_Subscription_Helper_Validator',
            [
                'orderHandler' => $dummy_order_class
            ],
            [
                'validateBillPaidWebhook' => true
            ]
        );

        \Codeception\Stub\Expected::atLeastonce($dummy_order_class);
        \Codeception\Stub\Expected::once(
            $dummy_validator_class->validateBillPaidWebhook(
                json_decode($this->webhooks::SINGLE_BILL_PAID_WEBHOOK, true)['event']['data']['bill']
            )
        );
    }

    public function testSuccessValidateBillCreatedWebhookWithPendingStatus()
    {
        $dummy_order_class = $this->makeEmpty(
            'Vindi_Subscription_Helper_Order',
            [
                'getOrderFromVindi' => json_decode(
                    $this->webhooks::BILL_CREATED_WEBHOOK, true
                )['event']['data']['bill']
            ],
            [
                'getOrder' => ['order' => ['id' => 1]]
            ],
            [
                'getOrderFromMagento' => ['order' => ['id' => 1]]
            ]
        );

        $dummy_bill_class = $this->makeEmpty(
            'Vindi_Subscription_Helper_Bill',
            [
                'processBillCreated' => true
            ]
        );

        $dummy_validator_class = $this->makeEmpty(
            'Vindi_Subscription_Helper_Validator',
            [
                'orderHandler' => $dummy_order_class
            ],
            [
                'billHandler'  => $dummy_bill_class
            ]
        );

        \Codeception\Stub\Expected::once($dummy_bill_class);
        \Codeception\Stub\Expected::once(
            $dummy_bill_class->processBillCreated(
                json_decode($this->webhooks::BILL_CREATED_WEBHOOK, true)['event']['data']['bill']
            )
        );
        $dummy_validator_class->validateBillCreatedWebhook(
            json_decode($this->webhooks::BILL_CREATED_WEBHOOK, true
        )['event']['data']);
    }

    public function testSuccessValidateBillCreatedWebhookForSinglePayments()
    {
        $dummy_order_class = $this->makeEmpty(
            'Vindi_Subscription_Helper_Order',
            [
                'getOrderFromVindi' => json_decode(
                    $this->webhooks::SINGLE_BILL_CREATED_WEBHOOK, true
                )['event']['data']['bill']
            ]
        );

        $dummy_validator_class = $this->make(
            'Vindi_Subscription_Helper_Validator',
            [
                'orderHandler' => $dummy_order_class
            ],
            [
                'logWebhook' => \Codeception\Stub\Expected::once(
                    'Ignorando o evento "bill_created" para venda avulsa.'
                )
            ]
        );
        $this->assertTrue($dummy_validator_class->validateBillCreatedWebhook(
            json_decode($this->webhooks::SINGLE_BILL_CREATED_WEBHOOK, true
        )['event']['data']));
    }

    public function testSuccessValidateBillCreatedWebhookForFirstPeriod()
    {
        $dummy_order_class = $this->makeEmpty(
            'Vindi_Subscription_Helper_Order',
            [
                'getOrderFromVindi' => json_decode(
                    $this->webhooks::FIRST_PERIOD_BILL_CREATED_WEBHOOK, true
                )['event']['data']['bill']
            ]
        );

        $dummy_validator_class = $this->make(
            'Vindi_Subscription_Helper_Validator',
            [
                'orderHandler' => $dummy_order_class
            ],
            [
                'logWebhook' => \Codeception\Stub\Expected::once(
                    'Ignorando o evento "bill_created" para o primeiro ciclo.'
                )
            ]
        );
        $this->assertTrue($dummy_validator_class->validateBillCreatedWebhook(
            json_decode($this->webhooks::FIRST_PERIOD_BILL_CREATED_WEBHOOK, true
        )['event']['data']));
    }

    public function testFailureValidateBillCreatedWebhook()
    {
        $dummy_validator_class = $this->make(
            'Vindi_Subscription_Helper_Validator',
            [
                'logWebhook' => \Codeception\Stub\Expected::once(
                    'Erro ao interpretar webhook "bill_created".'
                )
            ]
        );
        $this->assertFalse($dummy_validator_class->validateBillCreatedWebhook(
            json_decode($this->webhooks::CHARGE_REJECTED_WEBHOOK, true
        )['event']['data']));
    }

    public function testFailureValidateBillCreatedWebhookInvalid()
    {
        $dummy_order_class = $this->makeEmpty(
            'Vindi_Subscription_Helper_Order',
            [
                'getOrderFromVindi' => json_decode(
                    $this->webhooks::INVALID_BILL_CREATED_WEBHOOK, true
                )['event']['data']['bill']
            ]
        );

        $dummy_validator_class = $this->make(
            'Vindi_Subscription_Helper_Validator',
            [
                'orderHandler' => $dummy_order_class
            ],
            [
                'logWebhook' => \Codeception\Stub\Expected::once(
                    'Pedido anterior não encontrado. Ignorando evento.'
                )
            ]
        );
        $this->assertFalse($dummy_validator_class->validateBillCreatedWebhook(
            json_decode($this->webhooks::INVALID_BILL_CREATED_WEBHOOK, true
        )['event']['data']));
    }

    public function testSuccessValidateBillPaidWebhookForSinglePayments()
    {
        $dummy_order_class = $this->makeEmpty(
            'Vindi_Subscription_Helper_Order',
            [
                'getOrderFromMagento' => ['order' => ['id' => 1]]
            ]
        );

        $dummy_bill_class = $this->makeEmpty(
            'Vindi_Subscription_Helper_Bill',
            [
                'processBillPaid' => true
            ]
        );

        $dummy_validator_class = $this->makeEmpty(
            'Vindi_Subscription_Helper_Validator',
            [
                'getBillInfo' => array(
                    'type'  => 'fatura',
                    'id'    => 123456,
                    'cycle' => null
                )
            ],
            [
                'orderHandler' => $dummy_order_class
            ],
            [
                'billHandler'  => $dummy_bill_class
            ]

        );

        \Codeception\Stub\Expected::atLeastonce($dummy_bill_class);
        \Codeception\Stub\Expected::once(
            $dummy_bill_class->processBillPaid(
                ['order' => ['id' => 1]],
                json_decode($this->webhooks::SINGLE_BILL_PAID_WEBHOOK, true)['event']['data']
            )
        );
        $dummy_validator_class->validateBillPaidWebhook(
            json_decode($this->webhooks::SINGLE_BILL_PAID_WEBHOOK, true
        )['event']['data']);
    }

    public function testSuccessValidateBillPaidWebhookForRecurringPayments()
    {
        $dummy_order_class = $this->makeEmpty(
            'Vindi_Subscription_Helper_Order',
            [
                'getOrderFromMagento' => ['order' => ['id' => 1]]
            ]
        );

        $dummy_bill_class = $this->makeEmpty(
            'Vindi_Subscription_Helper_Bill',
            [
                'processBillPaid' => true
            ]
        );

        $dummy_validator_class = $this->makeEmpty(
            'Vindi_Subscription_Helper_Validator',
            [
                'getBillInfo' => array(
                    'type'  => 'assinatura',
                    'id'    => 123456,
                    'cycle' => null
                )
            ],
            [
                'orderHandler' => $dummy_order_class
            ],
            [
                'billHandler'  => $dummy_bill_class
            ]

        );

        \Codeception\Stub\Expected::atLeastonce($dummy_bill_class);
        \Codeception\Stub\Expected::once(
            $dummy_bill_class->processBillPaid(
                ['order' => ['id' => 1]],
                json_decode($this->webhooks::BILL_PAID_WEBHOOK, true)['event']['data']
            )
        );
        $dummy_validator_class->validateBillPaidWebhook(
            json_decode($this->webhooks::BILL_PAID_WEBHOOK, true
        )['event']['data']);
    }


    public function testFailureValidateBillPaidWebhookInvalid()
    {
        $dummy_order_class = $this->makeEmpty(
            'Vindi_Subscription_Helper_Order',
            [
                'getOrderFromMagento' => ['order' => ['id' => 1]]
            ]
        );

        $dummy_bill_class = $this->makeEmpty(
            'Vindi_Subscription_Helper_Bill',
            [
                'processBillPaid' => true
            ]
        );

        $dummy_validator_class = $this->makeEmpty(
            'Vindi_Subscription_Helper_Validator',
            [
                'getBillInfo' => array(
                    'type'  => 'assinatura',
                    'id'    => 123456,
                    'cycle' => null
                )
            ],
            [
                'orderHandler' => $dummy_order_class
            ],
            [
                'billHandler'  => $dummy_bill_class
            ],
            [
                'logWebhook' => \Codeception\Stub\Expected::once(
                    'Impossível atualizar status do pedido!'
                )
            ]

        );
        $dummy_validator_class->validateBillCreatedWebhook(
            json_decode($this->webhooks::INVALID_BILL_CREATED_WEBHOOK, true
        )['event']['data']);
    }

    public function testSuccessGetBillInfoForSinglePayments()
    {
        $subscription_dummy = array(
            'type'  => 'fatura',
            'id'    => 123456,
            'cycle' => null
        );

        $this->assertEquals($subscription_dummy, $this->validator->getBillInfo(
            json_decode($this->webhooks::SINGLE_BILL_PAID_WEBHOOK, true
        )['event']['data']['bill']));
    }

    public function testSuccessGetBillInfoForRecurringPayments()
    {
        $subscription_dummy = array(
            'type'  => 'assinatura',
            'id'    => 1,
            'cycle' => 2
        );

        $this->assertEquals($subscription_dummy, $this->validator->getBillInfo(
            json_decode($this->webhooks::BILL_PAID_WEBHOOK, true
        )['event']['data']['bill']));
    }
}
