<?php
/**
 * Magento Enterprise Edition
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magento Enterprise Edition License
 * that is bundled with this package in the file LICENSE_EE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.magentocommerce.com/license/enterprise-edition
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Sz
 * @package     Sz_Emaillog
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://www.magentocommerce.com/license/enterprise-edition
 */

/**
 * Email log helper
 *
 * @category   Sz
 * @package    Sz_Notification
 */
class Sz_Notification_Helper_Data extends Mage_Core_Helper_Abstract
{
    const PRODUCT_FILE_UPLOAD_NOTIFICATION_IS_ENABLE     = 'notification/product_file_upload/is_enable';
    const PRODUCT_FILE_UPLOAD_NOTIFICATION_EMAIL_SUBJECT  = 'notification/product_file_upload/subject';
    const PRODUCT_FILE_UPLOAD_NOTIFICATION_SENDER_EMAIL  = 'notification/product_file_upload/sender';
    const PRODUCT_FILE_UPLOAD_NOTIFICATION_COPY_ADDRESS  = 'notification/product_file_upload/receiver';
    const PRODUCT_FILE_UPLOAD_NOTIFICATION_TEMPLATE = 'notification/product_file_upload/email_template';

    const PRODUCT_FILE_PROCESS_NOTIFICATION_IS_ENABLE     = 'notification/product_file_process/is_enable';
    const PRODUCT_FILE_PROCESS_NOTIFICATION_EMAIL_SUBJECT  = 'notification/product_file_process/subject';
    const PRODUCT_FILE_PROCESS_NOTIFICATION_SENDER_EMAIL  = 'notification/product_file_process/sender';
    const PRODUCT_FILE_PROCESS_NOTIFICATION_COPY_ADDRESS  = 'notification/product_file_process/receiver';
    const PRODUCT_FILE_PROCESS_NOTIFICATION_TEMPLATE = 'notification/product_file_process/email_template';

    const ORDER_PLACE_NOTIFICATION_IS_ENABLE     = 'notification/order_place_notification/is_enable';
    const ORDER_PLACE_NOTIFICATION_EMAIL_SUBJECT  = 'notification/order_place_notification/subject';
    const ORDER_PLACE_NOTIFICATION_SENDER_EMAIL  = 'notification/order_place_notification/sender';
    const ORDER_PLACE_NOTIFICATION_COPY_ADDRESS  = 'notification/order_place_notification/receiver';
    const ORDER_PLACE_NOTIFICATION_TEMPLATE = 'notification/order_place_notification/email_template';

    const ORDER_CANCELLATION_REQUEST_NOTIFICATION_IS_ENABLE     = 'notification/order_cancellation_request/is_enable';
    const ORDER_CANCELLATION_REQUEST_EMAIL_SUBJECT  = 'notification/order_cancellation_request/subject';
    const ORDER_CANCELLATION_REQUEST_NOTIFICATION_SENDER_EMAIL  = 'notification/order_cancellation_request/sender';
    const ORDER_CANCELLATION_REQUEST_NOTIFICATION_COPY_ADDRESS  = 'notification/order_cancellation_request/receiver';
    const ORDER_CANCELLATION_REQUEST_NOTIFICATION_TEMPLATE = 'notification/order_cancellation_request/email_template';

    const ORDER_CANCELLATION_PROCESS_NOTIFICATION_IS_ENABLE     = 'notification/order_cancellation_process/is_enable';
    const ORDER_CANCELLATION_PROCESS_EMAIL_SUBJECT  = 'notification/order_cancellation_process/subject';
    const ORDER_CANCELLATION_PROCESS_NOTIFICATION_SENDER_EMAIL  = 'notification/order_cancellation_process/sender';
    const ORDER_CANCELLATION_PROCESS_NOTIFICATION_COPY_ADDRESS  = 'notification/order_cancellation_process/receiver';
    const ORDER_CANCELLATION_PROCESS_NOTIFICATION_TEMPLATE = 'notification/order_cancellation_process/email_template';


    public function getConf($path = null, $storeId = 1){
        if (is_null($path)) return false;
        return Mage::getStoreConfig($path, $storeId);
    }

    public function sendProductFileUploadNotification($fileInfo, $supplierInfo) {
        try {
            if ($this->getConf(self::PRODUCT_FILE_UPLOAD_NOTIFICATION_IS_ENABLE)) {
                $receiverEmails = explode(',', $this->getConf(self::PRODUCT_FILE_UPLOAD_NOTIFICATION_COPY_ADDRESS));
                $this->sendEmail(
                    $this->getConf(self::PRODUCT_FILE_UPLOAD_NOTIFICATION_EMAIL_SUBJECT),
                    $this->getConf(self::PRODUCT_FILE_UPLOAD_NOTIFICATION_TEMPLATE),
                    $this->getConf(self::PRODUCT_FILE_UPLOAD_NOTIFICATION_SENDER_EMAIL),
                    $receiverEmails,
                    array(
                        'file' => $fileInfo,
                        'supplier' => $supplierInfo
                    )
                );
            }
        } catch (Exception $e) {
            Mage::logException($e);
        }
        return;
    }
    
