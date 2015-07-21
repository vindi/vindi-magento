<?php

/** @var $installer Mage_Catalog_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();

if (! $this->getAttribute(Mage_Catalog_Model_Product::ENTITY, 'vindi_subscription_plan', 'attribute_id')) {
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
        Mage_Catalog_Model_Product::ENTITY,
        'vindi_subscription_plan'
    );

    $defaultSetId = $installer->getAttributeSetId(Mage_Catalog_Model_Product::ENTITY, 'default');

    $installer->addAttributeGroup(
        Mage_Catalog_Model_Product::ENTITY,
        $defaultSetId,
        'Vindi'
    );

    //find out the id of the new group
    $groupId = $installer->getAttributeGroup(
        Mage_Catalog_Model_Product::ENTITY,
        $defaultSetId,
        'Vindi',
        'attribute_group_id'
    );

    //assign the attribute to the group and set
    if ($attributeId > 0) {
        $installer->addAttributeToSet(
            Mage_Catalog_Model_Product::ENTITY,
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
        'cost',
        'tier_price',
        'weight',
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
}

if (! $this->getAttribute('customer', 'vindi_user_code', 'attribute_id')) {
    $installer->addAttribute('customer', 'vindi_user_code', [
        'input'         => 'text',
        'type'          => 'varchar',
        'is_visible'    => false,
        'is_unique'     => true,
        'is_required'   => false,
        'sort_order'    => 500,
        'label'         => 'Código do Cliente na Vindi',
        'used_in_forms' => ['adminhtml_customer'],
        'backend_type'  => 'varchar',
        'default_value' => '',
    ]);
}

$installer->endSetup();