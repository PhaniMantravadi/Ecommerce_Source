<?php
class Magik_Backorder_Model_Mysql4_Backorder extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init("backorder/backorder", "id");
    }
}