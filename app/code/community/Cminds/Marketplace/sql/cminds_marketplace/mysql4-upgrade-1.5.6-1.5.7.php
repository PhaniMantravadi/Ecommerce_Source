<?php
/**
 * @category    Fqs
 * @package     Fqs_Vendor
 * Created a new attribute.
 
*/
$installer = $this;
$installer->startSetup();

$installer->run("ALTER TABLE sales_flat_order_item DROP seller_tax_type_a;");
$installer->run("ALTER TABLE sales_flat_quote_item DROP seller_tax_type_a;");

$installer->run("ALTER TABLE sales_flat_order_item DROP seller_tax_rate_a;");
$installer->run("ALTER TABLE sales_flat_quote_item DROP seller_tax_rate_a;");

$installer->endSetup();
