<?php
class Velanapps_Ecfplus_Block_Adminhtml_Storelocator_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
               
        $this->_objectId = 'id';
        $this->_blockGroup = 'ecfplus';
        $this->_controller = 'adminhtml_storelocator';
		
 
        $this->_updateButton('save', 'label', Mage::helper('ecfplus')->__('Save StoreLocator'));
        $this->_updateButton('delete', 'label', Mage::helper('ecfplus')->__('Delete StoreLocator'));
    }
 
    public function getHeaderText()
    {
        if( Mage::registry('storelocator_data') && Mage::registry('storelocator_data')->getId() ) {
            return Mage::helper('ecfplus')->__("Edit StoreLocator '%s'", $this->htmlEscape(Mage::registry('storelocator_data')->getMapName()));
        } else {
            return Mage::helper('ecfplus')->__('Add StoreLocator');
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