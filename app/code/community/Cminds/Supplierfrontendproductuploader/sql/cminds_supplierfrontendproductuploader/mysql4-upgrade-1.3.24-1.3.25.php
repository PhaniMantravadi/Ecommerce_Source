<?php
$installer = $this;
$installer->startSetup();

$db = Mage::getSingleton('core/resource')->getConnection('core_write');
$table_prefix = Mage::getConfig()->getTablePrefix();

$entity = $this->getEntityTypeId('catalog_product');
$this->removeAttribute($entity, 'seller_tax_type_1');
$this->removeAttribute($entity, 'seller_tax_rate_1');
$installer->endSetup();
