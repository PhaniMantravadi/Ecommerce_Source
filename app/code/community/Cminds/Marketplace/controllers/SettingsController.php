<?php

class Cminds_Marketplace_SettingsController extends Cminds_Marketplace_Controller_Action {
    public function preDispatch() {
        parent::preDispatch();
        $hasAccess = $this->_getHelper()->hasAccess();

        if(!$hasAccess) {
            $this->getResponse()->setRedirect($this->_getHelper('supplierfrontendproductuploader')->getSupplierLoginPage());
        }
    }
    public function shippingAction() {
        if(!Mage::getStoreConfig('marketplace_configuration/presentation/change_shipping_costs')) {
            $this->getResponse()->setHeader('HTTP/1.1','404 Not Found');
            $this->getResponse()->setHeader('Status','404 File not found');
            $this->_forward('defaultNoRoute');
        }

        $this->_renderBlocks();
    }

    public function shippingSaveAction() {
        $postData = $this->getRequest()->getPost();

        if(!Mage::getStoreConfig('marketplace_configuration/presentation/change_shipping_costs')) {
            $this->getResponse()->setHeader('HTTP/1.1','404 Not Found');
            $this->getResponse()->setHeader('Status','404 File not found');
            $this->_forward('defaultNoRoute');
        }

        try {
            $transaction = Mage::getModel('core/resource_transaction');
            $shipping = Mage::getModel('marketplace/methods')->load(Mage::helper('marketplace')->getSupplierId(), 'supplier_id');

            $shipping->setSupplierId(Mage::helper('marketplace')->getSupplierId());
            $shipping->setFlatRateFee(0);
            $shipping->setFlatRateAvailable(0);
            $shipping->setTableRateAvailable(0);
            $shipping->setTableRateCondition(0);
            $shipping->setTableRateFee(0);
            $shipping->setFreeShipping(0);

            if(isset($postData['shipping_method']) && $postData['shipping_method'] == "flat_rate") {
                $shipping->setFlatRateAvailable(1);
                $shipping->setFlatRateFee($postData['flat_rate_fee']);
            } else {
                $shipping->setFlatRateFee(0);
                $shipping->setFlatRateAvailable(0);
            }
            if(isset($postData['shipping_method']) && $postData['shipping_method'] == "table_rate") {
                $shipping->setTableRateAvailable(1);
                $shipping->setTableRateFee($postData['table_rate_fee']);
                $shipping->setTableRateCondition($postData['table_rate_condition']);
                $this->_parseUploadedCsv();
            } else {
                $shipping->setTableRateFee(0);
                $shipping->setTableRateAvailable(0);
            }
            if(isset($postData['shipping_method']) && $postData['shipping_method'] == "free_shipping") {
                $shipping->setFreeShipping(1);
            } else {
                $shipping->setFreeShipping(0);
            }
            $transaction->addObject($shipping);
            $transaction->save();

            Mage::app()->getFrontController()->getResponse()->setRedirect(Mage::getBaseUrl() . 'marketplace/settings/shipping/');
        } catch (Exception $e) {
            Mage::getSingleton('core/session')->addError($e->getMessage());
            Mage::app()->getFrontController()->getResponse()->setRedirect(Mage::getBaseUrl() . 'marketplace/settings/shipping/');
            Mage::log($e->getMessage());
        }
    }

    public function profileAction() {
        if(!Mage::getStoreConfig('marketplace_configuration/presentation/supplier_page_enabled')) {
            $this->getResponse()->setHeader('HTTP/1.1','404 Not Found');
            $this->getResponse()->setHeader('Status','404 File not found');
            $this->_forward('defaultNoRoute');
        }

        $this->_renderBlocks(true, true, false, true);
    }

    public function invoiceInfoAction() {
        $this->_renderBlocks(true, true, false, true);
    }

