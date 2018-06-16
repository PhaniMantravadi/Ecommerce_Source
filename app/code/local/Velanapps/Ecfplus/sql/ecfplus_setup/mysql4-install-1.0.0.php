<?php
 
$installer = $this;
 
$installer->startSetup();
 
$multiform = $installer->getConnection()
    ->newTable($installer->getTable('ecfplus/multiform'))
    ->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, 11, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Form Id')
    ->addColumn('name', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
        'nullable'  => false,
        ), 'Name')
	->addColumn('status', Varien_Db_Ddl_Table::TYPE_BOOLEAN, null, array(
        'nullable'  => false,
        ), 'Status')
	->addColumn('enable_email', Varien_Db_Ddl_Table::TYPE_BOOLEAN, null, array(
        'nullable'  => false,
        ), 'Enable Email as Custom Options Field for Customer')
    ->addColumn('message_field', Varien_Db_Ddl_Table::TYPE_TEXT, null, array(
        'nullable'  => false,
        ), 'Thankyou Message Description')
	->addColumn('recaptcha', Varien_Db_Ddl_Table::TYPE_BOOLEAN, null, array(
        'nullable'  => false,
        ), 'Recaptcha in Form');
$installer->getConnection()->createTable($multiform);

$items = $installer->getConnection()
    ->newTable($installer->getTable('ecfplus/items'))
    ->addColumn('item_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 11, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Item Id')
    ->addColumn('group', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
        'nullable'  => false,
        ), 'Group')
	->addColumn('post_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 11, array(        
        'nullable'  => false,
        ), 'Post Id')
	->addColumn('title', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
        'nullable'  => false,
        ), 'Title')
	->addColumn('type', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
        'nullable'  => false,
        ), 'Type')
	->addColumn('is_require', Varien_Db_Ddl_Table::TYPE_INTEGER, 11, array(        
        'nullable'  => false,
        ), 'Required Field')
	->addColumn('sort_order', Varien_Db_Ddl_Table::TYPE_INTEGER, 11, array(        
        'nullable'  => false,
        ), 'Sort Order')
	->addColumn('form_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 11, array(        
        'nullable'  => false,
        ), 'Form Id')
	->addColumn('is_mail', Varien_Db_Ddl_Table::TYPE_INTEGER, 4, array(        
        'nullable'  => false,
        ), 'Is Mail');
	
$installer->getConnection()->createTable($items);

$itemoptions = $installer->getConnection()
    ->newTable($installer->getTable('ecfplus/itemoptions'))
    ->addColumn('option_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 11, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Option Id') 
	->addColumn('item_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 11, array(        
        'nullable'  => false,
        ), 'Item Id')
	->addColumn('title', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
        'nullable'  => false,
        ), 'Title')	
	->addColumn('sort_order', Varien_Db_Ddl_Table::TYPE_INTEGER, 11, array(        
        'nullable'  => false,
        ), 'Sort Order')
	->addColumn('validation', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
        'nullable'  => false,
        ), 'validation for text field')
	->addColumn('max_characters', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
        'nullable'  => false,
        ), 'Maximum Characters for text field')
	->addColumn('form_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 11, array(        
        'nullable'  => false,
        ), 'Form Id');
	
$installer->getConnection()->createTable($itemoptions); 
 
$manage = $installer->getConnection()
    ->newTable($installer->getTable('ecfplus/manage'))
    ->addColumn('manage_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 11, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Manage Fields Id')    
	->addColumn('store_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 11, array(        
        'nullable'  => false,
        ), 'Store Id')
	->addColumn('subfields', Varien_Db_Ddl_Table::TYPE_TEXT, null, array(
        'nullable'  => false,
        ), 'Form Field Values')
	->addColumn('form_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 11, array(        
        'nullable'  => false,
        ), 'Form Id');
	
$installer->getConnection()->createTable($manage);

$storelocator = $installer->getConnection()
    ->newTable($installer->getTable('ecfplus/storelocator'))
    ->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, 11, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Map Id')    
	->addColumn('map_name', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(        
        'nullable'  => false,
        ), 'Map Name')
	->addColumn('background_image', Varien_Db_Ddl_Table::TYPE_TEXT, null, array(
        'nullable'  => false,
        ), 'Background Image')
	->addColumn('status', Varien_Db_Ddl_Table::TYPE_BOOLEAN, null, array(
        'nullable'  => false,
        ), 'Status');
	
$installer->getConnection()->createTable($storelocator);

$storelocator_location = $installer->getConnection()
    ->newTable($installer->getTable('ecfplus/storelocator_locations'))
    ->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, 11, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Id')    
	->addColumn('parent_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 11, array(       
        'nullable'  => false,
        ), 'Parent_id')
	->addColumn('latitude', Varien_Db_Ddl_Table::TYPE_TEXT, null, array(       
        'nullable'  => false,
        ), 'Latitude')
	->addColumn('longitude', Varien_Db_Ddl_Table::TYPE_TEXT, null, array(
        'nullable'  => false,
        ), 'Longitude')
	->addColumn('address', Varien_Db_Ddl_Table::TYPE_TEXT, null, array(
        'nullable'  => false,
        ), 'Address');
	
$installer->getConnection()->createTable($storelocator_location);

$installer->getConnection()->addConstraint(
							'FK_STORELOCATOR_LOCATIONS',
							$installer->getTable('ecfplus/storelocator_locations'), 
							'parent_id',
							$installer->getTable('ecfplus/storelocator'), 
							'id',
							'cascade', 
							'cascade');
								

$installer->getConnection()->addConstraint(
								'FK_ITEMS_MULTIFORM',
								$installer->getTable('ecfplus/items'), 
								'form_id',
								$installer->getTable('ecfplus/multiform'), 
								'id',
								'cascade', 
								'cascade');
								
$installer->getConnection()->addConstraint(
								'FK_ITEMOPTIONS_MULTIFORM',
								$installer->getTable('ecfplus/itemoptions'), 
								'form_id',
								$installer->getTable('ecfplus/multiform'), 
								'id',
								'cascade', 
								'cascade');		
								

								
$installer->getConnection()->addConstraint(
								'FK_MANAGE_MULTIFORM',
								$installer->getTable('ecfplus/manage'), 
								'form_id',
								$installer->getTable('ecfplus/multiform'), 
								'id',
								'cascade', 
								'cascade'); 
								
$installer->getConnection()->addConstraint(
								'FK_ITEMOPTIONS_ITEMS',
								$installer->getTable('ecfplus/itemoptions'), 
								'item_id',
								$installer->getTable('ecfplus/items'), 
								'item_id',
								'cascade', 
								'cascade');
								
$installer->endSetup();