    public function sendProductFileProcessNotification($fileInfo, $supplierInfo) {
        try {
            if ($this->getConf(self::PRODUCT_FILE_PROCESS_NOTIFICATION_IS_ENABLE)) {
                $receiverEmails = explode(',', $this->getConf(self::PRODUCT_FILE_PROCESS_NOTIFICATION_COPY_ADDRESS));
                $receiverEmails[] = $supplierInfo->getEmail();
                $this->sendEmail(
                    $this->getConf(self::PRODUCT_FILE_PROCESS_NOTIFICATION_EMAIL_SUBJECT),
                    $this->getConf(self::PRODUCT_FILE_PROCESS_NOTIFICATION_TEMPLATE),
                    $this->getConf(self::PRODUCT_FILE_PROCESS_NOTIFICATION_SENDER_EMAIL),
                    $receiverEmails,
                    array(
                        'file' => $fileInfo,
                        'supplier' => $supplierInfo
                    )
                );
            }
        } catch (Exception $e) {
            Mage::logException($e);
        }
        return;
    }

    public function sendOrderPlacementNotification($orderInfo, $supplierInfo){
        try {
            if ($this->getConf(self::ORDER_PLACE_NOTIFICATION_IS_ENABLE)) {
                $receiverEmails = explode(',', $this->getConf(self::ORDER_PLACE_NOTIFICATION_COPY_ADDRESS));
                $receiverEmails[] = $supplierInfo->getEmail();
                $this->sendEmail(
                    $this->getConf(self::ORDER_PLACE_NOTIFICATION_EMAIL_SUBJECT),
                    $this->getConf(self::ORDER_PLACE_NOTIFICATION_TEMPLATE),
                    $this->getConf(self::ORDER_PLACE_NOTIFICATION_SENDER_EMAIL),
                    $receiverEmails,
                    array(
                        'order' => $orderInfo,
                        'supplier' => $supplierInfo
                    )
                );
            }
        } catch (Exception $e) {
            Mage::logException($e);
        }
        return;
    }

    public function sendOrderCancellationRequestNotification($orderItem = null){
        try {
            if (is_null($orderItem) || !($orderItem instanceof Mage_Sales_Model_Order_Item)) {
                return;
            }
            $order = Mage::getModel('sales/order')->load($orderItem->getOrderId());
            if (!$order->getCustomerId()) {
                return;
            }
            $product = Mage::getModel('catalog/product')->load($orderItem->getProductId());
            $customerInfo = Mage::getModel('customer/customer')->load($order->getCustomerId());
            $supplierInfo = Mage::getModel('customer/customer')->load($orderItem->getSellerId());
            if ($this->getConf(self::ORDER_CANCELLATION_REQUEST_NOTIFICATION_IS_ENABLE)) {
                $receiverEmails = explode(',', $this->getConf(self::ORDER_CANCELLATION_REQUEST_NOTIFICATION_COPY_ADDRESS));
                $receiverEmails[] = $supplierInfo->getEmail();
                $receiverEmails[] = $customerInfo->getEmail();
                $this->sendEmail(
                    $this->getConf(self::ORDER_CANCELLATION_REQUEST_EMAIL_SUBJECT),
                    $this->getConf(self::ORDER_CANCELLATION_REQUEST_NOTIFICATION_TEMPLATE),
                    $this->getConf(self::ORDER_CANCELLATION_REQUEST_NOTIFICATION_SENDER_EMAIL),
                    $receiverEmails,
                    array(
                        'order' => $order,
                        'product' => $product
                    )
                );
            }
        } catch (Exception $e) {
            Mage::logException($e);
        }
        return;
    }
    
    public function sendOrderCancellationProcessNotification($orderItem = null){
        try {
            if (is_null($orderItem) || !($orderItem instanceof Mage_Sales_Model_Order_Item)) {
                return;
            }
            $order = Mage::getModel('sales/order')->load($orderItem->getOrderId());
            if (!$order->getCustomerId()) {
                return;
            }
            $customerInfo = Mage::getModel('customer/customer')->load($order->getCustomerId());
            $supplierInfo = Mage::getModel('customer/customer')->load($orderItem->getSellerId());
            if ($this->getConf(self::ORDER_CANCELLATION_PROCESS_NOTIFICATION_IS_ENABLE)) {
                $receiverEmails = explode(',', $this->getConf(self::ORDER_CANCELLATION_PROCESS_NOTIFICATION_COPY_ADDRESS));
                $receiverEmails[] = $supplierInfo->getEmail();
                $receiverEmails[] = $customerInfo->getEmail();
                $this->sendEmail(
                    $this->getConf(self::ORDER_CANCELLATION_PROCESS_EMAIL_SUBJECT),
                    $this->getConf(self::ORDER_CANCELLATION_PROCESS_NOTIFICATION_TEMPLATE),
                    $this->getConf(self::ORDER_CANCELLATION_PROCESS_NOTIFICATION_SENDER_EMAIL),
                    $receiverEmails,
                    array(
                        'order' => $order
                    )
                );
            }
        } catch (Exception $e) {
            Mage::logException($e);
        }
        return;
    }
    
    public function sendEmail($templateSubject, $emailTemplate, $sender, $receivers, $variables= array()) {
        try {
            $receiverEmails = $receivers;
            foreach ($receiverEmails as $receiverEmail) {

                $translate = Mage::getSingleton('core/translate');
                /* @var $translate Mage_Core_Model_Translate */
                $translate->setTranslateInline(false);
                Mage::getModel('core/email_template')
                    ->setDesignConfig(array('area' => 'frontend', 'store' => 0))
                    ->setTemplateSubject($templateSubject)
                    ->sendTransactional(
                        $emailTemplate,
                        $sender,
                        $receiverEmail,
                        '',
                        $variables
                    );
                $translate->setTranslateInline(true);
            }
        } catch (Exception $e) {
            Mage::logException($e);
        }

    }
}