    public function invoiceInfoSaveAction() {
        $postData = $this->getRequest()->getPost();

        try {
            $customerData = false;

            if(Mage::getSingleton('customer/session')->isLoggedIn()) {
                $customerData = Mage::getModel('customer/customer')->load(Mage::getSingleton('customer/session')->getId());
            }

            if(!$customerData) {
                throw new ErrorException('Supplier does not exists');
            }


            if(isset($postData['submit'])) {

                $path = Mage::getBaseDir('media') . DS . 'supplier_logos' . DS;

                if(isset($postData['remove_sign'])) {
                    $s = $customerData->getData('supplier_invoice_auth_sign');

                    if(file_exists($path . $s)) {
                        unlink($path . $s);
                    }

                    $customerData->setData('supplier_invoice_auth_sign', null);
                }

                if(isset($postData['remove_logo'])) {
                    $s = $customerData->getData('supplier_invoice_logo');

                    if(file_exists($path . $s)) {
                        unlink($path . $s);
                    }

                    $customerData->setData('supplier_invoice_logo', null);
                }

                if(isset($postData['supplier_invoice_auth_label']) && $postData['supplier_invoice_auth_label']) {
                    $customerData->setData(
                        'supplier_invoice_auth_label',
                        $postData['supplier_invoice_auth_label']
                    );
                }
                if(isset($_FILES['supplier_invoice_auth_sign']['name']) and (file_exists($_FILES['supplier_invoice_auth_sign']['tmp_name']))) {
                    $uploader = new Varien_File_Uploader('supplier_invoice_auth_sign');
                    $uploader->setAllowedExtensions(array('jpg','jpeg','gif','png'));
                    $uploader->setAllowRenameFiles(false);
                    $uploader->setFilesDispersion(false);

                    $nameSplit = explode('.', $_FILES['supplier_invoice_auth_sign']['name']);
                    $ext = $nameSplit[count($nameSplit)-1];
                    $newName = md5($_FILES['supplier_invoice_auth_sign']['name'] . time()) . '.' . $ext;
                    $customerData->setData('supplier_invoice_auth_sign', $newName);
                    $uploader->save($path, $newName);

                }
                if(isset($_FILES['supplier_invoice_logo']['name']) and (file_exists($_FILES['supplier_invoice_logo']['tmp_name']))) {
                    $uploader = new Varien_File_Uploader('supplier_invoice_logo');
                    $uploader->setAllowedExtensions(array('jpg','jpeg','gif','png'));
                    $uploader->setAllowRenameFiles(false);
                    $uploader->setFilesDispersion(false);

                    $nameSplit = explode('.', $_FILES['supplier_invoice_logo']['name']);
                    $ext = $nameSplit[count($nameSplit)-1];
                    $newName = md5($_FILES['supplier_invoice_logo']['name'] . time()) . '.' . $ext;
                    $customerData->setData('supplier_invoice_logo', $newName);
                    $uploader->save($path, $newName);

                }

            }
            $customerData->save();
            Mage::app()->getFrontController()->getResponse()->setRedirect(Mage::getBaseUrl() . 'marketplace/settings/invoiceInfo/');
        } catch (Exception $e) {
            Mage::getSingleton('core/session')->addError($e->getMessage());
            Mage::app()->getFrontController()->getResponse()->setRedirect(Mage::getBaseUrl() . 'marketplace/settings/invoiceInfo/');
            Mage::log($e->getMessage());
        }
    }

    public function generalInfoEditAction() {
        //echo 'coming';exit;
        $this->_renderBlocks(true, true, false, true);
    }

