<?php

class Vindi_Subscription_Helper_Bill
{
	protected $logger;
	protected $orderHandler;

	public function __construct() {
		$this->logger       = Mage::helper('vindi_subscription/logger');
		$this->orderHandler = Mage::helper('vindi_subscription/order');
	}

	/**
	 * Trata Webhook 'bill_created'
	 * A fatura pode estar relacionada a uma assinatura ou uma compra avulsa
	 *
	 * @param array $data
	 *
	 * @return bool
	 */
	public function processBillCreated($data)
	{
		$bill = $data['bill'];
		$vindiData = $this->loadBillData($data);
		$lastOrder = $this->getLastPeriod($data);

		$order = $this->orderHandler->createOrder($lastOrder, $vindiData);

		if (! $order) {
			$this->logger->log('Impossível gerar novo pedido!', 4);
			return false;
		}
		return $this->orderHandler->renewalOrder($order, $vindiData);
	}

	/**
	 * Retorna o último pedido referente a assinatura
	 *
	 * @param array $data
	 *
	 * @return bool|Mage_Sales_Model_Order
	 */
	public function getLastPeriod($data)
	{
		$currentPeriod = $data['bill']['period']['cycle'];
		$subscriptionId = $data['bill']['subscription']['id'];
		return $this->orderHandler->getOrderFromMagento('subscription',
			$subscriptionId, $currentPeriod - 1);
	}

	/**
	 * Carrega os dados do Webhook de fatura criada
	 *
	 * @param array $data
	 *
	 * @return array
	 */       
	public function loadBillData($data)
	{
		$vindiData = [
			'bill'     => [
				'id'           => $data['bill']['id'],
				'amount'       => $data['bill']['amout'],
				'subscription' => $data['bill']['subscription']['id'],
				'cycle'        => $data['bill']['period']['cycle']
			],
			'products' => [],
			'shipping' => [],
		];

		foreach ($data['bill']['bill_items'] as $billItem) {
			if ($billItem['product']['code'] == 'frete') {
				$vindiData['shipping'] = $billItem;
				continue;
			}
			$vindiData['products'][] = $billItem;
		}
		return $vindiData;
	}

	/**
	 * Trata o Webhook 'bill_paid'
	 * A fatura pode estar relacionada a uma assinatura ou uma compra avulsa
	 *
	 * @param array $data
	 *
	 * @return bool
	 */
	public function processBillPaid($data)
	{
		$order = $this->orderHandler->getOrder($data);
		if (! $order) {
			$this->logger->log(sprintf(
				'Ainda não existe um pedido para ciclo %s da assinatura: %d.',
				$data['bill']['period']['cycle'], $data['bill']['subscription']['id']), 4);
			return false;
		}
		return $this->orderHandler->createInvoice($order);
	}
}
