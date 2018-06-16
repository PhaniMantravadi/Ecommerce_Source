<?php

$installer = $this;
$installer->startSetup();

$installer->setCustomerAttributes(
    array(
        'shop_name' => array(
            'type' => 'text',
            'visible' => false,
            'required' => false,
            'user_defined' => false
        ),
        'seller_category' => array(
            'type' => 'text',
            'visible' => false,
            'required' => false,
            'user_defined' => false
        ),
        'company_name' => array(
            'type' => 'text',
            'visible' => false,
            'required' => false,
            'user_defined' => false
        ),
        'about_shop' => array(
            'type' => 'text',
            'visible' => false,
            'required' => false,
            'user_defined' => false
        ),
        'bank_account' => array(
            'type' => 'text',
            'visible' => false,
            'required' => false,
            'user_defined' => false
        ),
        'bank_name' => array(
            'type' => 'text',
            'visible' => false,
            'required' => false,
            'user_defined' => false
        ),
        'branch_address' => array(
            'type' => 'text',
            'visible' => false,
            'required' => false,
            'user_defined' => false
        ),
        'ifsc_code' => array(
            'type' => 'text',
            'visible' => false,
            'required' => false,
            'user_defined' => false
        ),
        'vat' => array(
            'type' => 'text',
            'visible' => false,
            'required' => false,
            'user_defined' => false
        ),
        'pan' => array(
            'type' => 'text',
            'visible' => false,
            'required' => false,
            'user_defined' => false
        ),
        'cst' => array(
            'type' => 'text',
            'visible' => false,
            'required' => false,
            'user_defined' => false
        ),
        'pan_document' => array(
            'type' => 'text',
            'visible' => false,
            'required' => false,
            'user_defined' => false
        ),
        'vat_document' => array(
            'type' => 'text',
            'visible' => false,
            'required' => false,
            'user_defined' => false
        ),
        'tin_document' => array(
            'type' => 'text',
            'visible' => false,
            'required' => false,
            'user_defined' => false
        ),
        'bank_can_chq' => array(
            'type' => 'text',
            'visible' => false,
            'required' => false,
            'user_defined' => false
        ),
        'other_document' => array(
            'type' => 'text',
            'visible' => false,
            'required' => false,
            'user_defined' => false
        ),
        'other_document_2' => array(
            'type' => 'text',
            'visible' => false,
            'required' => false,
            'user_defined' => false
        )
    )
);

$installer->installCustomerAttributes();
$installer->endSetup();