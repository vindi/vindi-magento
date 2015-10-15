<?php

$setup = $this;
$connection = $setup->getConnection();
$setup->startSetup();

if (! $this->getAttribute(Mage_Catalog_Model_Product::ENTITY, 'vindi_product_id', 'attribute_id')) {
    $setup->addAttribute(
        Mage_Catalog_Model_Product::ENTITY,
        'vindi_product_id',
        [
            'type'                    => 'int',
            'input'                   => 'text',
            'backend'                 => '',
            'frontend'                => '',
            'label'                   => 'ID Vindi do produto',
            'class'                   => '',
            'global'                  => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
            'visible'                 => true,
            'required'                => true,
            'user_defined'            => false,
            'default'                 => '',
            'searchable'              => false,
            'filterable'              => false,
            'comparable'              => false,
            'visible_on_front'        => false,
            'unique'                  => false,
            'apply_to'                => 'subscription',
            'is_configurable'         => false,
            'used_in_product_listing' => false,
            'option'                  => [
                'values' => [],
            ],
        ]
    );

    $attributeId = $setup->getAttributeId(
        Mage_Catalog_Model_Product::ENTITY,
        'vindi_product_id'
    );

    $defaultSetId = $setup->getAttributeSetId(Mage_Catalog_Model_Product::ENTITY, 'default');

    $setup->addAttributeGroup(
        Mage_Catalog_Model_Product::ENTITY,
        $defaultSetId,
        'Vindi'
    );

    //find out the id of the new group
    $groupId = $setup->getAttributeGroup(
        Mage_Catalog_Model_Product::ENTITY,
        $defaultSetId,
        'Vindi',
        'attribute_group_id'
    );

    //assign the attribute to the group and set
    if ($attributeId > 0) {
        $setup->addAttributeToSet(
            Mage_Catalog_Model_Product::ENTITY,
            $defaultSetId,
            $groupId,
            $attributeId
        );
    }
}
$setup->endSetup();
