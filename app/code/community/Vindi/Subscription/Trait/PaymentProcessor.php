<?php

trait Vindi_Subscription_Trait_PaymentProcessor
{
	/**
	 * @param string $paymentAction
	 * @param object $stateObject
	 *
	 * @return bool|Mage_Payment_Model_Method_Abstract
	 */
	public function initialize($paymentAction = null, $stateObject)
	{
		if (is_null($paymentAction)){
			return;
		}

		if ($this->checkForReorder()) {
			return $this->processReorder($stateObject);
		}

		return $this->processNewOrder($stateObject);
	}

	/**
	 * @return bool
	 */
	protected function checkForReorder()
	{
		$session = Mage::getSingleton('core/session');
		$isReorder = $session->getData('vindi_is_reorder', false);
		$session->unsetData('vindi_is_reorder');

		return $isReorder;
	}

	/**
	 * @param Mage_Customer_Model_Customer $customer
	 *
	 * @return bool|string
	 * @throws \Mage_Core_Exception
	 */
	protected function getCustomerTipoPessoa($customer)
	{
		$attribute = Mage::getSingleton('eav/config')->getAttribute('customer', 'tipopessoa');

		$tipopessoa = $customer->getTipopessoa();

		if ($attribute && $attribute->usesSource() && $tipopessoa) {
			return $attribute->getSource()->getOptionText($tipopessoa);
		}

		return false;
	}

	/**
	 * @param array Customer phones $phone
	 * @return array
	 */
	public function formatPhone($phone)
	{
		$phone = '55' . preg_replace('/^0|\D+/', '', $phone);

		switch(strlen($phone)) {
			case 12:
				$phoneType = 'landline';
				break;
			case 13:
				$phoneType = 'mobile';
				break;
		}

		if (isset($phoneType)) {
			return array(array(
				'phone_type' => $phoneType,
				'number'     => $phone
			));
		}
	}

	/**
	 * @param Mage_Sales_Model_Order       $order
	 * @param Mage_Customer_Model_Customer $customer
	 *
	 * @return bool|int|null
	 */
   protected function createCustomer($order, $customer)
   {
		$billing = $order->getBillingAddress();

		if (Mage::app()->getStore()->isAdmin()) {
			$customer = Mage::getSingleton('adminhtml/session_quote')->getCustomer();
		} else {
			$customer->setWebsiteId(Mage::app()->getWebsite()->getId());
			$customer->loadByEmail($billing->getEmail());
		}

		//TODO fix user being created again if validation fails
		if (! ($userCode = $customer->getVindiUserCode())) {
			$userCode = 'mag-' . $customer->getId() . '-' . time();

			$customer->setVindiUserCode($userCode);
			$customer->save();
		}

		$address = array(
			'street'             => $billing->getStreet(1),
			'number'             => $billing->getStreet(2),
			'additional_details' => $billing->getStreet(3),
			'neighborhood'       => $billing->getStreet(4),
			'zipcode'            => $billing->getPostcode(),
			'city'               => $billing->getCity(),
			'state'              => $billing->getRegionCode(),
			'country'            => $billing->getCountry(),
		);

		$customerVindi = array(
			'name'          => $billing->getFirstname() . ' ' . $billing->getLastname(),
			'email'         => $order->getBillingAddress()->getEmail(),
			'registry_code' => $order->getData('customer_taxvat'),
			'code'          => $userCode,
			'phones'        => $this->formatPhone($order->getBillingAddress()->getTelephone()),
			'address'       => $address
		);

		if (Mage::getStoreConfig('vindi_subscription/general/send_nfe_information')) {
			switch ($this->getCustomerTipoPessoa($customer)) {
				case "Física":
					$customerVindi['metadata'] = array(
						'carteira_de_identidade' => $customer->getIe(),
					);
					break;
				case "Jurídica":
					$customerVindi['metadata'] = array(
						'inscricao_estadual' => $customer->getIe(),
					);
					break;
			}
		}

		$customerId = $this->api()->findOrCreateCustomer($customerVindi);

		if ($customerId === false) {
			$this->error('Falha ao registrar o usuário. Verifique os dados e tente novamente!');
		}

		return $customerId;
	}

