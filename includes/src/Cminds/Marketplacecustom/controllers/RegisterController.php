<?php
require_once(Mage::getModuleDir('controllers','Cminds_Supplierfrontendproductuploader').DS.'RegisterController.php');
class Cminds_Marketplacecustom_RegisterController extends Cminds_Supplierfrontendproductuploader_RegisterController {


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
                $customer->save();
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
    protected function _successProcessRegistration(Mage_Customer_Model_Customer $customer)
    {
        $session = $this->_getSession();
        if ($customer->isConfirmationRequired()) {
            $store = Mage::app()->getStore();
            $customer->sendNewAccountEmail(
                'confirmation',
                $session->getBeforeAuthUrl(),
                $store->getId()
            );
            $customerHelper = $this->_getHelper('customer');
            $session->addSuccess($this->__('Account confirmation is required. Please, check your email for the confirmation link. To resend the confirmation email please <a href="%s">click here</a>.',
                $customerHelper->getEmailConfirmationUrl($customer->getEmail())));
            $url = Mage::getUrl('*/*/step2', array('_secure' => true));
        } else {
            $session->setCustomerAsLoggedIn($customer);
            $this->_welcomeCustomer($customer);
            $url = Mage::getUrl('*/*/step2', array('_secure' => true));
        }
        $this->_redirectSuccess($url);
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
            ->setStreet($postData['address_line_1'] .' '.$postData['address_line_2'])
            ->setIsDefaultBilling('1')
            ->setIsDefaultShipping('1')
            ->setSaveInAddressBook('1')
            ->save();

        $this->getResponse()->setRedirect(Mage::getUrl('*/settings/documents', array('_secure' => true)));
    }
}