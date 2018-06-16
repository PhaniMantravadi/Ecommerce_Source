<?php
require_once 'Mage/Adminhtml/controllers/CustomerController.php';

class Cminds_Marketplace_Adminhtml_CustomerController extends Mage_Adminhtml_CustomerController {

	public function saveAction() {
		$data = $this->getRequest()->getPost();
		$isSupplier = $this->getRequest()->getParam('supplier', false);
        if ($data) {
            $redirectBack = $this->getRequest()->getParam('back', false);
            $this->_initCustomer('customer_id');

            /** @var $customer Mage_Customer_Model_Customer */
            $customer = Mage::registry('current_customer');
            $transaction = Mage::getModel('core/resource_transaction');
            $customerShippingData = Mage::getModel('marketplace/methods')->load($customer->getId(), 'supplier_id');
            $customerShippingData->setSupplierId($customer->getId());
            $customerShippingData->setFlatRateFee(0);
            $customerShippingData->setFlatRateAvailable(0);
            $customerShippingData->setTableRateAvailable(0);
            $customerShippingData->setTableRateCondition(0);
            $customerShippingData->setTableRateFee(0);
            $customerShippingData->setFreeShipping(0);

            /** @var $customerForm Mage_Customer_Model_Form */
            $customerForm = Mage::getModel('customer/form');
            if ($isSupplier) {

                if (isset($data['supplier_profile_approved'])) {
                    $customer->setData('supplier_profile_approved', $data['supplier_profile_approved']);
                }
                if (isset($data['supplier_invoice_auth_label'])) {
                    $customer->setData('supplier_invoice_auth_label', $data['supplier_invoice_auth_label']);
                }
                if(isset($_FILES['supplier_invoice_auth_sign']['name']) and (file_exists($_FILES['supplier_invoice_auth_sign']['tmp_name']))) {
                    $uploader = new Varien_File_Uploader('supplier_invoice_auth_sign');
                    $uploader->setAllowedExtensions(array('jpg','jpeg','gif','png'));
                    $uploader->setAllowRenameFiles(false);
                    $uploader->setFilesDispersion(false);
                    $path = Mage::getBaseDir('media') . DS . 'supplier_logos' . DS;
                    $nameSplit = explode('.', $_FILES['supplier_invoice_auth_sign']['name']);
                    $ext = $nameSplit[count($nameSplit)-1];
                    $newName = md5($_FILES['supplier_invoice_auth_sign']['name'] . time()) . '.' . $ext;
                    $customer->setData('supplier_invoice_auth_sign', $newName);
                    $uploader->save($path, $newName);

                }
                if(isset($_FILES['supplier_invoice_logo']['name']) and (file_exists($_FILES['supplier_invoice_logo']['tmp_name']))) {
                    $uploader = new Varien_File_Uploader('supplier_invoice_logo');
                    $uploader->setAllowedExtensions(array('jpg','jpeg','gif','png'));
                    $uploader->setAllowRenameFiles(false);
                    $uploader->setFilesDispersion(false);
                    $path = Mage::getBaseDir('media') . DS . 'supplier_logos' . DS;
                    $nameSplit = explode('.', $_FILES['supplier_invoice_logo']['name']);
                    $ext = $nameSplit[count($nameSplit)-1];
                    $newName = md5($_FILES['supplier_invoice_logo']['name'] . time()) . '.' . $ext;
                    $customer->setData('supplier_invoice_logo', $newName);
                    $uploader->save($path, $newName);

                }

                if(isset($_FILES['supplier_logo']['name']) and (file_exists($_FILES['supplier_logo']['tmp_name']))) {
                    $uploader = new Varien_File_Uploader('supplier_logo');
                    $uploader->setAllowedExtensions(array('jpg','jpeg','gif','png'));
                    $uploader->setAllowRenameFiles(false);
                    $uploader->setFilesDispersion(false);
                    $path = Mage::getBaseDir('media') . DS . 'supplier_logos' . DS;
                    $nameSplit = explode('.', $_FILES['supplier_logo']['name']);
                    $ext = $nameSplit[count($nameSplit)-1];
                    $newName = md5($_FILES['supplier_logo']['name'] . time()) . '.' . $ext;
                    $customer->setSupplierLogo($newName);
                    $uploader->save($path, $newName);

//                    $changed = true;
                }
                if (isset($data['supplier_remark'])) {
                    $customer->setData('supplier_remark', $data['supplier_remark']);
                }
                if (isset($data['supplier_profile_visible'])) {
                    $customer->setData('supplier_profile_visible', $data['supplier_profile_visible']);
                }
                if (isset($data['supplier_name'])) {
                    $customer->setData('supplier_name', $data['supplier_name']);
                }
                if (isset($data['supplier_description'])) {
                    $customer->setData('supplier_description', $data['supplier_description']);
                }
                if (isset($data['companyname'])) {
                    $customer->setData('companyname', $data['companyname']);
                }
                if (isset($data['supplier_name_new'])) {
                    $customer->setData('supplier_name_new', $data['supplier_name_new']);
                }
                if (isset($data['supplier_description_new'])) {
                    $customer->setData('supplier_description_new', $data['supplier_description_new']);
                }
                if (isset($data['companyname_new'])) {
                    $customer->setData('companyname_new', $data['companyname_new']);
                }

                /*For adding shipping information*/
                if (isset($data['flat_rate_enabled'])) {
                    $customerShippingData->setFlatRateAvailable($data['flat_rate_enabled']);
                }
                if (isset($data['flat_rate_fee'])) {
                    $customerShippingData->setFlatRateFee($data['flat_rate_fee']);
                }
                if (isset($data['table_rate_enabled'])) {
                    $customerShippingData->setTableRateAvailable($data['table_rate_enabled']);
                }
                if (isset($data['table_rate_fee'])) {
                    $customerShippingData->setTableRateFee($data['table_rate_fee']);
                }
                if (isset($data['table_rate_condition'])) {
                    $customerShippingData->setTableRateCondition($data['table_rate_condition']);
                }

                if (isset($data['freeshipping_enabled'])) {
                    $customerShippingData->setFreeShipping($data['freeshipping_enabled']);
                }

                try {
                    $customerShippingData->save();
                } catch (Exception $e) {

                }

                $this->_parseUploadedCsv();
            }
            $customerForm->setEntity($customer)
                ->setFormCode('adminhtml_customer')
                ->ignoreInvisible(false)
            ;

            $formData = $customerForm->extractData($this->getRequest(), 'account');

            // Handle 'disable auto_group_change' attribute
            if (isset($formData['disable_auto_group_change'])) {
                $formData['disable_auto_group_change'] = empty($formData['disable_auto_group_change']) ? '0' : '1';
            }

            $errors = null;
            if ($customer->getId()&& !empty($data['account']['new_password'])
                && Mage::helper('customer')->getIsRequireAdminUserToChangeUserPassword()
            ) {
                //Validate current admin password
                if (isset($data['account']['current_password'])) {
                    $currentPassword = $data['account']['current_password'];
                } else {
                    $currentPassword = null;
                }
                unset($data['account']['current_password']);
                $errors = $this->_validateCurrentPassword($currentPassword);
            }

            if (!is_array($errors)) {
                $errors = $customerForm->validateData($formData);
            }

            if ($errors !== true) {
                foreach ($errors as $error) {
                    $this->_getSession()->addError($error);
                }
                $this->_getSession()->setCustomerData($data);
                $this->getResponse()->setRedirect($this->getUrl('*/customer/edit', array('id' => $customer->getId())));
                return;
            }

            $customerForm->compactData($formData);

            // Unset template data
            if (isset($data['address']['_template_'])) {
                unset($data['address']['_template_']);
            }

            $modifiedAddresses = array();
            if (!empty($data['address'])) {
                /** @var $addressForm Mage_Customer_Model_Form */
                $addressForm = Mage::getModel('customer/form');
                $addressForm->setFormCode('adminhtml_customer_address')->ignoreInvisible(false);

                foreach (array_keys($data['address']) as $index) {
                    $address = $customer->getAddressItemById($index);
                    if (!$address) {
                        $address = Mage::getModel('customer/address');
                    }

                    $requestScope = sprintf('address/%s', $index);
                    $formData = $addressForm->setEntity($address)
                        ->extractData($this->getRequest(), $requestScope);

                    // Set default billing and shipping flags to address
                    $isDefaultBilling = isset($data['account']['default_billing'])
                        && $data['account']['default_billing'] == $index;
                    $address->setIsDefaultBilling($isDefaultBilling);
                    $isDefaultShipping = isset($data['account']['default_shipping'])
                        && $data['account']['default_shipping'] == $index;
                    $address->setIsDefaultShipping($isDefaultShipping);

                    $errors = $addressForm->validateData($formData);
                    if ($errors !== true) {
                        foreach ($errors as $error) {
                            $this->_getSession()->addError($error);
                        }
                        $this->_getSession()->setCustomerData($data);
                        $this->getResponse()->setRedirect($this->getUrl('*/customer/edit', array(
                            'id' => $customer->getId())
                        ));
                        return;
                    }

                    $addressForm->compactData($formData);

                    // Set post_index for detect default billing and shipping addresses
                    $address->setPostIndex($index);

                    if ($address->getId()) {
                        $modifiedAddresses[] = $address->getId();
                    } else {
                        $customer->addAddress($address);
                    }
                }
            }

            // Default billing and shipping
            if (isset($data['account']['default_billing'])) {
                $customer->setData('default_billing', $data['account']['default_billing']);
            }
            if (isset($data['account']['default_shipping'])) {
                $customer->setData('default_shipping', $data['account']['default_shipping']);
            }
            if (isset($data['account']['confirmation'])) {
                $customer->setData('confirmation', $data['account']['confirmation']);
            }

            // Mark not modified customer addresses for delete
            foreach ($customer->getAddressesCollection() as $customerAddress) {
                if ($customerAddress->getId() && !in_array($customerAddress->getId(), $modifiedAddresses)) {
                    $customerAddress->setData('_deleted', true);
                }
            }

            if (Mage::getSingleton('admin/session')->isAllowed('customer/newsletter')
                && !$customer->getConfirmation()
            ) {
                $customer->setIsSubscribed(isset($data['subscription']));
            }

            if (isset($data['account']['sendemail_store_id'])) {
                $customer->setSendemailStoreId($data['account']['sendemail_store_id']);
            }

            $isNewCustomer = $customer->isObjectNew();
            try {
                $sendPassToEmail = false;
                // Force new customer confirmation
                if ($isNewCustomer) {
                    $customer->setPassword($data['account']['password']);
                    $customer->setForceConfirmed(true);
                    if ($customer->getPassword() == 'auto') {
                        $sendPassToEmail = true;
                        $customer->setPassword($customer->generatePassword());
                    }
                }

                Mage::dispatchEvent('adminhtml_customer_prepare_save', array(
                    'customer'  => $customer,
                    'request'   => $this->getRequest()
                ));

                $customer->save();

                // Send welcome email
                if ($customer->getWebsiteId() && (isset($data['account']['sendemail']) || $sendPassToEmail)) {
                    $storeId = $customer->getSendemailStoreId();
                    if ($isNewCustomer) {
                        $customer->sendNewAccountEmail('registered', '', $storeId);
                    } elseif ((!$customer->getConfirmation())) {
                        // Confirm not confirmed customer
                        $customer->sendNewAccountEmail('confirmed', '', $storeId);
                    }
                }

                if (!empty($data['account']['new_password'])) {
                    $newPassword = $data['account']['new_password'];
                    if ($newPassword == 'auto') {
                        $newPassword = $customer->generatePassword();
                    }
                    $customer->changePassword($newPassword);
                    $customer->sendPasswordReminderEmail();
                }

				if($isSupplier) {
                	Mage::getSingleton('adminhtml/session')->addSuccess(
                    	Mage::helper('adminhtml')->__('The supplier has been saved.')
                	);
            	} else {
            		Mage::getSingleton('adminhtml/session')->addSuccess(
	                    Mage::helper('adminhtml')->__('The customer has been saved.')
	                );
            	}
                
                Mage::dispatchEvent('adminhtml_customer_save_after', array(
                    'customer'  => $customer,
                    'request'   => $this->getRequest()
                ));

                if ($redirectBack) {
                    $this->_redirect('*/*/edit', array(
                        'id' => $customer->getId(),
                        '_current' => true
                    ));
                    return;
                }
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
                $this->_getSession()->setCustomerData($data);
                $this->getResponse()->setRedirect($this->getUrl('*/customer/edit', array('id' => $customer->getId())));
            } catch (Exception $e) {
                	if($isSupplier) {
                		$this->_getSession()->addException($e,
	                    	Mage::helper('adminhtml')->__('An error occurred while saving the supplier.'));
                	} else {
	            		$this->_getSession()->addException($e,		
                			Mage::helper('adminhtml')->__('An error occurred while saving the customer.'));
                	}
                $this->_getSession()->setCustomerData($data);
                $this->getResponse()->setRedirect($this->getUrl('*/customer/edit', array('id'=>$customer->getId())));
                return;
            }
        }
        if($isSupplier) {
        	$this->_redirect('*/suppliers');
        } else {
        	$this->_redirect('*/customer');
        }
	}

	public function deleteAction()
    {
        $this->_initCustomer();
        $customer = Mage::registry('current_customer');
        if ($customer->getId()) {
            try {
                $customer->load($customer->getId());
                $customer->delete();
                if($this->getRequest()->getParam('supplier', false)) {
                	Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('The supplier has been deleted.'));
                } else {
                	Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('The customer has been deleted.'));
                }
            }
            catch (Exception $e){
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }

        if($this->getRequest()->getParam('supplier', false)) {
        	$this->_redirect('*/suppliers');
        } else {
        	$this->_redirect('*/customer');
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

        if(isset($parsedData[0][0]) && ($parsedData[0][0] == 'Country')) {
            unset($parsedData[0]);
        }

        if($changed) {
            $customer = Mage::registry('current_customer');
            $supplierRate = Mage::getModel("marketplace/rates")
                ->load($customer->getId(), 'supplier_id');
            if(!$supplierRate->getId()) {
                $supplierRate->setSupplierId($customer->getId());
            }
            $supplierRate->setRateData(serialize($parsedData));
            $supplierRate->save();
        }
    }
}
