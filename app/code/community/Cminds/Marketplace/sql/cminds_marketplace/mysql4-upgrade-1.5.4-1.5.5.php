<?php
/**
 * @category    Fqs
 * @package     Fqs_Vendor
 * Created a new attribute.
 
*/
$installer = $this;
$installer->startSetup();

$installer->run("ALTER TABLE sales_flat_order_item ADD seller_id int(11);");
$installer->run("ALTER TABLE sales_flat_quote_item ADD seller_id int(11);");


$installer->endSetup();
