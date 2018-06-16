<?php
$installer = $this;
$installer->startSetup();

$db = Mage::getSingleton('core/resource')->getConnection('core_write');
$table_prefix = Mage::getConfig()->getTablePrefix();

$entity = $this->getEntityTypeId('catalog_product');
$this->removeAttribute($entity, 'seller_sku');
$this->removeAttribute($entity, 'seller_cst');
$this->removeAttribute($entity, 'cst');
$installer->endSetup();
$installer->startSetup();
$this->addAttribute($entity, 'seller_sku', array(
    'type' => 'text',
    'backend' => '',
    'frontend' => '',
    'input' => 'text',
    'label' => 'Seller SKU',
    'visible' => true,
    'required' => false,
    'user_defined' => false,
    'default' => 0,
    'searchable' => false,
    'filterable' => false,
    'comparable' => false,
    'visible_on_front' => true,
    'visible_in_advanced_search' => true,
    'available_for_supplier' => true
));

$this->addAttribute($entity, 'seller_cst', array(
    'type' => 'text',
    'backend' => '',
    'frontend' => '',
    'input' => 'text',
    'label' => 'Seller CST',
    'visible' => true,
    'required' => false,
    'user_defined' => false,
    'default' => 0,
    'searchable' => false,
    'filterable' => false,
    'comparable' => false,
    'visible_on_front' => true,
    'visible_in_advanced_search' => true,
    'available_for_supplier' => true
));


$installer->endSetup();
