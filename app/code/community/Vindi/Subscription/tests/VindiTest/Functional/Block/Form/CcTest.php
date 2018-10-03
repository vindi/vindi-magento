<?php

namespace VindiTest\Functional\Block\Form;

use Magium\Magento\AbstractMagentoTestCase;
use Magium\Magento\Actions\Cart\AddItemToCart;
use Magium\Magento\Actions\Checkout\RegisterNewCustomerCheckout;
use Magium\Magento\Extractors\Checkout\OrderId;

class CcTest extends AbstractMagentoTestCase
{

    public function testBasicCheckout()
    {
        $this->commandOpen('https://vindi.local/teste-produto.html');
        $this->webdriver->manage()->timeouts()->implicitlyWait(5);
        $this->byXpath('//button[@title="Add to Cart"]')->click();
        $this->byXpath('//button[@title="Proceed to Checkout"]')->click();
        $this->setPaymentMethod('VindiCreditCard');
        $customerCheckout= $this->getAction(RegisterNewCustomerCheckout::ACTION);
        /* @var $customerCheckout \Magium\Magento\Actions\Checkout\RegisterNewCustomerCheckout */

        $customerCheckout->execute();

        $orderId = $this->getExtractor(OrderId::EXTRACTOR);
        $this->getLogger()->info(sprintf('Extracted %s as the order ID', $orderId->getOrderId()));
        /** @var $orderId OrderId */
        self::assertNotNull($orderId->getOrderId());
        self::assertGreaterThan(0, $orderId->getOrderId());
        self::assertNotEquals('comunidade@vindi.com.br', $this->getIdentity()->getEmailAddress());
    }


    public function checkoutWithSpecifiedEmailAddress()
    {
        $customer = $this->getIdentity();
        /* @var $customer \Magium\Magento\Identities\Customer */

        $address = hash_hmac('sha256', uniqid(), '') . '@vindi.com.br';
        $customer->setEmailAddress($address);
        $customer->setUniqueEmailAddressGenerated(true);

        $theme = $this->getTheme();
        $this->commandOpen($theme->getBaseUrl());
        $this->getLogger()->info('Opening page ' . $theme->getBaseUrl());
        $addToCart = $this->getAction(AddItemToCart::ACTION);
        /* @var $addToCart \Magium\Magento\Actions\Cart\AddItemToCart */

        $addToCart->addSimpleProductToCartFromCategoryPage();
        $this->setPaymentMethod('CashOnDelivery');
        $customerCheckout= $this->getAction(RegisterNewCustomerCheckout::ACTION);
        /* @var $customerCheckout \Magium\Magento\Actions\Checkout\RegisterNewCustomerCheckout */

        $customerCheckout->execute();

        $orderId = $this->getExtractor(OrderId::EXTRACTOR);
        $this->getLogger()->info(sprintf('Extracted %s as the order ID', $orderId->getOrderId()));
        /** @var $orderId OrderId */
        self::assertNotNull($orderId->getOrderId());
        self::assertGreaterThan(0, $orderId->getOrderId());
        self::assertEquals($address, $this->getIdentity()->getEmailAddress());
    }

}