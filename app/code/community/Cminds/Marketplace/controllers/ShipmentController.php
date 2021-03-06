<?php

class Cminds_Marketplace_ShipmentController extends Cminds_Marketplace_Controller_Action {

    public function preDispatch() {
        parent::preDispatch();
        $hasAccess = $this->_getHelper()->hasAccess();

        if(!$hasAccess) {
            $this->getResponse()->setRedirect($this->_getHelper('supplierfrontendproductuploader')->getSupplierLoginPage());
        }
    }

    public function createAction() {
        $id = $this->getRequest()->getParam('id');
        $marketplaceHelper = Mage::helper('marketplace');
        if (!$marketplaceHelper->canCreateShipment($id, Mage::getSingleton('customer/session')->getCustomerId())) {
            Mage::getModel('core/session')->addSuccess(
                'Order is cancelled and can not be shipped'
            );
            $this->_redirect('*/order/view/', array('id' =>$id));
            return;
        }
        Mage::register('order_id', $id);
        $this->_renderBlocks();
    }

    public function viewAction() {
        $id = $this->getRequest()->getParam('id');
        Mage::register('shipment_id', $id);
        $this->_renderBlocks();
    }
    public function saveAction() {
        $post = $this->_request->getPost();

        try {
            $transaction = Mage::getModel('core/resource_transaction');
            $order = Mage::getModel('sales/order')->load($post['order_id']);

            foreach($post['product'] AS $product_id => $qty) {

                if($qty <= 0) {
                    unset($post['product'][$product_id]);
                }
                $itemModel = Mage::getModel('sales/order_item')->load($product_id);
                if (($itemModel->getQtyCanceled()*1) == $itemModel->getQtyOrdered()) {
                    throw new Exception('Ordered Item has been canceled.');
                }
                if(!$itemModel->getProductId() || !Mage::helper('marketplace')->isOwner($itemModel->getProductId())) {
                    throw new Exception('You cannot ship non-owning products');
                }

                if($itemModel->getQtyOrdered() < ($itemModel->getQtyShipped() + intval($qty))) {
                    throw new Exception('You cannot ship more products than it was ordered');
                }

            }

            if($order->getState() == 'canceled') {
                throw new Exception('You cannot create shipment for canceled order');
            }
            $shipment = $order->prepareShipment($post['product']);

            $shipment->sendEmail((isset($post['notify_customer']) && $post['notify_customer'] == '1'))
                ->setEmailSent(false)
                ->register()
                ->save();

            foreach($shipment->getAllItems() AS $item) {
                $orderItem = Mage::getModel('sales/order_item')->load($item->getOrderItemId());
                $orderItem->setQtyShipped($item->getQty() + $orderItem->getQtyShipped());
                $orderItem->save();
            }

            $sh = Mage::getModel('sales/order_shipment_track')
                ->setShipment($shipment)
                ->setData('title', $post['title'])
                ->setData('number', $post['number'])
                ->setData('carrier_code', $post['carrier_code'])
                ->setData('order_id', $shipment->getData('order_id'));

            $transaction->addObject($sh);

            $loggedUser = Mage::getSingleton( 'customer/session', array('name' => 'frontend') );
            $customer = $loggedUser->getCustomer();

            $comment = $customer->getFirstname() .' '.$customer->getLastname() . ' (#'.$customer->getId().') created shipment for ' . count($post['product']) . ' item(s)';

            $order->addStatusHistoryComment($comment);
            $fullyInvoiced = false;
            if ($order->getData('base_subtotal') == $order->getData('base_subtotal_invoiced')) {
                $fullyInvoiced = true;
            }
            if($this->_orderFullyShipped($order->getId())) {
                if ($fullyInvoiced) {
                    $state = Mage_Sales_Model_Order::STATE_COMPLETE;
                    $status = $order->getConfig()->getStateDefaultStatus(Mage_Sales_Model_Order::STATE_COMPLETE);
                } else {
                    $state = Mage_Sales_Model_Order::STATE_PROCESSING;
                    $status = 'Shipped';
                }
            }
            if($state) {
                $order->setData('state', $state);
                $order->setStatus($status);
                $order->addStatusHistoryComment($comment, false);
                $order->save();
            }
            $transaction->addObject($order);

            $transaction->save();
            Mage::getSingleton('core/session')->addSuccess('Shipment for order #'.$order->getIncrementId().' was created');
            Mage::app()->getFrontController()->getResponse()->setRedirect(Mage::getUrl('*/order/view/', array('id' => $post['order_id'], 'tab' => 'shipment')));
        } catch (Exception $e) {
            if (null !== $order->getIncrementId()) {
                $order->addStatusHistoryComment('Failed to create shipment - '. $e->getMessage())
                    ->save();
            }
            Mage::getSingleton('core/session')->addError($e->getMessage());
            Mage::app()->getFrontController()->getResponse()->setRedirect(Mage::getUrl('*/shipment/create/', array('id' => $post['order_id'], 'tab' => 'shipment')));
        }
    }

    private function _orderFullyShipped($orderId = null)
    {
        if (is_null($orderId)) {
            return false;
        }
        $order = Mage::getModel('sales/order')->load($orderId);
        $totalOrderNumber = $order->getData('total_qty_ordered');
        $totalItemShipped = 0;
        foreach ($order->getAllVisibleItems() as $item){
            $totalItemShipped += $item->getQtyShipped();
        }
        if((int)$totalOrderNumber == (int)$totalItemShipped){
            return true;
        }
        return false;
    }
    public function printLabelAction() {
        $id = $this->getRequest()->getParam('id');
        try {
            $track = Mage::getModel('sales/order_shipment_track')->load($id);

            $model = Mage::getModel('marketplace/pdf');
             if ($track) {
                $model->setOrderId($track->getOrderId());
                $model->setCarrier($track->getCarrierCode());
                $pdf = $model->getPdf();
                return $this->_prepareDownloadResponse('label-'.Mage::getSingleton('core/date')->date('Y-m-d_H-i-s').'.pdf', $pdf->render(), 'application/pdf');
             }
        } catch(Exception $e) {
            Mage::getSingleton('core/session')->addError($e->getMessage());
        }
        $this->_redirect('*/order');
    }

    public function printAction()
    {
        if ($shipmentId = $this->getRequest()->getParam('id')) {
            if ($shipment = Mage::getModel('sales/order_shipment')->load($shipmentId)) {
                $pdf = Mage::getModel('sales/order_pdf_shipment')->setIsSupplier(true)->getPdf(array($shipment));
                $this->_prepareDownloadResponse('shipment'.Mage::getSingleton('core/date')->date('Y-m-d_H-i-s').
                    '.pdf', $pdf->render(), 'application/pdf');
            }
        }
        else {
            $this->_forward('noRoute');
        }
    }
}