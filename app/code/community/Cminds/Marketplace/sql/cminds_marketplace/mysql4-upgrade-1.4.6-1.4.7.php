<?php
$installer = $this;
$installer->startSetup();

$entity = $this->getEntityTypeId('customer');
$this->removeAttribute($entity, 'supplier_name');
$this->addAttribute($entity, 'supplier_name', array(
    'type' => 'text',
    'label' => 'Supplier Name',
    'input' => 'text',
    'visible' => TRUE,
    'required' => FALSE,
    'default_value' => '',
    'adminhtml_only' => '1'
));

$this->removeAttribute($entity, 'supplier_description');
$this->addAttribute($entity, 'supplier_description', array(
    'type' => 'text',
    'label' => 'Supplier Description',
    'input' => 'textarea',
    'visible' => TRUE,
    'required' => FALSE,
    'default_value' => '',
    'adminhtml_only' => '1'
));

$this->removeAttribute($entity, 'supplier_profile_visible');
$this->addAttribute($entity, 'supplier_profile_visible', array(
    'type' => 'int',
    'label' => 'Supplier Profile Visible',
    'input' => 'select',
    'source_model' => 'eav/entity_attribute_source_boolean',
    'visible' => TRUE,
    'required' => FALSE,
    'default_value' => 0,
    'adminhtml_only' => '1'
));

$this->removeAttribute($entity, 'supplier_profile_approved');
$this->addAttribute($entity, 'supplier_profile_approved', array(
    'type' => 'int',
    'label' => 'Profile approved',
    'input' => 'select',
    'source_model' => 'eav/entity_attribute_source_boolean',
    'visible' => TRUE,
    'required' => FALSE,
    'default_value' => 0,
    'adminhtml_only' => '1'
));

$this->removeAttribute($entity, 'supplier_remark');
$this->addAttribute($entity, 'supplier_remark', array(
    'type' => 'text',
    'label' => 'Comment',
    'input' => 'textarea',
    'visible' => TRUE,
    'required' => FALSE,
    'default_value' => '',
    'adminhtml_only' => '1'
));

$this->removeAttribute($entity, 'rejected_notfication_seen');
$this->addAttribute($entity, 'rejected_notfication_seen', array(
    'type' => 'int',
    'input' => 'text',
    'visible' => TRUE,
    'required' => FALSE,
    'default_value' => 1,
    'adminhtml_only' => '1'
));

$installer->endSetup();
