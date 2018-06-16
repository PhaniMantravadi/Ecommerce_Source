<?php
class Cminds_Marketplace_Model_Mysql4_Invoice extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        $this->_init('marketplace/invoice', 'id');
    }
}