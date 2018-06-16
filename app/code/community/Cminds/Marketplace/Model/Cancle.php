<?php
class Cminds_Marketplace_Model_Cancle extends Mage_Core_Model_Abstract
{

    const STATE_PARTIAL = 2;
    const STATE_COMPLETE = 1;
    const STATUS_PENDING = 1;
    const STATUS_COMPLETE = 2;
    const STATUS_REJECTED = 3;

    public function _construct()
    {
        parent::_construct();
        $this->_init('marketplace/cancle');
    }


    public function checkOrderCancel($orderId = null) {
        try {
            if (is_null($orderId)) {
                return false;
            }
            $cancleOrder = true;
            $order = Mage::getModel('sales/order')->load($orderId);
            $orderItems = $order->getItemsCollection();
            foreach ($orderItems as $orderItem) {
                if (($orderItem->getStatusId() != Mage_Sales_Model_Order_Item::STATUS_CANCELED)) {
                    $cancleOrder = false;
                }
            }
            if ($cancleOrder) {
                try {
                    $state = Mage_Sales_Model_Order::STATE_CANCELED;
                    $order->setState($state, true);
                    $order->setStatus('Canceled');
                    $order->save();
                } catch (Exception $e) {
                    Mage::logException($e);
                }

            }
        } catch (Exception $e) {

        }
    }
    public function cancleOrder($orderItem = null) {
        try {
            if ($orderItem instanceof Mage_Sales_Model_Order_Item) {
                if (($orderItem->getStatusId() != Mage_Sales_Model_Order_Item::STATUS_CANCELED) &&
                    ($orderItem->getStatusId() != Mage_Sales_Model_Order_Item::STATUS_SHIPPED) &&
                    ($orderItem->getStatusId() != Mage_Sales_Model_Order_Item::STATUS_INVOICED)) {
                    if (!$this->isAlreadyRequested($orderItem->getOrderId(), $orderItem->getItemId())) {
                        $this->setOrderId($orderItem->getOrderId());
                        $this->setOrderItemId($orderItem->getItemId());
                        $this->setStatus(self::STATUS_PENDING);

                        $helper = Mage::helper('marketplace');
                        $customer = $helper->getLoggedSupplier();
                        if ($customer instanceof Mage_Customer_Model_Customer && $helper->isSupplier($customer->getId())) {
                            $this->setSupplierId($customer->getId());
                            $this->setIsSupplierRequest(self::STATE_COMPLETE);

                        } else {
                            $this->setIsSupplierRequest(self::STATE_PARTIAL);
                        }
                        if ($this->save()) {
                            Mage::helper('sz_notification')->sendOrderCancellationRequestNotification(
                                $orderItem
                            );
                            return true;
                        }
                    }
                }
            }

        } catch (Exception $e) {
            Mage::logException($e);
            return false;
        }
        return false;
    }

    public function isAlreadyRequested($orderId = null, $orderItemId = null)
    {
        try {
            if (is_null($orderId) && is_null($orderItemId)) {
                return false;
            }
            $collection = $this->getCollection();
            $collection->getSelect()->Where('order_id ='.$orderId.' AND order_item_id ='.$orderItemId.'
             AND status !='.self::STATUS_REJECTED);
            if (count($collection)) {
                return $collection->getFirstItem();
            }
        } catch (Exception $e) {
            Mage::logException($e);
        }
        return false;
    }

    public function cancel($orderItem = null){
        try {
            if ($orderItem instanceof Mage_Sales_Model_Order_Item) {
                if (($orderItem->getStatusId() != Mage_Sales_Model_Order_Item::STATUS_CANCELED) &&
                    ($orderItem->getStatusId() != Mage_Sales_Model_Order_Item::STATUS_SHIPPED) &&
                    ($orderItem->getStatusId() != Mage_Sales_Model_Order_Item::STATUS_INVOICED)) {
                    $orderItem->cancel();
                    $orderItem->save();
                    Mage::helper('sz_notification')->sendOrderCancellationProcessNotification(
                        $orderItem
                    );
                    return true;
                }
            }

        } catch (Exception $e) {
            Mage::logException($e);
            return false;
        }
        return false;
    }
}