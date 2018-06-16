<?php
class Cminds_Marketplace_Adminhtml_SuppliersController extends Mage_Adminhtml_Controller_Action
{
    protected function _isAllowed() {
        return Mage::getSingleton('admin/session')->isAllowed('admin/suppliers/supplier_grid');
    }

    public function indexAction() {
        $this->_title($this->__('Suppliers'));
        $this->loadLayout();
        $this->_setActiveMenu('suppliers');
        $this->_addContent($this->getLayout()->createBlock('marketplace/adminhtml_supplier_list'));
        $this->renderLayout();
    }
    public function soldProductsAction() {
        $this->_title($this->__('Customers'))->_title($this->__('Manage Customers'));

        $customerId = $this->getRequest()->getParam('id');
        $customer = Mage::getModel('customer/customer');

        if ($customerId) {
            $customer->load($customerId);
        }

        Mage::register('current_customer', $customer);

        $this->loadLayout();
        $this->getLayout()->getBlock('admin.customer.view.edit.cart');
        $this->renderLayout();
    }
    public function assignedCategoriesAction() {
        $this->_title($this->__('Customers'))->_title($this->__('Manage Customers'));
        $customerId = (int) $this->getRequest()->getParam('id');
        $customer = Mage::getModel('customer/customer');

        if ($customerId) {
            $customer->load($customerId);
        }

        Mage::register('current_customer', $customer);

        $this->loadLayout();
        $this->getLayout()->getBlock('admin.customer.view.edit.cart');
        $this->renderLayout();
    }
    public function shippingCostsAction() {
        $this->_title($this->__('Customers'))->_title($this->__('Manage Customer Shipping Fees'));

        $customerId = (int) $this->getRequest()->getParam('id');
        $customer = Mage::getModel('customer/customer');

        if ($customerId) {
            $customer->load($customerId);
        }

        Mage::register('current_customer', $customer);
        $supplier = Mage::getModel('marketplace/methods')->load($customer->getId(), 'supplier_id');
        Mage::register('customer_shipping_costs', $supplier);

        $this->loadLayout();
        $this->getLayout()->getBlock('admin.customer.view.edit.cart');
        $this->renderLayout();
    }
    
    public function profileAction() {
        $this->_title($this->__('Customers'))->_title($this->__('Manage Supplier Profile'));

        $customerId = (int) $this->getRequest()->getParam('id');
        $customer = Mage::getModel('customer/customer');

        if ($customerId) {
            $customer->load($customerId);
        }

        Mage::register('current_customer', $customer);

        $this->loadLayout();
        $this->getLayout()->getBlock('admin.customer.view.edit.cart');
        $this->getLayout()->getBlock('head')
            ->setCanLoadExtJs(true);
        $this->renderLayout();
    }

    public function paymentProfileAction() {
        $this->_title($this->__('Customers'))->_title($this->__('Manage Supplier Payment Profile'));

        $customerId = (int) $this->getRequest()->getParam('id');
        $customer = Mage::getModel('customer/customer');

        if ($customerId) {
            $customer->load($customerId);
        }

        Mage::register('current_customer', $customer);

        $this->loadLayout();
        $this->getLayout()->getBlock('admin.customer.payment.profile');
        $this->renderLayout();
    }

    public function fieldsAction() {
        $this->loadLayout();
        $this->_setActiveMenu('suppliers');
        $this->_addContent($this->getLayout()->createBlock('marketplace/adminhtml_supplier_customfields'))
            ->renderLayout();
    }

    public function createCustomFieldAction() {
        $this->_forward('editCustomField');
    }

    public function editCustomFieldAction()
    {
        $field = Mage::getModel('marketplace/fields');
        if ($fieldId = $this->getRequest()->getParam('id', false)) {
            $field->load($fieldId);

            if (!$field->getId()) {
                $this->_getSession()->addError(
                    $this->__('This field no longer exists.')
                );

                return $this->_redirect(
                    '*/*/fields'
                );
            }
        }

        if ($postData = $this->getRequest()->getPost('fieldData')) {
            try {
                if (!$field->getId()) {
                    $postData['created_at'] = date('Y-m-d H:i:s');
                }
                $nameExists = Mage::getModel('marketplace/fields')->load($postData['name'], 'name');

                if($nameExists->getId() && !$this->getRequest()->getParam('id', false)) {
                    throw new Exception(
                        $this->__('Field with this name already exists.')
                    );
                }

                $field->addData($postData);
                $field->save();

                $this->_getSession()->addSuccess(
                    $this->__('The field has been saved.')
                );

                return $this->_redirect(
                    '*/*/fields',
                    array('id' => $field->getId())
                );
            } catch (Exception $e) {
                Mage::logException($e);
                $this->_getSession()->addError($e->getMessage());
            }
        }

        Mage::register('current_field', $field);

        if(isset($postData)) {
            Mage::register('current_field_post_data', $postData);
        }

        $editBlock = $this->getLayout()->createBlock(
            'marketplace/adminhtml_supplier_customfields_form'
        );

        $this->loadLayout()
            ->_addContent($editBlock)
            ->renderLayout();
    }

