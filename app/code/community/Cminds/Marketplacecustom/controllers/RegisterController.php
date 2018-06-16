<?php
require_once(Mage::getModuleDir('controllers','Cminds_Supplierfrontendproductuploader').DS.'RegisterController.php');
class Cminds_Marketplacecustom_RegisterController extends Cminds_Supplierfrontendproductuploader_RegisterController {

    /*
     * Check the email if its already registered
     */
    public function checkEmailAction()
    {
        $bool = 0;
        $email = $this->getRequest()->getParam('email');
        $websiteId = Mage::app()->getWebsite()->getId();
        $customer = Mage::getModel('customer/customer');
        $customer->setWebsiteId($websiteId);
        $customer->loadByEmail($email);
        if ($customer->getId()) {
            $bool = 1;
        }
        $info =  array( "status" => $bool);
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($info));
    }

    public function createPostAction()
    {
        if(!$this->_getHelper()->canRegister()) {
            $this->getResponse()->setHeader('HTTP/1.1','404 Not Found');
            $this->getResponse()->setHeader('Status','404 File not found');
            $this->_forward('defaultNoRoute');
            return;
        }
        $session = Mage::getSingleton('customer/session');
        if ($session->isLoggedIn()) {
            $this->_redirect('*/*/');
            return;
        }
        $session->setEscapeMessages(true); // prevent XSS injection in user input
        if (!$this->getRequest()->isPost()) {
            $errUrl = Mage::getUrl('*/*/index', array('_secure' => true));
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
                $customer->addData($_POST);
                $customer->setGroupId(5);
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
            ->setLastname($customer->getLastname())
            ->setCountryId($postData['registered']['country'])
            ->setPostcode($postData['registered']['zipcode'])
            ->setCompany($postData['registered']['company_name'])
            ->setCity($postData['registered']['city'])
            ->setRegion($postData['registered']['region'])
            ->setRegionId($postData['registered']['region_id'])
            ->setTelephone($customer->getMobileNumber())
            ->setStreet($postData['registered']['address_line_1'] .' '.$postData['registered']['address_line_2'])
            ->setIsDefaultBilling('1')
            ->setIsDefaultShipping('0')
            ->setSaveInAddressBook('1')
            ->save();
        $address->setCustomerId($customer->getId())
            ->setFirstname($customer->getFirstname())
            ->setLastname($customer->getLastname())
            ->setCountryId($postData['branch']['country'])
            ->setPostcode($postData['branch']['zipcode'])
            ->setCompany($postData['branch']['company_name'])
            ->setCity($postData['branch']['city'])
            ->setRegion($postData['branch']['region'])
            ->setRegionId($postData['branch']['region_id'])
            ->setTelephone($customer->getMobileNumber())
            ->setStreet($postData['branch']['address_line_1'] .' '.$postData['branch']['address_line_2'])
            ->setIsDefaultBilling('0')
            ->setIsDefaultShipping('1')
            ->setSaveInAddressBook('1')
            ->save();

    }

    private function _uploadDocuments($customer=null)
    {
        if (is_null($customer)) {
            return;
        }
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

    public function step2Action() {
        if(!Mage::helper('marketplacecustom')->isCustomer()) {
            $this->norouteAction();
            return;
        }
        $this->_renderBlocks();
    }

    public function createpoststep2Action() {
        if(!Mage::helper('marketplacecustom')->isCustomer()) {
            $this->norouteAction();
            return;
        }
        $postData = $this->getRequest()->getPost();

        $customer = Mage::helper('marketplacecustom')->getCustomer();
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
            ->setStreet($postData['address_line_1'] .','.$postData['address_line_2'])
            ->setIsDefaultBilling('1')
            ->setIsDefaultShipping('1')
            ->setSaveInAddressBook('1')
            ->save();

        $this->getResponse()->setRedirect(Mage::getUrl('*/settings/documents', array('_secure' => true)));
    }
}