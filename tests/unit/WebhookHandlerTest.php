<?php 
require_once 'app/code/community/Vindi/Subscription/Trait/LogMessenger.php';
require_once 'app/code/community/Vindi/Subscription/Trait/ExceptionMessenger.php';
require_once 'app/code/community/Vindi/Subscription/Helper/WebhookHandler.php';
require_once 'app/code/community/Vindi/Subscription/Helper/Validator.php';

class WebhookHandlerTest extends \Codeception\Test\Unit
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
     * @var \Vindi_Subscription_Helper_WebhookHandler
     */
    protected $webhook_handler;
    
    protected function _before()
    {
        $this->webhook_handler = new Vindi_Subscription_Helper_WebhookHandler();
        $this->webhooks = new Webhooks();
    }

    public function testFailureHandleWebhook()
    {
        $dummy_class = $this->make(
            'Vindi_Subscription_Helper_WebhookHandler',
            [
                'logWebhook' => \Codeception\Stub\Expected::once(
                    'Evento do webhook ignorado pelo plugin: '
                ),
                'error'      => \Codeception\Stub\Expected::atLeastOnce(
                    'Evento do Webhook não encontrado!'
                ),
            ]
        );

        $dummy_class->handle('');
    }

    public function testSuccessfullHandleTestWebhook()
    {
        $dummy_class = $this->make(
            'Vindi_Subscription_Helper_WebhookHandler',
            [
                'logWebhook' => \Codeception\Stub\Expected::once(
                    'Evento de teste do webhook.'
                )
            ]
        );

        $this->assertTrue($dummy_class->handle($this->webhooks::TEST_WEBHOOK));
    }

    public function testSuccessfullHandleBillCreatedWebhook()
    {
        $dummy_validator_class = $this->make(
            'Vindi_Subscription_Helper_Validator',
            [
                'validateBillCreatedWebhook' => true
            ]
        );

        $dummy_webhook_class = $this->constructEmpty(
            'Vindi_Subscription_Helper_WebhookHandler',
            [
                'validator' => $dummy_validator_class
            ]
        );

        \Codeception\Stub\Expected::once($dummy_validator_class);
        $dummy_webhook_class->handle($this->webhooks::BILL_CREATED_WEBHOOK);
    }

    public function testSuccessfullHandleBillPaidWebhook()
    {
        $dummy_validator_class = $this->make(
            'Vindi_Subscription_Helper_Validator',
            [
                'validateBillPaidWebhook' => true
            ]
        );

        $dummy_webhook_class = $this->constructEmpty(
            'Vindi_Subscription_Helper_WebhookHandler',
            [
                'validator' => $dummy_validator_class
            ]
        );

        \Codeception\Stub\Expected::once($dummy_validator_class);
        $dummy_webhook_class->handle($this->webhooks::SINGLE_BILL_PAID_WEBHOOK);
    }
}