<?php

class Vindi_Subscription_Helper_WebhookHandler extends Mage_Core_Helper_Abstract
{
    
    protected $logger;
    protected $billHandler;
    protected $chargeHandler;
    
    public function __construct() {
        $this->logger = Mage::helper('vindi_subscription/logger');
        $this->billHandler =Mage::helper('vindi_subscription/bill');
        $this->chargeHandler =Mage::helper('vindi_subscription/charge');
    }
    
    /**
     * Handle incoming webhook.
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
            $this->logger->log(sprintf('Falha ao interpretar JSON do webhook: %s', $e->getMessage()), 5);
            return false;
        }

        switch ($type) {
            // the webhook is being called before Order is actually placed.
            // I'm sorry for this, not going to use queues for now, so the solution is to use sleep().

            case 'test':
                $this->logger->log('Evento de teste do webhook.');
                return false;
            case 'bill_created':
                return $this->billHandler->billCreated($data);
            case 'bill_paid':
                return $this->billHandler->billPaid($data);
            case 'charge_rejected':
                return $this->chargeHandler->chargeRejected($data);
            default:
                $this->logger->log(sprintf('Evento do webhook ignorado pelo plugin: "%s".', $type), 5);
        }
    }

    /**
     * @param Mage_Sales_Model_Order $order
     *
     * @return bool
     */
    public function createInvoice($order)
    {
        if (! $order->getId()) {
            return false;
        }

        $this->logger->log(sprintf('Gerando fatura para o pedido: %s.', $order->getId()));

        $order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, true,
            'O pagamento foi confirmado e o pedido está sendo processado.', true);

        if (! $order->canInvoice()) {
            $this->logger->log(sprintf('Impossível gerar fatura para o pedido %s.', $order->getId()), 4);

            // TODO define how to handle this

            return false;
        }
        $invoice = $order->prepareInvoice();
        $invoice->setRequestedCaptureCase(Mage_Sales_Model_Order_Invoice::CAPTURE_OFFLINE);
        $invoice->register();
        Mage::getModel('core/resource_transaction')->addObject($invoice)->addObject($invoice->getOrder())->save();
        $invoice->sendEmail(true);
        $this->logger->log('Fatura gerada com sucesso.');

