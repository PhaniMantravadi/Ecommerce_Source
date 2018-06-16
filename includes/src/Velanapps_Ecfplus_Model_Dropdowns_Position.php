<?php
class  Velanapps_Ecfplus_Model_Dropdowns_Position
{
    public function toOptionArray(){
	
        return array(
            /* array(
                'value' => 'BL',
                'label' => 'Bottom Left',
            ),
            array(
                'value' => 'BR',
                'label' => 'Bottom Right',
            ), */
			 array(
                'value' => 'CL',
                'label' => 'Center Left',
            ),
            array(
                'value' => 'CR',
                'label' => 'Center Right',
            ),

        );
    }
}