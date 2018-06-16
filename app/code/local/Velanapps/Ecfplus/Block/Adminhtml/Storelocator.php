<?php

class Velanapps_Ecfplus_Block_Adminhtml_Storelocator extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_controller = 'adminhtml_storelocator';
        $this->_blockGroup = 'ecfplus';
        $this->_headerText = Mage::helper('ecfplus')->__('Manage Storelocator');
		$this->_addButtonLabel = Mage::helper('ecfplus')->__('Add New Locators');
		
        parent::__construct();		
    }
}