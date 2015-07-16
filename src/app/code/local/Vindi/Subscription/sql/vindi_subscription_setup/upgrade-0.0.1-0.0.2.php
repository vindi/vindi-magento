<?php

$setup = new Mage_Sales_Model_Resource_Setup('core_setup');

$setup->addAttribute(
    Mage_Sales_Model_Order::ENTITY,
    'vindi_subscription_id',
    [
        'type'             => 'varchar',
        'input'            => 'text',
        'backend'          => '',
        'frontend'         => '',
        'label'            => 'Id da Assinatura Vindi',
        'class'            => '',
        'global'           => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
        'visible'          => false,
        'required'         => false,
        'user_defined'     => false,
        'default'          => '',
        'searchable'       => false,
        'filterable'       => false,
        'comparable'       => false,
        'visible_on_front' => false,
        'unique'           => false,
    ]
);

$setup->addAttribute(
    Mage_Sales_Model_Order::ENTITY,
    'vindi_subscription_period',
    [
        'type'             => 'varchar',
        'input'            => 'text',
        'backend'          => '',
        'frontend'         => '',
        'label'            => 'Período da Assinatura Vindi',
        'class'            => '',
        'global'           => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
        'visible'          => false,
        'required'         => false,
        'user_defined'     => false,
        'default'          => '',
        'searchable'       => false,
        'filterable'       => false,
        'comparable'       => false,
        'visible_on_front' => false,
        'unique'           => false,
    ]
);

$setup->endSetup();