    public function generalInfoSaveAction() {
        try {
            $customer = Mage::getModel('customer/customer')->load(Mage::getSingleton('customer/session')->getId());
            $session = $this->_getSession();
            $postData = $this->getRequest()->getPost();
            if ($customer instanceof Mage_Customer_Model_Customer) {
                if (isset($postData['firstname']) && $postData['firstname']) {
                    $customer->setFirstname($postData['firstname']);
                }
                if (isset($postData['lastname']) && $postData['lastname']) {
                    $customer->setLastname($postData['lastname']);
                }
                if (isset($postData['shop_name']) && $postData['shop_name']) {
                    $customer->setShopName($postData['shop_name']);
                }
                if (isset($postData['mobile_number']) && $postData['mobile_number']) {
                    $customer->setMobileNumber($postData['mobile_number']);
                }
                if (isset($postData['email']) && $postData['email']) {
                    $customer->setEmail($postData['email']);
                }
                if (isset($postData['about_shop']) && $postData['about_shop']) {
                    $customer->setAboutShop($postData['about_shop']);
                }
                if (isset($postData['password']) && $postData['password']) {
                    $customer->setPassword($postData['password']);
                    $customer->setPasswordConfirmation($postData['password']);
                }
                $path = Mage::getBaseDir('media') . DS . 'supplier_logos' . DS;
                if(isset($postData['remove_logo']) && $postData['remove_logo']) {
                    $s = $customer->getSupplierLogo();

                    if(file_exists($path . $s)) {
                        unlink($path . $s);
                    }

                    $customer->setSupplierLogo(null);
                }

                if(isset($_FILES['logo']['name']) and (file_exists($_FILES['logo']['tmp_name']))) {
                    $uploader = new Varien_File_Uploader('logo');
                    $uploader->setAllowedExtensions(array('jpg','jpeg','gif','png'));
                    $uploader->setAllowRenameFiles(false);
                    $uploader->setFilesDispersion(false);

                    $nameSplit = explode('.', $_FILES['logo']['name']);
                    $ext = $nameSplit[count($nameSplit)-1];
                    $newName = md5($_FILES['logo']['name'] . time()) . '.' . $ext;
                    $customer->setSupplierLogo($newName);
                    $uploader->save($path, $newName);
                }

                $customer->save();
                $session->addSuccess(
                    $this->__(
                        'General Information has been updated successfully.'
                    )
                );
            }

        } catch (Exception $e) {
            Mage::logException($e);
        }
        $this->_redirectReferer(true);
    }

    public function bankInfoSaveAction() {
        try {
            $customer = Mage::getModel('customer/customer')->load(Mage::getSingleton('customer/session')->getId());
            $session = $this->_getSession();
            $postData = $this->getRequest()->getPost();
            if ($customer instanceof Mage_Customer_Model_Customer) {
                if (isset($postData['bank_account']) && $postData['bank_account']) {
                    $customer->setBankAccount($postData['bank_account']);
                }
                if (isset($postData['bank_name']) && $postData['bank_name']) {
                    $customer->setBankName($postData['bank_name']);
                }
                if (isset($postData['branch_address']) && $postData['branch_address']) {
                    $customer->setBranchAddress($postData['branch_address']);
                }
                if (isset($postData['ifsc_code']) && $postData['ifsc_code']) {
                    $customer->setIfscCode($postData['ifsc_code']);
                }
                if (isset($postData['vat']) && $postData['vat']) {
                    $customer->setVat($postData['vat']);
                }
                if (isset($postData['pan']) && $postData['pan']) {
                    $customer->setPan($postData['pan']);
                }
                if (isset($postData['cst']) && $postData['cst']) {
                    $customer->setCst($postData['cst']);

                }


                $customer->save();
                $session->addSuccess(
                    $this->__(
                        'Bank Information has been updated successfully.'
                    )
                );
            }

        } catch (Exception $e) {
            Mage::logException($e);
        }
        $this->_redirectReferer(true);
    }
    protected function _getSession(){
        return Mage::getSingleton('customer/session');
    }
    public function bankInfoEditAction()
    {
        $this->_renderBlocks(true, true, false, true);
    }

    public function addressEditAction() {
        if(!Mage::getStoreConfig('marketplace_configuration/presentation/supplier_page_enabled')) {
            $this->getResponse()->setHeader('HTTP/1.1','404 Not Found');
            $this->getResponse()->setHeader('Status','404 File not found');
            $this->_forward('defaultNoRoute');
        }

        $this->_renderBlocks(true, true, false, true);
    }

