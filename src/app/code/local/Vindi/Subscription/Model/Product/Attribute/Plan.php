<?php

class Vindi_Subscription_Model_Product_Attribute_Plan extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
    public function getAllOptions()
    {
        $this->_options = [
            [
                'value' => '',
                'label' => Mage::helper('catalog')->__('-- Please Select --'),
            ],
        ];

        /** @var Vindi_Subscription_Helper_API $api */
        $api = Mage::helper('vindi_subscription/api');

        foreach ($api->getPlans() as $id => $name) {
            $this->_options[] = [
                'value' => $id,
                'label' => $name,
            ];
        }

        return $this->_options;
    }
}
