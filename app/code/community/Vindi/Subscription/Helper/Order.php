<?php

class Vindi_Subscription_Helper_Order
{
    protected $logger;
    protected $billHandler;

    public function __construct() {
        $this->logger       = Mage::helper('vindi_subscription/logger');
        $this->billHandler  = Mage::helper('vindi_subscription/bill');
    }

    /**
     * @param Mage_Sales_Model_Order $order, String $gatewayMessage
     *
     */
    public function updateToRejected($order, $gatewayMessage)
    {
        $order->setState(Mage_Sales_Model_Order::STATE_CANCELED, true,
            sprintf('Todas as tentativas de pagamento foram rejeitadas. Motivo: "%s".',
                $gatewayMessage), true);

        $order->save();
    }

    /**
     * @param Mage_Sales_Model_Order $order, String $gatewayMessage
     *
     */
    public function addRetryStatusMessage($order, $gatewayMessage)
    {
        $order->addStatusHistoryComment(sprintf(
            'Tentativa de Pagamento rejeitada. Motivo: "%s". Uma nova tentativa será feita.',
                $gatewayMessage));
    }

    /**
     * @param int $billId
     *
     * @return bool|Mage_Sales_Model_Order
     */
    private function getOrderFromVindi($billId)
    {
        /** @var Vindi_Subscription_Helper_API $api */
        $api = Mage::helper('vindi_subscription/api');

        if (! ($bill = $api->getBill($billId))) {
            return false;
        }
        return $this->getOrder(compact('bill'));
    }

    /**
     * @param array $data
     *
     * @return Mage_Sales_Model_Order|bool
     */
    public function getOrder($data)
    {
        if (!isset($data['bill'])) {
            return false;
        }

        if (isset($data['bill']['subscription']['id'])
            && ($orderCode = filter_var($data['bill']['subscription']['id'],
                FILTER_SANITIZE_NUMBER_INT))) {
            $order = $this->getSubscriptionOrder($orderCode, $data['bill']['period']['cycle']);
            $orderType = 'assinatura';
        }
        else {
            $orderCode = $data['bill']['id'];
            $order = $this->getSingleOrder($orderCode);
            $orderType = 'fatura';
        }

        if (!$order || !$order->getId()) {
            $this->logger->log(sprintf('Nenhum pedido encontrado para a "%s": %d.', $orderType,
                $orderCode));
            return false;
        }
        return $order;
    }

    /**
     * @param Mage_Sales_Model_Order $order
     *
     * @return bool
     */
    public function createInvoice($order)
    {
        if ($orderId = $order->getId() && $order->canInvoice()) {
            $this->logger->log('Gerando fatura para o pedido: ' . $orderId);
            $this->updateToSuccess($order);
            $this->logger->log('Fatura gerada com sucesso.');
            return true;
        }
        elseif ($orderId = $order->getId()) { 
            $this->logger->log('Impossível gerar fatura para o pedido ' . $orderId, 4);
        }
        return false;
    }

    public function updateToSuccess($order)
    {
        $invoice = $order->prepareInvoice();
        $invoice->setRequestedCaptureCase(Mage_Sales_Model_Order_Invoice::CAPTURE_OFFLINE);
        $invoice->register();
        Mage::getModel('core/resource_transaction')
            ->addObject($invoice)
            ->addObject($invoice
            ->getOrder())->save();
        $invoice->sendEmail(true);
        $order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, true,
            'O pagamento foi confirmado e o pedido está sendo processado.', true);
    }

    /**
     * @param int $subscriptionId
     * @param int $subscriptionPeriod
     *
     * @return Mage_Sales_Model_Order
     */
    private function getSubscriptionOrder($subscriptionId, $subscriptionPeriod)
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

    private function renewalOrder($order, $vindiData, $charges)
    {
        $order->setVindiSubscriptionId($vindiData['bill']['subscription']);
        $order->setVindiSubscriptionPeriod($vindiData['bill']['cycle']);
        $order->setBaseGrandTotal($vindiData['bill']['amount']);
        $order->setGrandTotal($vindiData['bill']['amount']);
        $order->save();

        if(Mage::getStoreConfig('vindi_subscription/general/bankslip_link_in_order_comment'))
        {
            foreach ($charges as $charge)
            {
                if ($charge['payment_method']['type'] == 'PaymentMethod::BankSlip')
                {
                    $order->addStatusHistoryComment(sprintf(
                        '<a target="_blank" href="%s">Clique aqui</a> para visualizar o boleto.',
                        $charge['print_url']
                    ))
                    ->setIsVisibleOnFront(true);
                    $order->save();
                }
            }
        }
        $this->logger->log(sprintf('Novo pedido gerado: %s.', $order->getId()));
        return true;
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
            }
            else {
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
            }
            else {
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