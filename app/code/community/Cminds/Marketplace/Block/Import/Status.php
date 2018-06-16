<?php
class Cminds_Marketplace_Block_Import_Status extends Mage_Core_Block_Template {
    public function _construct() {
        $this->setTemplate('marketplace/import/status.phtml');
    }

    public function getFlatCollection() {

        $collection = Mage::getModel('marketplace/uploader')->getCollection();
        $supplierId = Mage::getSingleton('customer/session')->getId();
        $filterValues = $this->getRequest()->getPost();
        $eATable = "eav_attribute_set";
        $collection->getSelect()
            ->joinInner(
                array('ea' => $eATable),
                'ea.attribute_set_id = main_table.attribute_set',
                array('ea.attribute_set_name as attribute_set_name')
            );
        if ($supplierId) {
            $collection->getSelect()->where('supplier_id = '.$supplierId);
        }

        if(isset($filterValues['status']) && ($filterValues['status'] != 5)) {
            $collection->getSelect()->where('main_table.status = ?', $this->getFilter('status'));
        }

        if($this->getFilter('from') && strtotime($this->getFilter('from'))) {
            $datetime = new DateTime($this->getFilter('from'));
            $collection->getSelect()->where('main_table.created_at >= ?', $datetime->format('Y-m-d') . " 00:00:00");
        }
        if($this->getFilter('to') && strtotime($this->getFilter('to'))) {
            $datetime = new DateTime($this->getFilter('to'));
            $collection->getSelect()->where('main_table.created_at <= ?', $datetime->format('Y-m-d') . " 23:59:59");
        }
        return $collection;
    }

    private function getFilter($key) {
        return $this->getRequest()->getPost($key);
    }

    public function isFullyShipped($orderId) {
        $order = Mage::getModel('sales/order')->load($orderId);
        $orderItems = $order->getItemsCollection();
        $allOrderItemIds = array();
        $shipments = $order->getShipmentsCollection();
        $shippedItemIds = array();

        foreach($orderItems As $item) {
            if(Mage::helper('marketplace')->isOwner($item->getProductId())) {
                $allOrderItemIds[$item->getItemId()] = $item->getQtyOrdered();
            }
        }

        foreach ($shipments as $shipment) {
            $shippedItems = $shipment->getItemsCollection();
            foreach ($shippedItems as $item) {
                if(Mage::helper('marketplace')->isOwner($item->getOrderItem()->getProductId())) {
                    if(!isset($shippedItemIds[$item->getOrderItemId()])) {
                        $shippedItemIds[$item->getOrderItemId()] = 0;
                    }
                    $shippedItemIds[$item->getOrderItemId()] = $shippedItemIds[$item->getOrderItemId()] + $item->getQty();
                }
            }
        }
        return (count($shippedItemIds) == count($allOrderItemIds) && array_sum($allOrderItemIds) == array_sum($shippedItemIds));
    }

    public function getImportStatus() {
        return array(
            Cminds_Marketplace_Helper_Uploader::PENDING => 'Pending',
            Cminds_Marketplace_Helper_Uploader::APPROVED => 'Approved For Processing',
            Cminds_Marketplace_Helper_Uploader::COMPLETE => 'Complete',
            Cminds_Marketplace_Helper_Uploader::ERROR => 'Error In Processing',
            Cminds_Marketplace_Helper_Uploader::WORKING => 'Working'
        );
    }

    public function statusHtml($value)
    {
        $class = '';
        switch ($value) {
            case Cminds_Marketplace_Helper_Uploader::PENDING :
                $class = 'grid-severity-notice';
                $value = 'Pending';
                break;
            case Cminds_Marketplace_Helper_Uploader::APPROVED :
                $class = 'grid-severity-notice';
                $value = 'Approved For Processing';
                break;
            case Cminds_Marketplace_Helper_Uploader::WORKING :
                $class = 'grid-severity-major';
                $value = 'Working';
                break;
            case Cminds_Marketplace_Helper_Uploader::COMPLETE :
                $class = 'grid-severity-notice';
                $value = 'Complete';
                break;
            case Cminds_Marketplace_Helper_Uploader::ERROR:
                $class = 'grid-severity-critical';
                $value = 'Error In Processing';
                break;
        }
        return '<span class="'.$class.'"><span>'.$value.'</span></span>';
    }
}