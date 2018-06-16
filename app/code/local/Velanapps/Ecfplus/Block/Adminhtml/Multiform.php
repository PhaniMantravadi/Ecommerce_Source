<?php

class Velanapps_Ecfplus_Block_Adminhtml_Multiform extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_controller = 'adminhtml_multiform';
        $this->_blockGroup = 'ecfplus';
        $this->_headerText = Mage::helper('ecfplus')->__('Manage Multiform');
		$this->_addButtonLabel = Mage::helper('ecfplus')->__('Add New Form');
		
        parent::__construct();		
    }
}