<?php

class Vindi_Subscription_Helper_WebhookHandler extends Mage_Core_Helper_Abstract
{
	use Vindi_Subscription_Trait_LogMessenger;
	use Vindi_Subscription_Trait_ExceptionMessenger;

	protected $billHandler;
	protected $orderHandler;
	protected $validator;

	public function __construct() {
		$this->billHandler  = Mage::helper('vindi_subscription/bill');
		$this->orderHandler = Mage::helper('vindi_subscription/order');
		$this->validator    = Mage::helper('vindi_subscription/validator');
	}

	/**
	 * Filtra os Webhooks recebidos
	 *
	 * @param string $body
	 *
	 * @return bool
	 */
	public function handle($body)
	{
		try {
			$jsonBody = json_decode($body, true);

			if (! $jsonBody || ! isset($jsonBody['event'])) {
				$this->error('Evento do Webhook não encontrado!');
			}

			$type = $jsonBody['event']['type'];
			$data = $jsonBody['event']['data'];
		} catch (Exception $e) {
			$this->logWebhook('Falha ao interpretar JSON do webhook: ' . $e->getMessage(), 5);
			return false;
		}

		switch ($type) {
			// O Webhook pode ser recebido antes que o pedido seja criado.
			// Para contornar é possível utilizar o sleep() ou criar filas.

		case 'test':
			$this->logWebhook('Evento de teste do webhook.');
			return true;
		case 'bill_created':
			return $this->validator->validateBillCreatedWebhook($data['bill']);
		case 'bill_paid':
			return $this->validator->validateBillPaidWebhook($data['bill']);
		case 'charge_rejected':
			return $this->validator->validateChargeWebhook($data);
		default:
			$this->logWebhook('Evento do webhook ignorado pelo plugin: ' . $type);
			return true;
		}
	}
}
