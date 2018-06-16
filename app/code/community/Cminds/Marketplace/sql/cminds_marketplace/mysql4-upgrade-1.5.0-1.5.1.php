<?php
$installer = $this;
$installer->startSetup();

$entity = $this->getEntityTypeId('customer');
$this->removeAttribute($entity, 'supplier_invoice_auth_label');
$this->removeAttribute($entity, 'supplier_invoice_auth_sign');
$installer->endSetup();
$installer->startSetup();
$this->addAttribute($entity, 'supplier_invoice_auth_label', array(
    'type' => 'text',
    'label' => 'Supplier Invoice Auth Sign Label',
    'input' => 'text',
    'visible' => TRUE,
    'required' => FALSE,
    'default_value' => '',
    'visible_on_front' => 1,
    'global' => 1,
    'source' =>   NULL,
));

$this->addAttribute($entity, 'supplier_invoice_auth_sign', array(
    'type' => 'text',
    'label' => 'Supplier Invoice Auth Sign',
    'input' => 'text',
    'visible' => TRUE,
    'required' => FALSE,
    'visible_on_front' => 1,
    'global' => 1,
    'default_value' => '',
));

$installer->endSetup();