    public function deleteCustomFieldAction() {
        if ($fieldId = $this->getRequest()->getParam('id', false)) {
            $field = Mage::getModel('marketplace/fields');
            $field->load($fieldId);

            if (!$field->getId()) {
                $this->_getSession()->addError(
                    $this->__('This field no longer exists.')
                );
            }

            try {
                $field->delete();
            } catch(Exception $e) {
                $this->_getSession()->addError(
                    $this->__('Can not delete this field.')
                );
            }
        }

        return $this->_redirect(
            '*/*/fields'
        );
    }

    public function ratesAction() {
        $this->_title($this->__('Customers'))->_title($this->__('Customer Rates'));

        $customerId = (int) $this->getRequest()->getParam('id');
        $customer = Mage::getModel('customer/customer');

        if ($customerId) {
            $customer->load($customerId);
        }

        Mage::register('current_customer', $customer);

        $this->loadLayout();
        $this->renderLayout();
    }

    public function removeRateAction() {
        $id = $this->getRequest()->getParam('rate', false);
        $customer_id = $this->getRequest()->getParam('customer_id', false);
        if($id) {
            $rating = Mage::getModel('marketplace/rating')->load($id);

            if($rating->getId()) {
                try {
                    $rating->delete();
                    Mage::getSingleton('adminhtml/session')->addSuccess($this->__("Rating has been canceled"));
                } catch(Exception $e) {
                    Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                }
            }
        }
        $this->_redirect('*/customer/edit', array('id' => $customer_id));
    }

    public function processCsvAction() {
        $this->_title($this->__("Process Product CSV"));
        $this->loadLayout()
            ->_addBreadcrumb(
                Mage::helper('adminhtml')->__('Items Manager'),
                Mage::helper('adminhtml')->__('Item Manager')
            );
        $this->renderLayout();
    }

    public function approveFilesAction() {
        $fileIds = $this->getRequest()->getParam('file_id');
        if (!is_null($fileIds)) {
            $uploader = Mage::getModel('marketplace/uploader');
            $fileNames = '';
            foreach ($fileIds as $fileId) {
                $fileData = $uploader->load($fileId);
                $fileData->setStatus(Cminds_Marketplace_Helper_Uploader::APPROVED);
                $fileData->save();
                if (!$fileNames) {
                    $fileNames = $fileData->getData('file_name');
                } else {
                    $fileNames .= ', '.$fileData->getData('file_name');
                }
            }
        }
        Mage::getSingleton('adminhtml/session')->addSuccess(
            $this->__("%s files are approved for processing.", $fileNames)
        );
        $this->_redirectReferer(true);
    }

    public function processcsvgridAction(){
        $this->loadLayout();
        $this->getResponse()->setBody($this->getLayout()->createBlock('marketplace/adminhtml_uploader_grid')->toHtml());
    }

    public function orderCancleAction() {
        $this->_title($this->__("Manage Order Cancelation Reequest"));
        $this->loadLayout()
            ->_addBreadcrumb(
                Mage::helper('adminhtml')->__('Items Manager'),
                Mage::helper('adminhtml')->__('Item Manager')
            );
        $this->renderLayout();
    }

    public function rejectAction() {
        try {
            $cancelIds = $this->getRequest()->getParam('id', 0);
            if ($cancelIds) {
                foreach ($cancelIds as $cancelId) {
                    $orderCancel = Mage::getModel('marketplace/cancle')->load($cancelId);
                    $orderCancel->setStatus(Cminds_Marketplace_Model_Cancle::STATUS_REJECTED);
                    if ($orderCancel->save()) {
                        Mage::getSingleton('adminhtml/session')->addSuccess(
                            $this->__(
                                'Order Cancelation Request has been rejected'
                            )
                    );
                    }
                }

            }
        } catch (Exception $e) {
            Mage::logException($e);
            Mage::getSingleton('adminhtml/session')->addError(
                $e->getMessage()
            );
        }
        $this->_redirectReferer(true);
    }

