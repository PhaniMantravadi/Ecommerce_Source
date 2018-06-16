<?php
/**
 * Magento Sz Edition
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magento Sz Edition License
 * that is bundled with this package in the file LICENSE_EE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.magentocommerce.com/license/sz-edition
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
 * @package     Sz_Rma
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://www.magentocommerce.com/license/sz-edition
 */

/**
 * RMA Item status attribute model
 *
 * @category   Sz
 * @package    Sz_Rma
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Sz_Rma_Model_Rma_Source_Status extends Sz_Rma_Model_Rma_Source_Abstract
{
    /**
     * Status constants
     */
    const STATE_PENDING            = 'pending';
    const STATE_AUTHORIZED         = 'authorized';
    const STATE_PARTIAL_AUTHORIZED = 'partially_authorized';
    const STATE_RECEIVED           = 'received';
    const STATE_RECEIVED_ON_ITEM   = 'received_on_item';
    const STATE_APPROVED           = 'approved';
    const STATE_APPROVED_ON_ITEM   = 'approved_on_item';
    const STATE_REJECTED           = 'rejected';
    const STATE_REJECTED_ON_ITEM   = 'rejected_on_item';
    const STATE_DENIED             = 'denied';
    const STATE_CLOSED             = 'closed';
    const STATE_PROCESSED_CLOSED   = 'processed_closed';


    /**
     * Get state label based on the code
     *
     * @param string $state
     * @return string
     */
    public function getItemLabel($state)
    {
        switch ($state) {
            case self::STATE_PENDING:            return Mage::helper('sz_rma')->__('Pending');
            case self::STATE_AUTHORIZED:         return Mage::helper('sz_rma')->__('Authorized');
            case self::STATE_PARTIAL_AUTHORIZED: return Mage::helper('sz_rma')->__('Partially Authorized');
            case self::STATE_RECEIVED:           return Mage::helper('sz_rma')->__('Return Received');
            case self::STATE_RECEIVED_ON_ITEM:   return Mage::helper('sz_rma')->__('Return Partially Received');
            case self::STATE_APPROVED:           return Mage::helper('sz_rma')->__('Approved');
            case self::STATE_APPROVED_ON_ITEM:   return Mage::helper('sz_rma')->__('Partially Approved');
            case self::STATE_REJECTED:           return Mage::helper('sz_rma')->__('Rejected');
            case self::STATE_REJECTED_ON_ITEM:   return Mage::helper('sz_rma')->__('Partially Rejected');
            case self::STATE_DENIED:             return Mage::helper('sz_rma')->__('Denied');
            case self::STATE_CLOSED:             return Mage::helper('sz_rma')->__('Closed');
            case self::STATE_PROCESSED_CLOSED:   return Mage::helper('sz_rma')->__('Processed and Closed');
            default: return $state;
        }
    }

    /**
     * Get RMA status by array of items status
     *
     * First function creates correspondence between RMA Item statuses and numbers
     * I.e. pending <=> 0, authorized <=> 1, and so on
     * Then it converts array with unique item statuses to "bitmask number"
     * according to mentioned before numbers as a bits
     * For Example if all item statuses are "pending", "authorized", "rejected",
     * then "bitmask number" = 2^0 + 2^1 + 2^5 = 35
     * Then function builds correspondence between these numbers and RMA's statuses
     * and returns it
     *
     * @param array $itemStatusArray Array of RMA items status
     * @throws Mage_Core_Exception
     * @return string
     */
    public function getStatusByItems($itemStatusArray)
    {
        if (!is_array($itemStatusArray) || empty($itemStatusArray)) {
            Mage::throwException(Mage::helper('sz_rma')->__('Wrong RMA item status.'));
        }

        $itemStatusArray = array_unique($itemStatusArray);

        $itemStatusModel = Mage::getModel('sz_rma/item_attribute_source_status');

        foreach ($itemStatusArray as $status) {
            if (!$itemStatusModel->checkStatus($status)) {
                Mage::throwException(Mage::helper('sz_rma')->__('Wrong RMA item status.'));
            }
        }

        $itemStatusToBits = array(
            Sz_Rma_Model_Item_Attribute_Source_Status::STATE_PENDING => 1,
            Sz_Rma_Model_Item_Attribute_Source_Status::STATE_AUTHORIZED => 2,
            Sz_Rma_Model_Item_Attribute_Source_Status::STATE_DENIED => 4,
            Sz_Rma_Model_Item_Attribute_Source_Status::STATE_RECEIVED => 8,
            Sz_Rma_Model_Item_Attribute_Source_Status::STATE_APPROVED => 16,
            Sz_Rma_Model_Item_Attribute_Source_Status::STATE_REJECTED => 32,
        );
        $rmaBitMaskStatus = 0;
        foreach ($itemStatusArray as $status) {
            $rmaBitMaskStatus += $itemStatusToBits[$status];
        }

        if ($rmaBitMaskStatus == 1) {
            return self::STATE_PENDING;
        } elseif ($rmaBitMaskStatus == 2) {
            return self::STATE_AUTHORIZED;
        } elseif ($rmaBitMaskStatus == 4) {
            return self::STATE_CLOSED;
        } elseif ($rmaBitMaskStatus == 5) {
            return self::STATE_PENDING;
        } elseif (($rmaBitMaskStatus > 2) && ($rmaBitMaskStatus < 8)) {
            return self::STATE_PARTIAL_AUTHORIZED;
        } elseif ($rmaBitMaskStatus == 8) {
            return self::STATE_RECEIVED;
        } elseif (($rmaBitMaskStatus >= 9) && ($rmaBitMaskStatus <= 15)) {
            return self::STATE_RECEIVED_ON_ITEM;
        } elseif ($rmaBitMaskStatus == 16) {
            return self::STATE_PROCESSED_CLOSED;
        } elseif ($rmaBitMaskStatus == 20) {
            return self::STATE_PROCESSED_CLOSED;
        } elseif (($rmaBitMaskStatus >= 17) && ($rmaBitMaskStatus <= 31)) {
            return self::STATE_APPROVED_ON_ITEM;
        } elseif ($rmaBitMaskStatus == 32) {
            return self::STATE_CLOSED;
        } elseif ($rmaBitMaskStatus == 36) {
            return self::STATE_CLOSED;
        } elseif (($rmaBitMaskStatus >= 33) && ($rmaBitMaskStatus <= 47)) {
            return self::STATE_REJECTED_ON_ITEM;
        } elseif ($rmaBitMaskStatus == 48) {
            return self::STATE_PROCESSED_CLOSED;
        } elseif ($rmaBitMaskStatus == 52) {
            return self::STATE_PROCESSED_CLOSED;
        } elseif (($rmaBitMaskStatus > 48)) {
            return self::STATE_APPROVED_ON_ITEM;
        } else {
            return self::STATE_PENDING;
        }
    }

    /**
     * Get available states keys for entities
     *
     * @return array
     */
    protected function _getAvailableValues()
    {
        return array(
            self::STATE_PENDING,
            self::STATE_AUTHORIZED,
            self::STATE_PARTIAL_AUTHORIZED,
            self::STATE_RECEIVED,
            self::STATE_RECEIVED_ON_ITEM,
            self::STATE_APPROVED_ON_ITEM,
            self::STATE_REJECTED_ON_ITEM,
            self::STATE_CLOSED,
            self::STATE_PROCESSED_CLOSED,
        );
    }

    /**
     * Get button disabled status
     *
     * @param string $status
     * @return bool
     */
    public function getButtonDisabledStatus($status)
    {
        if (
            in_array(
                $status,
                array(
                    self::STATE_PARTIAL_AUTHORIZED,
                    self::STATE_RECEIVED,
                    self::STATE_RECEIVED_ON_ITEM,
                    self::STATE_APPROVED_ON_ITEM,
                    self::STATE_REJECTED_ON_ITEM,
                    self::STATE_CLOSED,
                    self::STATE_PROCESSED_CLOSED,
                )
            )
        ) {
           return true;
        }
        return false;
    }
}
