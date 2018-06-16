<?php
class  Velanapps_Ecfplus_Model_Dropdowns_Pallet
{
	public function toOptionArray()
    {
        return array(
            array('value' => 'color1', 'label' => Mage::helper('ecfplus')->__('color1')),
            array('value' => 'color2', 'label' => Mage::helper('ecfplus')->__('color2')),         
        );
    }
 

   
}