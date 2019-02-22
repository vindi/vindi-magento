<?php

class Vindi_Subscription_Model_PaymentMethod extends Mage_Payment_Model_Method_Abstract
{
    use Vindi_Subscription_Trait_PaymentProcessor;
    
    /**
     * Assign data to info model instance
     *
     * @param   mixed $data
     *
     * @return  Mage_Payment_Model_Method_Abstract
     */
    public function assignData($data)
    {
        if (! ($data instanceof Varien_Object)) {
            $data = new Varien_Object($data);
        }

        $info = $this->getInfoInstance();
        $quote = $info->getQuote();

        if ('saved' === $data->getDcChoice()) {
            $info->setAdditionalInformation('PaymentMethod', $this->_code)
                ->setAdditionalInformation($this->save_method, true);

            return $this;
        }

        $info->setCcType($data->getCcType())
            ->setCcTypeName($data->getCcTypeName())
            ->setCcOwner($data->getCcOwner())
            ->setCcLast4(substr($data->getCcNumber(), -4))
            ->setCcNumber($data->getCcNumber())
            ->setCcCid($data->getCcCid())
            ->setCcExpMonth($data->getCcExpMonth())
            ->setCcExpYear($data->getCcExpYear())
            ->setCcSsIssue($data->getCcSsIssue())
            ->setCcSsStartMonth($data->getCcSsStartMonth())
            ->setCcSsStartYear($data->getCcSsStartYear())
            ->setAdditionalInformation('PaymentMethod', $this->_code)
            ->setAdditionalInformation($this->save_method, false);

        return $this;
    }
}