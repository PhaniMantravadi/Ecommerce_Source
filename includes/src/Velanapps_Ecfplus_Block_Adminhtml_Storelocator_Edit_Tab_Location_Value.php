<?php
class Velanapps_Ecfplus_Block_Adminhtml_Storelocator_Edit_Tab_Location_Value
   extends Mage_Adminhtml_Block_Widget implements Varien_Data_Form_Element_Renderer_Interface
{
    /**
     * Initialize block
     */
    public function __construct()
    {
	   $this->setTemplate('ecfplus/locator.phtml');
    }
	
	public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $this->setElement($element);
        return $this->toHtml();
    }
	
}