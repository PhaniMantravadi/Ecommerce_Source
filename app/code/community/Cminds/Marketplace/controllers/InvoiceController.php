<?php

class Cminds_Marketplace_InvoiceController extends Cminds_Marketplace_Controller_Action {
    public function preDispatch() {
        parent::preDispatch();
        $hasAccess = $this->_getHelper()->hasAccess();

        if(!$hasAccess) {
            Mage::app()->getFrontController()->getResponse()->setRedirect(Mage::helper('customer')->getLoginUrl());
        }
    }
    public function createAction() {
        $id = $this->getRequest()->getParam('id');
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

                if(!$itemModel->getProductId() || !Mage::helper('marketplace')->isOwner($itemModel->getProductId())) {
                    throw new Exception('You cannot ship non-owning products');
                }

                if($itemModel->getQtyOrdered() < ($itemModel->getQtyInvoiced() + intval($qty))) {
                    throw new Exception('You cannot ship more products than it was ordered');
                }

            }

            if($order->getState() == 'canceled') {
                throw new Exception('You cannot create shipment for canceled order');
            }

            $invoice = Mage::getModel('marketplace/inv', $order)->prepareInvoice($post['product']);

/*            $invoice->sendEmail((isset($post['notify_customer']) && $post['notify_customer'] == '1'))
                ->setEmailSent(false)
                ->register()
                ->save();*/

            $invoice->register();

            $invoice->getOrder()->setIsInProcess(true);

            foreach($invoice->getAllItems() AS $item) {
                $orderItem = Mage::getModel('sales/order_item')->load($item->getOrderItemId());
                $orderItem->setQtyInvoiced($item->getQty() + $orderItem->getQtyInvoiced());
            }


            $loggedUser = Mage::getSingleton('customer/session', array('name' => 'frontend') );
            $customer = $loggedUser->getCustomer();

            $comment = $customer->getFirstname() .' '.$customer->getLastname() . ' (#'.$customer->getId().') created invoice for ' . count($post['product']) . ' item(s)';

            $order->addStatusHistoryComment($comment);

            $fullyInvoiced = true;

            foreach ($order->getAllItems() as $item) {
                if ($item->getQtyToInvoiced() > 0) {
                    $fullyInvoiced = false;
                }
            }

            if($fullyInvoiced) {
                if($order->getState() != Mage_Sales_Model_Order::STATE_PROCESSING) {
                    $state = Mage_Sales_Model_Order::STATE_PROCESSING;
                    $order->setState($state, true);

                } elseif($order->getState() == Mage_Sales_Model_Order::STATE_PROCESSING) {
                    $state = Mage_Sales_Model_Order::STATE_COMPLETE;
                }

            }

            $transaction->addObject($invoice);
            $transaction->addObject($orderItem);
            $transaction->addObject($order);

