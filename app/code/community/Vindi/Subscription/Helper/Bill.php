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
		$lastOrder = $this->getLastPeriod($data);

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
				'amount'       => $data['bill']['amount'],
				'subscription' => $data['bill']['subscription']['id'],
				'cycle'        => $data['bill']['period']['cycle']
			],
			'products' => [],
			'shipping' => [],
			'taxes'    => [],
		];

		foreach ($data['bill']['bill_items'] as $billItem) {
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
		return $vindiData;
	}

	/**
	 * Registra o pagamento do pedido no Magento
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
