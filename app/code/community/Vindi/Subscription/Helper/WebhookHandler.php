<?php

class Vindi_Subscription_Helper_WebhookHandler extends Mage_Core_Helper_Abstract
{
  use Vindi_Subscription_Trait_LogMessenger;

	protected $billHandler;
	protected $orderHandler;
	protected $validator;

	private $local = 'vindi_webhooks.log';

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
				Mage::throwException('Evento do Webhook não encontrado!');
			}

			$type = $jsonBody['event']['type'];
			$data = $jsonBody['event']['data'];
		} catch (Exception $e) {
			$this->log('Falha ao interpretar JSON do webhook: ' . $e->getMessage(), $local, 5);
			return false;
		}

		switch ($type) {
			// O Webhook pode ser recebido antes que o pedido seja criado.
			// Para contornar é possível utilizar o sleep() ou criar filas.

		case 'test':
			$this->log('Evento de teste do webhook.', $local);
			return false;
		case 'bill_created':
			return $this->validator->validateBillCreatedWebhook($data);
		case 'bill_paid':
			return $this->billHandler->processBillPaid($data);
		case 'charge_rejected':
			return $this->validator->validateChargeWebhook($data);
		default:
			$this->log('Evento do webhook ignorado pelo plugin: ' . $type, $local);
			return true;
		}
	}
}
