<?php 
class AsifHussain_ShippingCod_Block_Postcode extends Mage_Core_Block_Template { 

	protected function _prepareCollection()
    {
		$collection = Mage::getModel('asifhussain_shippingcod/postcode')->getCollection();
		$this->setCollection($collection);
		return parent::_prepareCollection(); 
    }
}
?>