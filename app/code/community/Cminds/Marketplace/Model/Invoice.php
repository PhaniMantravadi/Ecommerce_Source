<?php
class Cminds_Marketplace_Model_Invoice extends Mage_Core_Model_Abstract
{

    public function _construct()
    {
        parent::_construct();
        $this->_init('marketplace/invoice');
    }

}