    public function addressInfoSaveAction() {
        try {
            $customer = Mage::getModel('customer/customer')->load(Mage::getSingleton('customer/session')->getId());
            $session = $this->_getSession();
            $postData = $this->getRequest()->getPost();
            $address = Mage::getModel("customer/address");
            if ($customer instanceof Mage_Customer_Model_Customer) {
                if (isset($postData['address_id']) && $postData['address_id']) {
                    $address->load($postData['address_id']);
                    if (isset($postData['type']) && ($postData['type'] == 1)) {
                        $address->setCountryId($postData['registered']['country'])
                            ->setPostcode($postData['registered']['zipcode'])
                            ->setCompany($postData['registered']['company_name'])
                            ->setCity($postData['registered']['city'])
                            ->setRegion($postData['registered']['region'])
                            ->setRegionId($postData['registered']['region_id'])
                            ->setTelephone($customer->getMobileNumber())
                            ->setStreet($postData['registered']['address_line_1'] ."\n".$postData['registered']['address_line_2'])
                            ->setIsDefaultBilling('1')
                            ->setIsDefaultShipping('0');
                    } else if (isset($postData['type']) && ($postData['type'] == 2)) {
                        $address
                            ->setCountryId($postData['branch']['country'])
                            ->setPostcode($postData['branch']['zipcode'])
                            ->setCompany($postData['branch']['company_name'])
                            ->setCity($postData['branch']['city'])
                            ->setRegion($postData['branch']['region'])
                            ->setRegionId($postData['branch']['region_id'])
                            ->setTelephone($customer->getMobileNumber())
                            ->setStreet($postData['branch']['address_line_1'] ."\n".$postData['branch']['address_line_2'])
                            ->setIsDefaultBilling('0')
                            ->setIsDefaultShipping('1');
                    }
                    $address->save();
                }


                $session->addSuccess(
                    $this->__(
                        'Address Information has been updated successfully.'
                    )
                );
            }

        } catch (Exception $e) {
            Mage::logException($e);
        }
        $this->_redirectReferer(true);
    }
    public function profileSaveAction() {
        $postData = $this->getRequest()->getPost();

        if(!Mage::getStoreConfig('marketplace_configuration/presentation/supplier_page_enabled')) {
            $this->getResponse()->setHeader('HTTP/1.1','404 Not Found');
            $this->getResponse()->setHeader('Status','404 File not found');
            $this->_forward('defaultNoRoute');
        }

        try {
            $transaction = Mage::getModel('core/resource_transaction');
            $customerData = false;
            
            if(Mage::getSingleton('customer/session')->isLoggedIn()) {
                $customerData = Mage::getModel('customer/customer')->load(Mage::getSingleton('customer/session')->getId());
            }

            if(!$customerData) {
                throw new ErrorException('Supplier does not exists');
            }

            $waitingForApproval = false;

            if(isset($postData['submit'])) {
                $changed = false;
                $forceChange = false;

                if(htmlentities($postData['name'], ENT_QUOTES, "UTF-8") != $customerData->getSupplierName()) {
                    $changed = true;
                    $forceChange = true;
                    $customerData->setSupplierNameNew(htmlentities($postData['name'], ENT_QUOTES, "UTF-8"));
                }

                $path = Mage::getBaseDir('media') . DS . 'supplier_logos' . DS;

                if(isset($postData['remove_logo'])) {
                    $s = $customerData->getSupplierLogo();

                    if(file_exists($path . $s)) {
                        unlink($path . $s);
                    }

                    $customerData->setSupplierLogo(null);
                }

                if(isset($_FILES['logo']['name']) and (file_exists($_FILES['logo']['tmp_name']))) {
                    $uploader = new Varien_File_Uploader('logo');
                    $uploader->setAllowedExtensions(array('jpg','jpeg','gif','png'));
                    $uploader->setAllowRenameFiles(false);
                    $uploader->setFilesDispersion(false);

                    $nameSplit = explode('.', $_FILES['logo']['name']);
                    $ext = $nameSplit[count($nameSplit)-1];
                    $newName = md5($_FILES['logo']['name'] . time()) . '.' . $ext;
                    $customerData->setSupplierLogo($newName);
                    $uploader->save($path, $newName);

//                    $changed = true;
                }

                if(strip_tags($postData['description'], '<ol><li><b><span><a><i><u><p><br><h1><h2><h3><h4><h5><div>') != $customerData->getSupplierDescription() || $forceChange) {
                    $customerData->setSupplierDescriptionNew(strip_tags($postData['description'], '<ol><li><b><span><a><i><u><p><br><h1><h2><h3><h4><h5><div>'));

                    if(!$changed) {
                        $customerData->setSupplierNameNew(htmlentities($postData['name'], ENT_QUOTES, "UTF-8"));
                    }

                    $changed = true;
                }

                if(isset($postData['profile_enabled'])) {
                    $customerData->setSupplierProfileVisible(1);
                } else {
                    $customerData->setSupplierProfileVisible(0);
                }

                if($customerData->hasDataChanges() && $changed) {
                    $customerData->setData('rejected_notfication_seen', 2);
                    $waitingForApproval = true;

                }

                $customFieldsCollection = Mage::getModel('marketplace/fields')->getCollection();
                $customFieldsValues = array();
                $oldCustomFieldsValues = unserialize($customerData->getCustomFieldsValues());

                foreach($customFieldsCollection AS $field) {
                    if(isset($postData[$field->getName()])) {
                        if($field->getIsRequired() && $postData[$field->getName()] == '') {
                            throw new Exception("Field ".$field->getName()." is required");
                        }
                        
                        if($field->getType() == 'date' && !strtotime($postData[$field->getName()])) {
                            throw new Exception("Field ".$field->getName()." is not valid date");
                        }

                        $oldValue = $this->_findValue($field->getName(), $oldCustomFieldsValues);

                        if($oldValue != $postData[$field->getName()] && $field->getMustBeApproved()) { 
                            $waitingForApproval = true;
                        }

                        $customFieldsValues[] = array('name' => $field->getName(), 'value' => $postData[$field->getName()]);
                    }
                }

                if($waitingForApproval) {
                    $customerData->setNewCustomFieldsValues(serialize($customFieldsValues));
                } else {
                    $customerData->setCustomFieldsValues(serialize($customFieldsValues));
                }
            
            } elseif(isset($postData['clear'])) {
                $customerData->setSupplierNameNew(null);
                $customerData->setSupplierDescriptionNew(null);
                $customerData->setNewCustomFieldsValues(null);
            }

            $transaction->addObject($customerData);
            $transaction->save();
    
            if($waitingForApproval) {   
                Mage::helper('marketplace/email')->notifyAdminOnProfileChange($customerData); 
                Mage::getSingleton('core/session')->addSuccess($this->_getHelper()->__('Profile was changed and waiting for admin approval'));
            } else {
                Mage::getSingleton('core/session')->addSuccess($this->_getHelper()->__('Your profile was changed'));
            }

            Mage::app()->getFrontController()->getResponse()->setRedirect(Mage::getBaseUrl() . 'marketplace/settings/profile/');
        } catch (Exception $e) {
            Mage::getSingleton('core/session')->addError($e->getMessage());
            Mage::app()->getFrontController()->getResponse()->setRedirect(Mage::getBaseUrl() . 'marketplace/settings/profile/');
            Mage::log($e->getMessage());
        }
    }

