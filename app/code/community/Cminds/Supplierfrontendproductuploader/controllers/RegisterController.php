<?php

class Cminds_Supplierfrontendproductuploader_RegisterController extends Cminds_Supplierfrontendproductuploader_Controller_Action {
    protected $forceHeader = true;
    protected $forceFooter = true;
    public function preDispatch() {
        parent::preDispatch();
    }

    protected function _getSession(){
        return Mage::getSingleton('customer/session');
    }

    public function indexAction() {
        if(!Mage::getSingleton('customer/session')->isLoggedIn()) {
            if ($this->_getHelper()->canRegister()) {
                $this->_renderBlocks(true, true);
            } else {
                $this->getResponse()->setHeader('HTTP/1.1', '404 Not Found');
                $this->getResponse()->setHeader('Status', '404 File not found');
                $this->_forward('defaultNoRoute');
                return;
            }
        } else {
            $this->_redirect('*/index/');
            return;
        }
    }

    public function createPostAction()
    {
        /** @var $session Mage_Customer_Model_Session */
        $session = $this->_getSession();
        if ($session->isLoggedIn()) {
            $this->_redirect('*/*/');
            return;
        }
        $session->setEscapeMessages(true); // prevent XSS injection in user input
        if (!$this->getRequest()->isPost()) {
            $errUrl = $this->_getUrl('*/*/*', array('_secure' => true));
            $this->_redirectError($errUrl);
            return;
        }

        $customer = $this->_getCustomer();
        try {
            $errors = $this->_getCustomerErrors($customer);
            if (empty($errors)) {
                if(method_exists($customer, 'cleanPasswordsValidationData')) {
                    $customer->cleanPasswordsValidationData();
                }
                $customer->save();
                $this->_completeStep2($customer);
                $this->_uploadDocuments($customer);
                Mage::dispatchEvent('customer_register_success',
                    array('account_controller' => $this, 'customer' => $customer)
                );
                $this->_successProcessRegistration($customer);
                return;
            } else {
                $this->_addSessionError($errors);
            }
        } catch (Mage_Core_Exception $e) {
            $session->setCustomerFormData($this->getRequest()->getPost());
            if ($e->getCode() === Mage_Customer_Model_Customer::EXCEPTION_EMAIL_EXISTS) {
                $url = Mage::getUrl('customer/account/forgotpassword');
                $message = $this->__('There is already an account with this email address. If you are sure that it is your email address, <a href="%s">click here</a> to get your password and access your account.', $url);
                $session->setEscapeMessages(false);
            } else {
                $message = $e->getMessage();
            }
            $session->addError($message);
        } catch (Exception $e) {
            $session->setCustomerFormData($this->getRequest()->getPost())
                ->addException($e, $this->__('Cannot save the supplier.'));
        }
        $errUrl = Mage::getUrl('*/*/index', array('_secure' => true));
        $this->_redirectError($errUrl);
    }

    protected function _completeStep2($customer= null)
    {
        if (is_null($customer)) {
            return;
        }
        $postData = $this->getRequest()->getPost();
        $customer->addData($postData);
        $customer->save();

        $address = Mage::getModel("customer/address");
        $address->setCustomerId($customer->getId())
            ->setFirstname($customer->getFirstname())
            ->setMiddleName($customer->getMiddlename())
            ->setLastname($customer->getLastname())
            ->setCountryId($postData['country_id'])
            ->setPostcode($postData['zipcode'])
            ->setCompany($postData['company_name'])
            ->setCity($postData['state'])
            ->setRegion($postData['state'])
            ->setTelephone($customer->getMobileNumber())
            ->setStreet($postData['address_line_1'] .' '.$postData['address_line_2'])
            ->setIsDefaultBilling('1')
            ->setIsDefaultShipping('1')
            ->setSaveInAddressBook('1')
            ->save();

    }

