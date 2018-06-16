<?php

$installer = $this;
$installer->startSetup();

$installer->setCustomerAttributes(
    array(
        'mobile_number' => array(
            'type' => 'text',
            'visible' => false,
            'required' => false,
            'user_defined' => false
        )
    )
);

$installer->installCustomerAttributes();
$installer->endSetup();