<?php

class Vindi_Subscription_Helper_WebhookHandler extends Mage_Core_Helper_Abstract
{

	protected $logger;
	protected $billHandler;
	protected $orderHandler;
	protected $validator;

	public function __construct() {
		$this->logger       = Mage::helper('vindi_subscription/logger');
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
			$this->logger->log('Falha ao interpretar JSON do webhook: ' . $e->getMessage(), 5);
			return false;
		}

		switch ($type) {
			// the webhook is being called before Order is actually placed.
			// Sorry for this, not going to use queues for now, so the solution is to use sleep().

		case 'test':
			$this->logger->log('Evento de teste do webhook.');
			return false;
		case 'bill_created':
			return $this->validator->validateBillCreatedWebhook($data);
		case 'bill_paid':
			return $this->billHandler->processBillPaid($data);
		case 'charge_rejected':
			return $this->validator->validateChargeWebhook($data);
		default:
			$this->logger->log('Evento do webhook ignorado pelo plugin: ' . $type);
			return true;
		}
	}
}
