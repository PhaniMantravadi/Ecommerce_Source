<?php
$installer = $this;
$installer->startSetup();

$entity = $this->getEntityTypeId('customer');
$this->removeAttribute($entity, 'supplier_invoice_logo');
$installer->endSetup();
$installer->startSetup();
$this->addAttribute($entity, 'supplier_invoice_logo', array(
    'type' => 'text',
    'label' => 'Supplier Invoice Logo',
    'input' => 'text',
    'visible' => TRUE,
    'required' => FALSE,
    'visible_on_front' => 1,
    'global' => 1,
    'default_value' => '',
));

$installer->endSetup();