            $transaction->save();
            Mage::getSingleton('core/session')->addSuccess('Invoice for order #'.$order->getIncrementId().' was created');
            Mage::app()->getFrontController()->getResponse()->setRedirect(Mage::getUrl('*/order/view/', array('id' => $post['order_id'], 'tab' => 'invoice')));
        } catch (Exception $e) {
            if (null !== $order->getIncrementId()) {
                $order->addStatusHistoryComment('Failed to create invoice - '. $e->getMessage())
                    ->save();
            }
            Mage::getSingleton('core/session')->addError($e->getMessage());
            Mage::app()->getFrontController()->getResponse()->setRedirect(Mage::getUrl('*/invoice/create/', array('id' => $post['order_id'], 'tab' => 'invoice')));
        }
    }

    private function _createSystemInvoice($order = null) {
        try {
            if (is_null($order)) {
                return;
            }
            $orderProductIds = array();
            $transaction = Mage::getModel('core/resource_transaction');
            $orderItems = $order->getAllItems();
            foreach($orderItems AS $orderItem) {
                $orderProductIds[$orderItem->getData('item_id')] = $orderItem->getData('qty_ordered');
            }

            if($order->getState() == 'canceled') {
                throw new Exception('You cannot create invoice for canceled order');
            }

            $invoice = Mage::getModel('marketplace/inv', $order)->prepareInvoice($orderProductIds);
            $invoice->register();

            $invoice->getOrder()->setIsInProcess(true);
            $invoiceItems = $invoice->getAllItems();
            $marketPlaceHelper = Mage::helper('marketplace');
            $invoiceItemCnt = 0;
            foreach($invoiceItems AS $item) {
                $product = Mage::getModel('catalog/product')->load($item->getProductId());
                $orderItem = Mage::getModel('sales/order_item')->load($item->getOrderItemId());
                if($product->getCreatorId() == $orderItem->getSellerId()) {
                    if (!$marketPlaceHelper->isAlreadyCancled($orderItem) && ($orderItem->getStatusId() != Mage_Sales_Model_Order_Item::STATUS_CANCELED)) {
                        $invoiceItemCnt++;
                        $orderItem->setQtyInvoiced($item->getQty() + $orderItem->getQtyInvoiced());
                    }

                }

            }
            if (!$invoiceItemCnt) {
                Mage::getModel('core/session')->addError(
                    'Order items has be already invoiced or cancelled or requested for cancellation, hence can not invoiced.'
                );
                return false;
            }

            $loggedUser = Mage::getSingleton('customer/session', array('name' => 'frontend') );
            $customer = $loggedUser->getCustomer();

            $comment = $customer->getFirstname() .' '.$customer->getLastname() . ' (#'.$customer->getId().') created invoice for ' . count($orderProductIds) . ' item(s)';

            $order->addStatusHistoryComment($comment);

            $fullyInvoiced = false;

            if ($order->getData('base_subtotal') == $order->getData('base_subtotal_invoiced')) {
                $fullyInvoiced = true;
            }

            if ($fullyInvoiced && ($order->getStatus() == 'Shipped')) {
                $order->setData('state', Mage_Sales_Model_Order::STATE_COMPLETE);
                $status = $order->getConfig()->getStateDefaultStatus(Mage_Sales_Model_Order::STATE_COMPLETE);
                $order->setStatus($status);
            }

            $transaction->addObject($invoice);
            $transaction->addObject($orderItem);
            $transaction->addObject($order);

            $transaction->save();
            return $invoice;
        } catch (Exception $e) {
            if (null !== $order->getIncrementId()) {
                $order->addStatusHistoryComment('Failed to create invoice - '. $e->getMessage())
                    ->save();
            }
            Mage::log($e->getMessage(), null, 'market_invoice.log', true);
        }
    }
    public function printAction()
    {
        if ($invoiceId = $this->getRequest()->getParam('id')) {
            if ($invoice = Mage::getModel('sales/order_invoice')->load($invoiceId)) {
                $pdf = Mage::getModel('sales/order_pdf_invoice')->setIsSupplier(true)->getPdf(array($invoice));
                $this->_prepareDownloadResponse('invoice'.Mage::getSingleton('core/date')->date('Y-m-d_H-i-s').
                    '.pdf', $pdf->render(), 'application/pdf');
            }
        }
        else {
            $this->_forward('noRoute');
        }
    }

    public function uploadAction() {
        $id = $this->getRequest()->getParam('order_id', 0);
        $this->_storeInvoiceFileInfo($id);
       $this->_redirect('*/order/view/', array('id' =>$id));
    }

    private function _storeInvoiceFileInfo($orderId = null) {
        if (is_null($orderId)) {
            return;
        }
        if (isset($_FILES['file']['name']) && ($_FILES['file']['tmp_name'] != NULL)) {
            $vendorHelper = Mage::helper('marketplace/uploader');
            $destinationPath = $vendorHelper->getSupplierImportedFileDirectory();
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 777, true);
            }
            try {

                $fileName = date('ymdhms') . '-invoice.pdf';
                $uploader = new Varien_File_Uploader($_FILES['file']);
                $uploader->setAllowedExtensions(array('pdf'));
                $uploader->setAllowRenameFiles(false);
                $uploader->setFilesDispersion(false);
                if ($uploader->save($destinationPath, $fileName)) {
                    $supplierFileModel = Mage::getModel('marketplace/invoice');
                    $supplierFileModel->setSupplierId(Mage::getSingleton('customer/session')->getCustomerId());
                    $supplierFileModel->setInvoice($fileName);
                    $supplierFileModel->setOrderId($orderId);
                    $supplierFileModel->setIsSend(0);

                    if ($supplierFileModel->save()) {
                        Mage::getModel('core/session')->addSuccess(
                           'Invoice '. $_FILES['file']['name'].' has been uploaded in system.'
                        );
                    }
                }
            } catch (Exception $e) {
            }
        }

    }


    public function createInvoiceAction()
    {
        try {
            $supplierHelper = Mage::helper('marketplace/uploader');
            $marketplaceHelper = Mage::helper('marketplace');
            $orderId = $this->getRequest()->getParam('order_id', '');
            if (!$marketplaceHelper->canCreateInvoice($orderId, Mage::getSingleton('customer/session')->getCustomerId())) {
                Mage::getModel('core/session')->addSuccess(
                   'Order is cancelled and can not be invoiced'
                );
                $this->_redirect('*/order/view/', array('id' =>$orderId));
                return;
            }

            $file = Mage::getModel('marketplace/invoice');
            $orderInfo = Mage::getModel('sales/order')->load($orderId);
            $invoiceInfo = $this->_createSystemInvoice($orderInfo);
            if (!$invoiceInfo) {
                $this->_redirect('*/order/view/', array('id' =>$orderId));
                return;
            }
            $file->setData('invoice_id', $invoiceInfo->getId());
            $file->setSupplierId(Mage::getSingleton('customer/session')->getCustomerId());
            $file->setOrderId($orderId);
            $file->setData('is_send', 1);
            $file->save();
            $pdf = Mage::getModel('sales/order_pdf_invoice')->setIsSupplier(true)->getPdf(array($invoiceInfo));
            $translate = Mage::getSingleton('core/translate');
            /* @var $translate Mage_Core_Model_Translate */
            $translate->setTranslateInline(false);
            $transactionalEmail = Mage::getModel('core/email_template')
                ->setDesignConfig(array('area' => 'frontend', 'store' => 0))
                ->setTemplateSubject("36Cultures : Invoice For Order #" . $orderInfo->getIncrementId());
            if (!empty($filePath) && file_exists($filePath)) {
                $transactionalEmail
                    ->getMail()
                    ->createAttachment(file_get_contents($pdf), 'application/pdf')
                    ->filename = basename($pdf);
            }
            $transactionalEmail->sendTransactional(
                $supplierHelper->getConf(Cminds_Marketplace_Helper_Uploader::INVOICE_NOTIFICATION_TEMPLATE),
                $supplierHelper->getConf(Cminds_Marketplace_Helper_Uploader::INVOICE_SENDER_EMAIL),
                $orderInfo->getCustomerEmail(),
                '',
                array(
                    'order'=>$orderInfo,
                    'invoice'=>$invoiceInfo
                )
            );
            $translate->setTranslateInline(true);

            Mage::getModel('core/session')->addSuccess(
                'Invoice '. basename($filePath).' has been send to customer.'
            );
        } catch (Exception $e) {

        }
        $this->_redirect('*/order/view/', array('id' =>$orderId));
    }


    public function deleteAction() {

        $id = $this->getRequest()->getParam('file_id', 0);
        if ($id) {
            $file = Mage::getModel('marketplace/invoice')->load($id);
            $supplierId = Mage::getSingleton('customer/session')->getId();
            if ($supplierId == $file->getSupplierId()) {
                $supplierHelper = Mage::helper('marketplace/uploader');
                $filePath = $supplierHelper->getSupplierImportedFileDirectory().$file->getInvoice();
                $fileName = $file->getInvoice();
                $file->delete();
                unlink($filePath);
                Mage::getModel('core/session')->addSuccess(
                    'Invoice '. $fileName.' has been removed.'
                );
            }

        }
        $this->_redirect('*/order/view/', array('id' =>$id));
    }
}
