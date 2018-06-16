<?php 
class Uscoding_Xautosku_Model_System_Config_Skutype
{
    public function toOptionArray()
    {
        return array(        	
            array('value'=>2, 'label'=>Mage::helper('xautosku')->__('Random Number')),
            array('value'=>3, 'label'=>Mage::helper('xautosku')->__('Category\'s Prefix And Product Name ' )),
       );
    }
    
}
