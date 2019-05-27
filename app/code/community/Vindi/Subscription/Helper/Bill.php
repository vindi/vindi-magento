<?php

class Vindi_Subscription_Helper_Bill
{
	use Vindi_Subscription_Trait_LogMessenger; 

	protected $orderHandler;

	public function __construct() {
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
		$lastOrder = $this->getLastPeriod($bill);

		$order = $this->orderHandler->createOrder($lastOrder, $vindiData);

		if (! $order) {
			$this->logWebhook('Impossível gerar novo pedido!', 4);
			return false;
		}

		// Remove os produtos inativos
		$this->orderHandler->updateProductsList($order, $vindiData, $bill['charges']);

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
		$currentPeriod = $data['period']['cycle'];
		$subscriptionId = $data['subscription']['id'];
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
				'id'           => $data['id'],
				'amount'       => $data['amount'],
				'subscription' => $data['subscription']['id'],
				'cycle'        => $data['period']['cycle']
			],
			'products' => [],
			'shipping' => [],
			'taxes'    => [],
		];

		foreach ($data['bill_items'] as $billItem) {
			if ($billItem['product']['code'] == 'frete') {
				$vindiData['shipping'] = $billItem;
				continue;
			}
			if ($billItem['product']['code'] == 'taxa') {
				$vindiData['taxes'][] = $billItem;
				continue;
			}
			$vindiData['products'][] = $billItem;

		}
		$vindiData['products'] = $this->unifiesProducts($vindiData['products']);
		return $vindiData;
	}

	/**
	 * Unifica os produtos somando os valores e quantidades
	 *
	 * @param array | $vindiData['products']
	 *
	 * @return array | new $vindiData['products']
	 */ 
	public function unifiesProducts($currentData)
	{
		$newData = array();
		$lastCode = null;
		$key = 0;

		foreach ($currentData as $product) {
			if ($lastCode && $lastCode == $product['product']['code']) {
				$newData[$key - 1]['quantity'] += $product['quantity'];
				(float)$newData[$key - 1]['pricing_schema']['price'] +=
				(float)$product['pricing_schema']['price'];
				$key++;
				continue;
			}
			$newData[$key] = $product;
			$lastCode = $product['product']['code'];
			$key++;
		}
		return $newData;
	}

	/**
	 * Trata o Webhook 'bill_paid'
	 * A fatura pode estar relacionada a uma assinatura ou uma compra avulsa
	 *
	 * @param Mage_Sales_Model_Order $order | array $data
	 *
	 * @return bool
	 */
	public function processBillPaid($order, $data)
	{
		return $this->orderHandler->createInvoice($order, $data);
	}
}
