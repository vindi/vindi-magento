<?php

/** @var $installer Mage_Catalog_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();

$installer->addAttribute(
    Mage_Catalog_Model_Product::ENTITY,
    'vindi_subscription_plan',
    [
        'type'                    => 'int',
        'input'                   => 'select',
        'backend'                 => '',
        'frontend'                => '',
        'label'                   => 'Plano da Vindi',
        'class'                   => '',
        'source'                  => 'vindi_subscription/product_attribute_plan',
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

$attributeId = $installer->getAttributeId(
    'catalog_product',
    'vindi_subscription_plan'
);

$defaultSetId = $installer->getAttributeSetId('catalog_product', 'default');

$installer->addAttributeGroup(
    'catalog_product',
    $defaultSetId,
    'Vindi'
);

//find out the id of the new group
$groupId = $installer->getAttributeGroup(
    'catalog_product',
    $defaultSetId,
    'Vindi',
    'attribute_group_id'
);

//assign the attribute to the group and set
if ($attributeId > 0) {
    $installer->addAttributeToSet(
        'catalog_product',
        $defaultSetId,
        $groupId,
        $attributeId
    );
}

$attributes = [
    'price',
    'special_price',
    'special_from_date',
    'special_to_date',
    'minimal_price',
    'tax_class_id',
];

foreach ($attributes as $attributeCode) {
    $applyTo = explode(
        ',',
        $installer->getAttribute(
            Mage_Catalog_Model_Product::ENTITY,
            $attributeCode,
            'apply_to'
        )
    );

    if (! in_array('subscription', $applyTo)) {
        $applyTo[] = 'subscription';
        $installer->updateAttribute(
            Mage_Catalog_Model_Product::ENTITY,
            $attributeCode,
            'apply_to',
            join(',', $applyTo)
        );
    }
}

$installer->endSetup();