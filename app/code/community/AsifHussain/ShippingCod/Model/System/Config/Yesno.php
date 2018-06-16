<?php
 
class AsifHussain_ShippingCod_Model_System_Config_Yesno
{
    public function toOptionArray()
    {
        return array(
            array(
                'value' => '1',
                'label' => 'Yes',
            ),
            array(
                'value' => '0',
                'label' => 'No',
            ),
        );
    }
}