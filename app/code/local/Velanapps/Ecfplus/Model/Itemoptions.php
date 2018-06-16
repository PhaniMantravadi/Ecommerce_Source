<?php
class Velanapps_Ecfplus_Model_Itemoptions extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('ecfplus/itemoptions');
    }
}