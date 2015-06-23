<?php

class Vindi_Subscription_Model_Product_Attribute_Plan extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
    public function getAllOptions()
    {
        /** @todo grab informations from API */
        $this->_options = array(
            array(
                'value' => '',
                'label' => Mage::helper('catalog')->__('-- Please Select --'),
            ),
            array(
                'value' => '1',
                'label' => 'Teste',
            ),
        );

        return $this->_options;
    }
}