    private function _uploadDocuments($customer=null)
    {
        if (is_null($customer)) {
            return;
        }
        $customer = Mage::helper('marketplacecustom')->getCustomer();

        $path = Mage::getBaseDir('media') . DS . 'documents' . DS;

        foreach($_FILES AS $k => $file) {
            if (isset($file['name']) and (file_exists($file['tmp_name']))) {
                $dir = $path . $k . '/';

                if(!is_dir($path)) {
                    mkdir($path, 0777, true);
                }

                $uploader = new Varien_File_Uploader($k);
                $uploader->setAllowRenameFiles(false);
                $uploader->setFilesDispersion(false);

                $nameSplit = explode('.', $file['name']);
                $ext = $nameSplit[count($nameSplit) - 1];
                $newName = $customer->getId(). '-' . time() . '.' . $ext;
                $customer->setData($k, $newName);
                $uploader->save($dir, $newName);
            }
        }
        $customer->save();

    }

    protected function _getCustomer()
    {
        $customer = Mage::registry('current_customer');
        if (!$customer) {
            $customer = Mage::getModel('customer/customer')->setId(null);
        }
        $customer->getGroupId();

        return $customer;
    }

    protected function _getCustomerErrors($customer)
    {
        $errors = array();
        $request = $this->getRequest();
        if ($request->getPost('create_address')) {
            $errors = $this->_getErrorsOnCustomerAddress($customer);
        }
        $customerForm = $this->_getCustomerForm($customer);
        $customerData = $customerForm->extractData($request);
        $customerErrors = $customerForm->validateData($customerData);
        if ($customerErrors !== true) {
            $errors = array_merge($customerErrors, $errors);
        } else {
            $customerForm->compactData($customerData);
            $customer->setPassword($request->getPost('password'));
            $customer->setPasswordConfirmation($request->getPost('confirmation_password'));
            $customerErrors = $customer->validate();
            if (is_array($customerErrors)) {
                $errors = array_merge($customerErrors, $errors);
            }
        }
        return $errors;
    }

    protected function _getCustomerForm($customer)
    {
        $customerForm = Mage::getModel('customer/form');
        $customerForm->setFormCode('customer_account_create');
        $customerForm->setEntity($customer);
        return $customerForm;
    }

    protected function _addSessionError($errors)
    {
        $session = $this->_getSession();
        $session->setCustomerFormData($this->getRequest()->getPost());

        if (is_array($errors)) {
            foreach ($errors as $errorMessage) {
                $session->addError($errorMessage);
            }
        } else {
            $session->addError($this->__('Invalid customer data'));
        }
    }

    protected function _successProcessRegistration(Mage_Customer_Model_Customer $customer)
    {
        $session = $this->_getSession();
        $customerHelper = $this->_getHelper('customer');
        if ($customer->isConfirmationRequired()) {
            $store = Mage::app()->getStore();
            $customer->sendNewAccountEmail(
                'confirmation',
                $session->getBeforeAuthUrl(),
                $store->getId()
            );

            $session->addSuccess($this->__('Account confirmation is required. Please, check your email for the confirmation link. To resend the confirmation email please <a href="%s">click here</a>.',
                $customerHelper->getEmailConfirmationUrl($customer->getEmail())));
            $this->_redirect(Mage::getUrl('*/index/index', array('_secure' => true)));
        } else {
            $store = Mage::app()->getStore();
            $customer->sendNewAccountEmail(
                'registered',
                '',
                $store->getId()
            );
            $session->addSuccess(
                $this->__(
                    'Thank you for registration with us.Please log in.'
                )
            );
        }
        $this->_redirect('supplierfrontendproductuploader/login');
        return $this;
    }

    protected function _welcomeCustomer(Mage_Customer_Model_Customer $customer, $isJustConfirmed = false)
    {
        $this->_getSession()->addSuccess(
            $this->__('Thank you for registering with %s.', Mage::app()->getStore()->getFrontendName())
        );

        $customer->sendNewAccountEmail(
            $isJustConfirmed ? 'confirmed' : 'registered',
            '',
            Mage::app()->getStore()->getId()
        );

        $successUrl = Mage::getUrl('*/index/index', array('_secure' => true));
        if ($this->_getSession()->getBeforeAuthUrl()) {
            $successUrl = $this->_getSession()->getBeforeAuthUrl(true);
        }
        return $successUrl;
    }

}
