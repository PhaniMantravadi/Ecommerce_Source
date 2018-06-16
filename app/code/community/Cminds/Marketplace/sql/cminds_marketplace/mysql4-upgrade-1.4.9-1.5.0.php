<?php
$installer = $this;
$installer->startSetup();

$connection = $installer->getConnection();

if(!$connection->tableColumnExists($installer->getTable('marketplace/invoice'),'invoice_id')){
    $installer->run("alter ignore table {$installer->getTable('marketplace/invoice')} add column `invoice_id` int(11) DEFAULT 0  after `order_id`");
}