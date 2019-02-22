<?php

class Vindi_Subscription_Helper_Validator
{
	protected $billHandler;
	protected $orderHandler;

	private $local = 'vindi_webhooks.log';

	public function __construct() 
	{
		$this->billHandler  = Mage::helper('vindi_subscription/bill');
		$this->orderHandler = Mage::helper('vindi_subscription/order');
	}

	/**
	 * Valida estrutura do Webhook 'charge_rejected'
	 *
	 * @param array $data
	 *
	 * @return bool
	 */
	public function validateChargeWebhook($data)
	{
		$charge = $data['charge'];
		$order = $this->orderHandler->getOrderFromVindi($charge['bill']['id']);
		
		if (! $order) {
			$this->log('Pedido não encontrado.', $local, 4);
			return false;
		}

		$gatewayMessage = $charge['last_transaction']['gateway_message'];

		if (is_null($charge['next_attempt'])) {
			$this->orderHandler->updateToRejected($order, $gatewayMessage);
			$this->log(sprintf(
				'Todas as tentativas de pagamento do pedido %s foram rejeitadas. Motivo: "%s".',
				$order->getId(), $gatewayMessage), $local);
			return true;
		}

		$this->orderHandler->addRetryStatusMessage($order, $gatewayMessage);
		$this->log(sprintf(
			'Tentativa de pagamento do pedido %s foi rejeitada. Motivo: "%s".' .
			' Uma nova tentativa será feita.', $order->getId(), $gatewayMessage), $local);
		return true;
	}

	/**
	 * Valida estrutura do Webhook 'bill_created'
	 *
	 * @param array $data
	 *
	 * @return bool
	 */
	public function validateBillCreatedWebhook($data)
	{
		$bill = $data['bill'];

		if (! $bill) {
			$this->log('Erro ao interpretar webhook "bill_created".', $local, 5);
			return false;
		}

		if (! isset($bill['subscription']) || is_null($bill['subscription'])) {
			$this->log(sprintf('Ignorando o evento "bill_created" para venda avulsa.'), $local, 5);
			return false;
		}

		if (isset($bill['period']) && ($bill['period']['cycle'] === 1)) {
			$this->log(sprintf(
				'Ignorando o evento "bill_created" para o primeiro ciclo.'), $local, 5);
			return false;
		}

		$order = $this->orderHandler->getOrder($data);

		if ($order) {
			$this->log(sprintf('Já existe o pedido %s para o evento "bill_created".',
				$order->getId()), $local, 5);
			return false;
		}

		if (isset($bill['subscription']['id']) && $bill['period']['cycle']) {
			$this->billHandler->processBillCreated($data);
			return true;
		}
		$this->log('Pedido anterior não encontrado. Ignorando evento.', $local, 4);
		return false;
	}
}
