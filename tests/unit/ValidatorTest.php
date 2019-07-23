<?php 
require_once 'app/code/community/Vindi/Subscription/Trait/LogMessenger.php';
require_once 'app/code/community/Vindi/Subscription/Helper/Bill.php';
require_once 'app/code/community/Vindi/Subscription/Helper/Order.php';

class ValidatorTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

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

    protected function _after()
    {
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
}