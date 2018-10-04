<?php

namespace VindiTest\Functional\Block\Form;

use Vindi\AbstractMagentoTestCase;
use Vindi\Actions\Checkout\RegisterNewCustomerCheckout;
use Vindi\Extractors\Checkout\OrderId;

class CcTest extends AbstractMagentoTestCase
{

    public function testBasicCheckout()
    {
        $this->commandOpen('https://vindi.local/vindi-product.html');
        $this->byXpath('//button[@title="Add to Cart"]')->click();
        $this->byXpath('//button[@title="Proceed to Checkout"]')->click();
        $this->setPaymentMethod('VindiCreditCard');
        $customerCheckout= $this->getAction(RegisterNewCustomerCheckout::ACTION);
        /* @var $customerCheckout \Vindi\Actions\Checkout\RegisterNewCustomerCheckout */

        $customerCheckout->execute();

        $orderId = $this->getExtractor(OrderId::EXTRACTOR);
        $this->getLogger()->info(sprintf('Extracted %s as the order ID', $orderId->getOrderId()));
        /** @var $orderId OrderId */
        self::assertNotNull($orderId->getOrderId());
        self::assertGreaterThan(0, $orderId->getOrderId());
        self::assertNotEquals('comunidade@vindi.com.br', $this->getIdentity()->getEmailAddress());
    }


    public function testCheckoutWithSpecifiedEmailAddress()
    {
        $customer = $this->getIdentity();
        /* @var $customer \Vindi\Identities\Customer */

        $address = hash_hmac('sha256', uniqid(), '') . '@vindi.com.br';
        $customer->setEmailAddress($address);
        $customer->setUniqueEmailAddressGenerated(true);

        $this->commandOpen('https://vindi.local/vindi-product.html');
        $this->webdriver->manage()->timeouts()->implicitlyWait(5);
        $this->byXpath('//button[@title="Add to Cart"]')->click();
        $this->byXpath('//button[@title="Proceed to Checkout"]')->click();
        $this->setPaymentMethod('VindiCreditCard');
        $customerCheckout= $this->getAction(RegisterNewCustomerCheckout::ACTION);
        /* @var $customerCheckout \Vindi\Actions\Checkout\RegisterNewCustomerCheckout */

        $customerCheckout->execute();

        $orderId = $this->getExtractor(OrderId::EXTRACTOR);
        $this->getLogger()->info(sprintf('Extracted %s as the order ID', $orderId->getOrderId()));
        /** @var $orderId OrderId */
        self::assertNotNull($orderId->getOrderId());
        self::assertGreaterThan(0, $orderId->getOrderId());
        self::assertEquals($address, $this->getIdentity()->getEmailAddress());
    }

}