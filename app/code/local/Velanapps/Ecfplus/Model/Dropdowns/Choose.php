<?php
class Velanapps_Ecfplus_Model_Dropdowns_Choose
{
    public function toOptionArray()
    {
        return array(
            array(
                'value' => '0',
                'label' => 'Color Pallet',
            ),array(
                'value' => '1',
                'label' => 'Color Picker',
            ),
        );
    }
}