        return true;
    }

    /**
     * @param array $data
     *
     * @return Mage_Sales_Model_Order|bool
     */
    private function getOrder($data)
    {
        if (! isset($data['bill'])) {
            return false;
        }

        if (isset($data['bill']['subscription']) && ($subscription = $data['bill']['subscription'])
            && ($subscriptionId = filter_var($subscription['id'], FILTER_SANITIZE_NUMBER_INT))
        ) {
            $order = $this->getOrderForPeriod($subscriptionId, $data['bill']['period']['cycle']);

            if (! $order || ! $order->getId()) {
                $this->logger->log(sprintf('Nenhum pedido encontrado para a assinatura: %d.', $subscriptionId));

                return false;
            }

            return $order;
        } else {
            $order = $this->getSingleOrder($data['bill']['id']);

            if (! $order || ! $order->getId()) {
                $this->logger->log(sprintf('Nenhum pedido encontrado para a fatura: %d.', $data['bill']['id']));

                return false;
            }

            return $order;
        }
    }

    /**
     * @param int $billId
     *
     * @return bool|\Mage_Sales_Model_Order
     */
    private function getOrderFromBill($billId)
    {
        /** @var Vindi_Subscription_Helper_API $api */
        $api = Mage::helper('vindi_subscription/api');

        if (! $bill = $api->getBill($billId)) {
            return false;
        }

        return $this->getOrder(compact('bill'));
    }

    /**
     * @param int $subscriptionId
     * @param int $subscriptionPeriod
     *
     * @return Mage_Sales_Model_Order
     */
    private function getOrderForPeriod($subscriptionId, $subscriptionPeriod)
    {
        return Mage::getModel('sales/order')->getCollection()
            ->addAttributeToSelect('*')
            ->addFieldToFilter('vindi_subscription_id', $subscriptionId)
            ->addFieldToFilter('vindi_subscription_period', $subscriptionPeriod)
            ->getFirstItem();
    }

    /**
     * @param int $billId
     *
     * @return Mage_Sales_Model_Order
     */
    private function getSingleOrder($billId)
    {
        return Mage::getModel('sales/order')->getCollection()
            ->addAttributeToSelect('*')
            ->addFieldToFilter('vindi_bill_id', $billId)
            ->getFirstItem();
    }

    private function updateProductsList($order, $vindiData)
    {
        $codes = [];
        foreach ($vindiData['products'] as $product) {
            $codes[] = $product['product']['code'];
        }

        $itens = $order->getAllItems();
        foreach ($itens as $item) {
            if (!in_array($item->getSku(), $codes)) {
                $item->delete();
                $order->setTotalItemCount(count($items) - 1);
                $order->setSubtotal($order->getSubtotal() - $item->getPrice());
                $order->save();
            }
        }
    }

    /**
     * Create a new order from an old one using reorder functionality.
     *
     * @param Mage_Sales_Model_Order $oldOrder
     *
     * @return Mage_Sales_Model_Order;
     */
    private function createOrder($oldOrder, $vindiData)
    {
        $oldOrder->setReordered(true);

        $model = Mage::getSingleton('adminhtml/sales_order_create');

        /** @var Mage_Adminhtml_Model_Sales_Order_Create $order */
        $order = $model->initFromOrder($oldOrder);

        $quote = $order->getQuote();

        // get shipping method
        $shippingMethod = $oldOrder->getShippingMethod();
        $activedShippingMethods = Mage::getSingleton('vindi_subscription/config_shippingmethod')->getActivedShippingMethodsValues();

        // verify if current shipping method is active
        if(!in_array($shippingMethod, $activedShippingMethods)){
            $oldShippingMethod = $shippingMethod;
            $shippingMethod = Mage::getStoreConfig('vindi_subscription/general/default_shipping_method');
            $this->logger->log(sprintf("Erro ao utilizar o método de envio %s alterado para o método padrão %s.",
                        $oldShippingMethod,
                        $shippingMethod
                    ));
            unset($oldShippingMethod);
        }

        // quote shipping method
        $quote->getShippingAddress()
            ->setShippingMethod($shippingMethod)
            ->setCollectShippingRates(true)
            ->collectShippingRates()
            ->collectTotals();

        if(isset($vindiData['shipping']['pricing_schema']['price']) && !empty($vindiData['shipping']['pricing_schema']['price']))
        {
            // set shipping price
            $billShippingPrice = $vindiData['shipping']['pricing_schema']['price'];

            $quote->setPrice($billShippingPrice)
                ->setCost($billShippingPrice);

            $address = $quote->getShippingAddress();
            $address->setShippingAmount($billShippingPrice);
            $address->setBaseShippingAmount($billShippingPrice);

            $rates = $address->collectShippingRates()
                            ->getGroupedAllShippingRates();
            foreach ($rates as $carrier) {
                foreach ($carrier as $rate) {
                    $rate->setPrice($billShippingPrice);
                    $rate->save();
                }
            }
            $address->save();
        }

        $quote->save();

        foreach ($vindiData['products'] as $item) {
            $magentoProduct = Mage::getModel('catalog/product')->loadByAttribute('vindi_product_id', $item['product']['id']);
            if(!$magentoProduct)
            {
                $this->logger->log(sprintf('O produto com ID Vindi #%s não existe no Magento.', $item['product']['id']), 5);
            }else{
                if(number_format($magentoProduct->getPrice(), 2) !== number_format($item['pricing_schema']['price'], 2)){
                    $this->logger->log(sprintf("Divergencia de valores na fatura #%s: produto %s: ID Magento #%s , ID Vindi #%s: Valor Magento R$ %s , Valor Vindi R$ %s",
                                $vindiData['bill']['id'],
                                $magentoProduct->getName(),
                                $magentoProduct->getId(),
                                $item['product']['id'],
                                $magentoProduct->getPrice(),
                                $item['pricing_schema']['price'])
                            );

                    $quote->getItemByProduct($magentoProduct)
                        // ->setPrice($item['pricing_schema']['price'])
                        // ->setCost($item['pricing_schema']['price'])
                        ->setOriginalCustomPrice($item['pricing_schema']['price'])
                        ->setCustomPrice($item['pricing_schema']['price'])
                        ->save();

                }
            }
        }

        $quote->setTotalsCollectedFlag(false)
            ->collectTotals()
            ->save();

        if (isset(reset($vindiData['taxes'])['pricing_schema']['price']) && !empty(reset($vindiData['taxes'])['pricing_schema']['price'])) {
                $quote->getShippingAddress()->setTaxAmount(reset($vindiData['taxes'])['pricing_schema']['price']);
        }

        $quote->collectTotals()
            ->save();

        $session = Mage::getSingleton('core/session');
        $session->setData('vindi_is_reorder', true);

        try {
            $order = $order->createOrder();
        } catch (Exception $e) {
            $this->logger->log("Erro ao criar pedido!");

            if($e->getMessage()){
                $this->logger->log($e->getMessage(), 5);
            }else{
                $messages = $order->getSession()->getMessages(true);
                foreach($messages->getItems() as $message)
                {
                   $this->logger->log($message->getText(), 5);
                }
            }

            return false;
        }

        return $order;
    }
}
