<?php
class Magik_Backorder_Helper_Data extends Mage_Core_Helper_Abstract
{

    public function isEnabled()
    {
        return Mage::getStoreConfig('bkordersection/bkordergeneral/enabled');
    }
    public function isadvanceEnabled()
    {
        return Mage::getStoreConfig('advanced/modules_disable_output/Magik_Backorder');
    }
}
	 