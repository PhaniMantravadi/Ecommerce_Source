<?php
class Velanapps_Ecfplus_Model_Mysql4_Multiform extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {
       $this->_init('ecfplus/multiform', 'id');
    }
}