	/**
	 * @return Vindi_Subscription_Helper_API
	 */
	public function api()
	{
		if (isset($this->vindiApi)) {
			return $this->vindiApi;
		}

		return $this->vindiApi = Mage::helper('vindi_subscription/api');
	}

	/**
	 * @param object $stateObject
	 *
	 * @return bool|Mage_Payment_Model_Method_Abstract
	 */
	protected function processNewOrder($stateObject)
	{
		$payment = $this->getInfoInstance();
		$order = $payment->getOrder();

		$customer = Mage::getModel('customer/customer');

		$customerId      = $this->createCustomer($order, $customer);
		$customerVindiId = $customer->getVindiUserCode();

		$hasPaymentProfile = $this->processCardInformation($payment, $customerId, $customerVindiId);

		$bill = $this->filterOrder($order, $payment, $customerId);

		if (! $bill || ! $hasPaymentProfile || ! $order->getId() || ! $order->canInvoice()) {
			return false;
		}

		if ($this->processPaidReturn($bill)) {
			$orderHandler = Mage::helper('vindi_subscription/order');
			$orderHandler->updateToSuccess($order);
			$stateObject->setStatus(Mage_Sales_Model_Order::STATE_PROCESSING)
				->setState(Mage_Sales_Model_Order::STATE_PROCESSING);

			return $this;
		}

		$stateObject->setStatus(Mage_Sales_Model_Order::STATE_PENDING_PAYMENT)
			->setState(Mage_Sales_Model_Order::STATE_PENDING_PAYMENT);

		return $this;
	}

	protected function filterOrder($order, $payment, $customerId)
	{
		if ($this->isSingleOrder($order)) {
			return $this->processSinglePayment($payment, $order, $customerId);
		}

		return $this->processSubscription($payment, $order, $customerId);
	}

	/**
	 * @param object $stateObject
	 *
	 * @return bool|Mage_Payment_Model_Method_Abstract
	 */
	protected function processReorder($stateObject)
	{
		$payment = $this->getInfoInstance();
		$order = $payment->getOrder();

		$payment->setAmount($order->getTotalDue());
		$this->setStore($order->getStoreId());

		$payment->setStatus(
			Mage_Sales_Model_Order::STATE_PENDING_PAYMENT,
			Mage_Sales_Model_Order::STATE_PENDING_PAYMENT,
			'Novo período da assinatura criado', true
		);

		$stateObject->setStatus(Mage_Sales_Model_Order::STATE_PENDING_PAYMENT)
			->setState(Mage_Sales_Model_Order::STATE_PENDING_PAYMENT);

		return $this;
	}

	/**
	 * @param Mage_Payment_Model_Method_Abstract $payment
	 * @param Mage_Sales_Model_Order             $order
	 * @param int                                $customerId
	 *
	 * @return bool
	 * @throws \Mage_Core_Exception
	 */
	protected function processSubscription($payment, $order, $customerId)
	{
		$subscription = $this->createSubscription($payment, $order, $customerId);

		if ($subscription === false) {
			$this->error('Erro ao criar a assinatura. Verifique os dados e tente novamente!');

			return false;
		}

		$payment->setAmount($order->getTotalDue());
		$this->setStore($order->getStoreId());

		$payment->setStatus(
			Mage_Sales_Model_Order::STATE_PENDING_PAYMENT,
			Mage_Sales_Model_Order::STATE_PENDING_PAYMENT,
			'Assinatura criada', true
		);

		return $subscription['bill'];
	}

	/**
	 * @param Mage_Payment_Model_Method_Abstract $payment
	 * @param Mage_Sales_Model_Order             $order
	 * @param int                                $customerId
	 *
	 * @return bool
	 * @throws \Mage_Core_Exception
	 */
	protected function processSinglePayment($payment, $order, $customerId)
	{
		$body = array(
			'customer_id'         => $customerId,
			'payment_method_code' => $this->getPaymentMethodCode(),
			'bill_items'          => $this->api()->findOrCreateUniquePaymentProduct($order)
		);

		$paymentProfile = $payment->getPaymentProfile();

		if ($paymentProfile) {
			$body['payment_profile'] = array(
				'id' => $paymentProfile['payment_profile']['id']
			);
		}

		if ($installments = $payment->getAdditionalInformation('installments')) {
			$body['installments'] = (int) $installments;
		}

		$bill = $this->api()->createBill($body);

		if ($this->validatePayment($bill, $order, $payment)) {
			$order->setVindiBillId($bill['id']);
			$order->save();
			return $bill;
		}
	}

