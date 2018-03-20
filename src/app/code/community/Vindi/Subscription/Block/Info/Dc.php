<?php

class Vindi_Subscription_Block_Info_Dc extends Mage_Payment_Block_Info_Cc
{
    /**
     * Initialize block
     */
    public function _construct()
    {
        parent::_construct();
        $this->setTemplate('vindi_subscription/payment/info/dc.phtml');
    }


    /**
     * Prepare debit card related payment info
     *
     * @param Varien_Object|array $transport
     * @return Varien_Object
     */
    protected function _prepareSpecificInformation($transport = null)
    {
        if (null !== $this->_paymentSpecificInformation) {
            return $this->_paymentSpecificInformation;
        }

        $transport = Mage_Payment_Block_Info::_prepareSpecificInformation($transport);

        $data = array();

        if ($dcType = $this->getCcTypeName()) {
            $data[Mage::helper('payment')->__('Debit Card Type')] = $dcType;
        }
        if ($this->getInfo()->getCcLast4()) {
            $data[Mage::helper('payment')->__('Debit Card Number')] = sprintf('xxxx-%s', $this->getInfo()->getCcLast4());
        }
        if (!$this->getIsSecureMode()) {
            if ($dcSsIssue = $this->getInfo()->getCcSsIssue()) {
                $data[Mage::helper('payment')->__('Switch/Solo/Maestro Issue Number')] = $dcSsIssue;
            }
            $year = $this->getInfo()->getCcSsStartYear();
            $month = $this->getInfo()->getCcSsStartMonth();
            if ($year && $month) {
                $data[Mage::helper('payment')->__('Switch/Solo/Maestro Start Date')] =  $this->_formatCardDate($year, $month);
            }
        }
        return $transport->setData(array_merge($data, $transport->getData()));
    }
}