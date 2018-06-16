<?php
$installer = $this;
$installer->startSetup();
$query = "
CREATE TABLE IF NOT EXISTS {$this->getTable('marketplace_supplier_product_files')} (
  `id` int(11) unsigned NOT NULL auto_increment,
  `supplier_id` smallint(6) NOT NULL default '0',
  `attribute_set` smallint(6) NOT NULL default '0',
  `file_name` text NOT NULL,
  `file_type` int(2) NOT NULL default '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `status` int(11) NOT NULL default '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
";
$installer->run($query);

$installer->endSetup(); 
