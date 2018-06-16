<?php
class Cminds_Marketplace_Model_Mysql4_Cancle extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        $this->_init('marketplace/cancle', 'id');
    }
}