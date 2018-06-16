<?php

class Cminds_Marketplace_OrderController extends Cminds_Marketplace_Controller_Action {
    public function preDispatch() {
        parent::preDispatch();
        $hasAccess = $this->_getHelper()->hasAccess();

        if(!$hasAccess) {
            $this->getResponse()->setRedirect($this->_getHelper('supplierfrontendproductuploader')->getSupplierLoginPage());
        }
    }
    public function indexAction() {
        $this->_renderBlocks(false, true);
    }
    public function viewAction() {
        // @todo: add validation if products from orders belongs to the supplier
        $id = $this->getRequest()->getParam('id');
        Mage::register('order_id', $id);
        $this->_renderBlocks();
    }

    public function cancleAction() {
        try {

            $orderItemId = $this->getRequest()->getParam('order_item_id', null);
            if (!$orderItemId) {
                $this->_redirect('*/order');
            }
            $orderItem = Mage::getModel('sales/order_item')->load($orderItemId);
            $cancleModel = Mage::getModel('marketplace/cancle')->cancleOrder($orderItem);
            if ($cancleModel) {
                Mage::getModel('core/session')->addSuccess(
                    'Order has been requested for Canclation.'
                );

            } else {
                Mage::getModel('core/session')->addError(
                    'Order has been failed to request for Canclation.'
                );

            }

        } catch (Exception $e) {
            Mage::logException($e);
            Mage::getModel('core/session')->addError(
                'Order has been failed to request for Canclation.'
            );
        }
        $this->_redirectReferer(true);
        return;
    }

    public function exportCsvAction() {
        $orderItemsCollection = $this->_prepareOrderCollection();
        $productCsv = array();

        foreach($orderItemsCollection AS $orderItem) {
            $order = $this->getOrder($orderItem->getOrderId());
            $shippingAddress = $order->getShippingAddress();
            $productCsv[] = array(
                'Store SKU' => $orderItem->getSku(),
                'Order #' => $order->getIncrementId(),
                'Sale Date' => $orderItem->getName(),
                'Name' => $orderItem->getName(),
                'Quantity' => $orderItem->getQtyOrdered(),
                'Price' => $orderItem->getPrice(),
                'Discount' => $orderItem->getOriginalPrice() - $orderItem->getPrice(),
                'Shipping Address - First Name' => $shippingAddress ? $shippingAddress->getFirstname() : '',
                'Shipping Address - Last Name' => $shippingAddress ? $shippingAddress->getLastname() : '',
                'Shipping Address - Street' => $shippingAddress ? $shippingAddress->getStreet(1) . ' ' . $shippingAddress->getStreet(2) : '',
                'Shipping Address - City' => $shippingAddress ? $shippingAddress->getCity() : '',
                'Shipping Address - Region' => $shippingAddress ? $shippingAddress->getRegion() : ''
            );
        }

        Mage::helper('marketplace')->prepareCsvHeaders("order_export_" . date("Y-m-d") . ".csv");
        echo Mage::helper('marketplace')->array2Csv($productCsv);
    }



    private function _prepareOrderCollection() {
        $eavAttribute   = new Mage_Eav_Model_Mysql4_Entity_Attribute();
        $supplier_id    = Mage::helper('marketplace')->getSupplierId();
        $code           = $eavAttribute->getIdByCode('catalog_product', 'creator_id');
        $table          = "catalog_product_entity_int";
        $tableName      = Mage::getSingleton("core/resource")->getTableName($table);
        $orderTable = Mage::getSingleton('core/resource')->getTableName('sales/order');

        $collection = Mage::getModel('sales/order_item')->getCollection();
        $collection->getSelect()
            ->joinInner(array('o' => $orderTable), 'o.entity_id = main_table.order_id', array())
            ->joinInner(array('e' => $tableName), 'e.entity_id = main_table.product_id AND e.attribute_id = ' . $code, array() )
            ->where('main_table.parent_item_id is null')
            ->where('e.value = ?', $supplier_id)
            ->group('o.entity_id')
            ->order('o.entity_id DESC');

        if($this->getFilter('autoincrement_id')) {
            $collection->getSelect()->where('o.increment_id LIKE ?', "%".$this->getFilter('autoincrement_id')."%");
        }
        if($this->getFilter('status')) {
            $collection->getSelect()->where('o.status = ?', $this->getFilter('status'));
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

    private function getOrder($order_id) {
        if(!isset($this->_orders[$order_id])) {
            $this->_orders[$order_id] = Mage::getModel('sales/order')->load($order_id);
        }
        return $this->_orders[$order_id];
    }
}
