<?php
class Cminds_Marketplacecustom_Model_Adminhtml_Observer {
    public function onCustomerSave($observer) {
        $request = $observer->getRequest();
        $customer = $observer->getCustomer();
        $postData = $request->getPost();

        if(!Mage::helper('marketplace')->isSupplier($customer->getId())) {
            return false;
        }

        try {
            $postData = $request->getPost();

            if(isset($postData['seller_data'])) {
                foreach($postData['seller_data'] AS $k => $v) {
                    $customer->setData($k, $v);
                }
                $customer->save();
            }
        } catch(Exception $e) {

        }
    }
}