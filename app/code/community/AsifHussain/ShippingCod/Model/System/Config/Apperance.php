<?php
 
class AsifHussain_ShippingCod_Model_System_Config_Apperance
{
    public function toOptionArray()
    {
        return array(
            array(
                'value' => 'text',
                'label' => 'Text',
            ),
            array(
                'value' => 'icon',
                'label' => 'Icons',
            ),
        );
    }
}