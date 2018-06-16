<?php
class Cminds_Marketplace_Block_Shipment_Create extends Mage_Core_Block_Template {
    public function getOrder() {
        $id = Mage::registry('order_id');
        return Mage::getModel('sales/order')->load($id);
    }
    public function getItems() {
        $id = Mage::registry('order_id');
        $_order = Mage::getModel('sales/order')->load($id);
        $_items = array();
        $marketPlaceHelper = Mage::helper('marketplace');
        foreach($_order->getAllItems() AS $item) {
            $product = Mage::getModel('catalog/product')->load($item->getProductId());

            if($product->getCreatorId() == Mage::helper('marketplace')->getSupplierId()) {

                if (!$marketPlaceHelper->isAlreadyCancled($item) && ($item->getStatusId() != Mage_Sales_Model_Order_Item::STATUS_CANCELED)) {
                    $_items[] = $item;
                }

            }
        }

        return $_items;
    }
}