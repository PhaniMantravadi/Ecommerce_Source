<?php
$installer = $this;
$installer->startSetup();

$entity = $this->getEntityTypeId('customer');
$this->addAttribute($entity, 'vat', array(
    'type' => 'text',
    'label' => 'VAT',
    'input' => 'text',
    'visible' => TRUE,
    'required' => FALSE,
    'default_value' => '',
));

Mage::getSingleton('eav/config')
    ->getAttribute('customer', 'vat')
    ->setData('used_in_forms', array('customer_account_create','customer_account_edit'))
    ->save();

$this->addAttribute($entity, 'pan', array(
    'type' => 'text',
    'label' => 'PAN',
    'input' => 'text',
    'visible' => TRUE,
    'required' => FALSE,
    'default_value' => '',
));

Mage::getSingleton('eav/config')
    ->getAttribute('customer', 'pan')
    ->setData('used_in_forms', array('customer_account_create','customer_account_edit'))
    ->save();

$this->addAttribute($entity, 'cst', array(
    'type' => 'text',
    'label' => 'CST',
    'input' => 'text',
    'visible' => TRUE,
    'required' => FALSE,
    'default_value' => '',
));

Mage::getSingleton('eav/config')
    ->getAttribute('customer', 'cst')
    ->setData('used_in_forms', array('customer_account_create','customer_account_edit'))
    ->save();

$this->addAttribute($entity, 'bank_ac', array(
    'type' => 'text',
    'label' => 'Bank A/C',
    'input' => 'text',
    'visible' => TRUE,
    'required' => FALSE,
    'default_value' => '',
));

Mage::getSingleton('eav/config')
    ->getAttribute('customer', 'bank_ac')
    ->setData('used_in_forms', array('customer_account_create','customer_account_edit'))
    ->save();

$this->addAttribute($entity, 'ifsc', array(
    'type' => 'text',
    'label' => 'IFSC code',
    'input' => 'text',
    'visible' => TRUE,
    'required' => FALSE,
    'default_value' => '',
));

Mage::getSingleton('eav/config')
    ->getAttribute('customer', 'ifsc')
    ->setData('used_in_forms', array('customer_account_create','customer_account_edit'))
    ->save();

$this->addAttribute($entity, 'supplier_document', array(
    'type' => 'text',
    'label' => 'Supplier Document File',
    'input' => 'text',
    'visible' => TRUE,
    'required' => FALSE,
    'default_value' => '',
    'adminhtml_only' => '1'
));

Mage::getSingleton('eav/config')
    ->getAttribute('customer', 'ifsc')
    ->setData('used_in_forms', array('customer_account_create','customer_account_edit'))
    ->save();

Mage::helper('marketplace')->setSupplierDataInstalled(true);

$installer->endSetup();
