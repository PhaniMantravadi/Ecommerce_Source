<?php
class Velanapps_Ecfplus_Block_Adminhtml_Manage extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {	  
        $this->_controller = 'adminhtml_manage';
        $this->_blockGroup = 'ecfplus';
        $this->_headerText = Mage::helper('ecfplus')->__('Manage Reports');
        parent::__construct();
		$this->_removeButton('add');		
		
    }
}