    public function cancelAction() {
        try {
            $cancelIds = $this->getRequest()->getParam('id', 0);
            if ($cancelIds) {
                $orderId = false;
                foreach ($cancelIds as $cancelId) {
                    $orderCancel = Mage::getModel('marketplace/cancle')->load($cancelId);
                    if ($orderCancel->getStatus() != Cminds_Marketplace_Model_Cancle::STATUS_REJECTED) {
                        $orderItem = Mage::getModel('sales/order_item')->load($orderCancel->getOrderItemId());

                        if ($orderCancel->cancel($orderItem)) {
                            $orderCancel->setStatus(Cminds_Marketplace_Model_Cancle::STATUS_COMPLETE);
                            if ($orderCancel->save()) {
                                Mage::getModel('marketplace/cancle')->checkOrderCancel($orderItem->getOrderId());
                                Mage::getSingleton('adminhtml/session')->addSuccess(
                                    $this->__(
                                        'Order Cancelation Request has been completed'
                                    )
                                );
                            }
                        }

                    }

                }


            }
        } catch (Exception $e) {
            Mage::logException($e);
            Mage::getSingleton('adminhtml/session')->addError(
                $e->getMessage()
            );
        }
        $this->_redirectReferer(true);
    }
    public function ordercanclegridAction(){
        $this->loadLayout();
        $this->getResponse()->setBody($this->getLayout()->createBlock('marketplace/adminhtml_cancle_grid')->toHtml());
    }
    public function downloadCsvAction() {
        $fileId = $this->getRequest()->getParam('file_id', '');
        $fileId = urldecode($fileId);
        if ($fileId) {
            $file = Mage::getModel('marketplace/uploader')->load($fileId);
            $supplierHelper = Mage::helper('marketplace/uploader');
            if ($file->getStatus() != Cminds_Marketplace_Helper_Uploader::COMPLETE) {
                $filePath = $supplierHelper->getProductImportedFileDirectory().$file->getFileName();
            } else {
                $filePath = $supplierHelper->getArchiveDirectory().$file->getFileName();
            }

            $this->getResponse()->setHttpResponseCode(200)
                ->setHeader('Pragma','publi', true)
                ->setHeader('Cache-Control', 'must-revalidate, post-check=0', true)
                ->setHeader('Content-type', 'application/force-download')
                ->setHeader('Content-Length', filesize($filePath))
                ->setHeader(
                    'Content-Disposition', 'attachment'.';filename='.basename($filePath)
                );
            $this->getResponse()->clearBody();
            $this->getResponse()->sendHeaders();
            readfile($filePath);
            exit;
        }
    }

    public function processAction() {
        $fileIds = $this->getRequest()->getParam('file_id');
        if (!is_null($fileIds)) {
            $fileNames = '';
            $uploader = Mage::getModel('marketplace/uploader');
            $supplierUploaderHelper = Mage::helper('marketplace/uploader');
            foreach ($fileIds as $fileId) {
                $fileData = $uploader->load($fileId);
                if ($fileData->getFileName() &&
                    ($fileData->getStatus() != Cminds_Marketplace_Helper_Uploader::COMPLETE)) {
                    $fileData->setStatus(Cminds_Marketplace_Helper_Uploader::WORKING);
                    $fileData->save();
                    try {
                        if ($fileData->getFileType() == Cminds_Marketplace_Helper_Uploader::PRODUCT_FILE_CSV) {
                            $status = $supplierUploaderHelper->importProduts($fileData->getFileName(), $fileData);
                        }
                        if ($fileData->getFileType() == Cminds_Marketplace_Helper_Uploader::ATTRIBUTE_FILE_CSV) {
                            $status = $supplierUploaderHelper->importAttributes($fileData->getFileName(), $fileData);
                        }
                        if ($status) {
                            if (!$fileNames) {
                                $fileNames = $fileData->getData('file_name');
                            } else {
                                $fileNames .= ', '.$fileData->getData('file_name');
                            }
                        }
                    } catch (Exception $e) {
                        $fileData->setStatus(Cminds_Marketplace_Helper_Uploader::ERROR);
                        $fileData->save();
                        Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                    }
                }

            }
        }
        Mage::getSingleton('adminhtml/session')->addSuccess(
            $this->__("%s files has been processed successfully.", $fileNames)
        );
        $this->_redirectReferer(true);
    }
}
