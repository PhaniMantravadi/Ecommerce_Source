<?php 
class Velanapps_Ecfplus_Block_Adminhtml_Manage_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
        $this->_objectId = 'id';
        $this->_blockGroup = 'ecfplus';
        $this->_controller = 'adminhtml_manage';
		$this->_removeButton('save');
		$this->_removeButton('reset');
    }
 
	public function getHeaderText()
    {
        if( Mage::registry('manage_data') && Mage::registry('manage_data')->getManageId() ) 
		{
            return Mage::helper('ecfplus/data')->__("Customer Detail");
        }
    }  
}