<?php
/**
 * @category    Fqs
 * @package     Fqs_Vendor
 * Created a new attribute.
 
*/
$installer = $this;
$installer->startSetup();

$installer->run("ALTER TABLE sales_flat_order_item ADD seller_tax_type varchar(255);");
$installer->run("ALTER TABLE sales_flat_quote_item ADD seller_tax_type varchar(255);");

$installer->run("ALTER TABLE sales_flat_order_item ADD seller_tax_rate decimal(12,4);");
$installer->run("ALTER TABLE sales_flat_quote_item ADD seller_tax_rate decimal(12,4);");

$installer->endSetup();
