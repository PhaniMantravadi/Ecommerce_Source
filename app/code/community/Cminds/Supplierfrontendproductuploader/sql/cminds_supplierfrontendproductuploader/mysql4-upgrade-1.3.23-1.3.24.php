<?php
$installer = $this;
$installer->startSetup();

$db = Mage::getSingleton('core/resource')->getConnection('core_write');
$table_prefix = Mage::getConfig()->getTablePrefix();

$entity = $this->getEntityTypeId('catalog_product');
$this->removeAttribute($entity, 'seller_tax_type_1');
$this->removeAttribute($entity, 'seller_tax_rate_1');
$installer->endSetup();
$installer->startSetup();
$this->addAttribute($entity, 'seller_tax_type_1', array(
    'type' => 'text',
    'backend' => '',
    'frontend' => '',
    'input' => 'text',
    'label' => 'Seller Tax Type 1',
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

$this->addAttribute($entity, 'seller_tax_rate_1', array(
    'type' => 'text',
    'backend' => '',
    'frontend' => '',
    'input' => 'text',
    'label' => 'Seller Tax Rate 1',
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
