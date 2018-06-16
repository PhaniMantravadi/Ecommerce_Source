<?php
class Cminds_Marketplace_Model_Mysql4_Cancle_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('marketplace/cancle');
    }
}