    private function _parseUploadedCsv() {
        $changed = false;
        $parsedData = array();
        if (isset($_FILES["table_rate_file"])) {
            $changed = true;
            if(file_exists($_FILES["table_rate_file"]["tmp_name"])) {
                if (($handle = fopen($_FILES["table_rate_file"]["tmp_name"], "r")) !== FALSE) {
                    while (($row = fgetcsv($handle)) !== FALSE)
                    {
                        $parsedData[] = $row;
                    }
                    fclose($handle);
                } else {
                    throw new ErrorException('Cannot handle uploaded CSV');
                }
            }
        }

        if($parsedData[0][0] == 'Country') {
            unset($parsedData[0]);
        }

        if($changed) {
            $supplierRate = Mage::getModel("marketplace/rates")
                ->load(Mage::helper('marketplace')->getSupplierId(), 'supplier_id');

            if(!$supplierRate->getId()) {
                $supplierRate->setSupplierId(Mage::helper('marketplace')->getSupplierId());
            }

            $supplierRate->setRateData(serialize($parsedData));
            $supplierRate->save();
        }
    }

    private function _findValue($name, $data) {
        if(!is_array($data)) return false;
        
        foreach($data AS $value) {
            if($value['name'] == $name) {
                return $value['value'];
            }
        }

        return false;
    }
}