	/**
	 * @param Mage_Payment_Model_Method_Abstract    $payment
	 * @param Mage_Sales_Model_Order                $order
	 * @param int                                   $customerId
	 *
	 * @return bool
	 */
	protected function createSubscription($payment, $order, $customerId)
	{
		$orderItems = $order->getItemsCollection();

		foreach ($orderItems as $item) {
			$plan = !empty($plan = $this->getCurrentVindiPlan($item)) ? $plan : null;

			if (null !== $plan) {
				break;
			}
		}

		$productItems = $this->api()->buildPlanItemsForSubscription($order);
		if (!$productItems) {
			return false;
		}

		$body = array(
			'customer_id'         => $customerId,
			'payment_method_code' => $this->getPaymentMethodCode(),
			'plan_id'             => $plan,
			'code'                => 'mag-' . $order->getIncrementId() . '-' . time(),
			'product_items'       => $productItems,
		);

		if ($installments = $payment->getAdditionalInformation('installments')) {
			$body['installments'] = (int) $installments;
		}

		$paymentProfile = $payment->getPaymentProfile();

		if ($paymentProfile) {
			$body['payment_profile'] = array(
				'id' => $paymentProfile['payment_profile']['id']
			);
		}

		$subscription = $this->api()->createSubscription($body);

		$this->log(json_encode($payment->getAdditionalInformation()), $this->_code . '.log');

		if ($this->validatePayment($subscription, $order, $payment)) {
			$order->setVindiSubscriptionId($subscription['id']);
			$order->setVindiBillId($subscription['bill']['id']);
			$order->setVindiSubscriptionPeriod(1);
			$order->save();
			return $subscription;
		}
	}

	public function cancelOrder($order)
	{
		$order->setStatus(
			Mage_Sales_Model_Order::STATE_CANCELED,
			Mage_Sales_Model_Order::STATE_CANCELED,
			'Houve um problema na confirmação do pagamento.',
			true
		);
	}

	public function validatePayment($payment, $order, $orderAttempt)
	{
		$type = 'bills';
		if($payment && $payment['id']) {
			$vindiId = $payment['id'];
			$billing_type = 'beginning_of_period';
			if (! $this->isSingleOrder($order)) {
				$billing_type = $payment['billing_trigger_type'];
				$payment = $payment['bill'];
				$type = 'subscriptions';
			}

			$paymentMethod = reset($payment['charges'])['payment_method']['type'];
			if ($paymentMethod === 'PaymentMethod::BankSlip'
				|| $paymentMethod === 'PaymentMethod::OnlineBankSlip'
				|| $paymentMethod === 'PaymentMethod::DebitCard'
				|| $billing_type !== 'beginning_of_period'
				|| $payment['status'] === 'paid'
				|| $payment['status'] === 'review'
				|| reset($payment['charges'])['status'] === 'fraud_review')
				return $payment;

			$this->api()->cancelPurchase($vindiId, $type);
		}

		$this->cancelOrder($orderAttempt);
		$this->log(
			sprintf('Erro no pagamento do pedido %d.', $order->getId()), 'vindi_api.log'
		);
		$message = 'Houve um problema na confirmação do pagamento. ' .
		'Verifique os dados e tente novamente.';
		$this->error($message);
	}

	public function getCurrentVindiPlan($product)
	{
		return Mage::getModel('catalog/product')
			->load($product->getProductId())
			->getData('vindi_subscription_plan');
	}

	/*
	 * @param Mage_Sales_Model_Order $order
	 */
	protected function isSingleOrder($order)
	{
		if (! $order) {
			return false;
		}

		foreach ($order->getAllVisibleItems() as $item) {
			if (($product = $item->getProduct()) && ($product->getTypeId() === 'subscription')) {
				return false;
			}
		}
		return true;
	}
}
