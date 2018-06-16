<?php
class Velanapps_Ecfplus_Block_Adminhtml_Multiform_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
               
        $this->_objectId = 'id';
        $this->_blockGroup = 'ecfplus';
        $this->_controller = 'adminhtml_multiform';
		
 
        $this->_updateButton('save', 'label', Mage::helper('ecfplus')->__('Save Form'));
        $this->_updateButton('delete', 'label', Mage::helper('ecfplus')->__('Delete Form'));
    }
 
    public function getHeaderText()
    {
        if( Mage::registry('multiform_data') && Mage::registry('multiform_data')->getId() ) {
            return Mage::helper('ecfplus')->__("Edit Form '%s'", $this->htmlEscape(Mage::registry('multiform_data')->getName()));
        } else {
            return Mage::helper('ecfplus')->__('Add Form');
        }
    }
	
	protected function _prepareLayout()
	{	
		parent::_prepareLayout();
		 if (Mage::getSingleton('cms/wysiwyg_config')->isEnabled())
		{
			$this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
		} 
	}
}