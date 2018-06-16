<?php

class Cminds_Supplierfrontendproductuploader_LoginController extends Cminds_Supplierfrontendproductuploader_Controller_Action {
    protected $forceHeader = true;
    protected $forceFooter = true;
    public function preDispatch() {
        parent::preDispatch();

        $this->_getHelper()->validateModule();
        
        if($this->_getHelper()->hasAccess()) {
            $this->getResponse()->setRedirect($this->_getHelper()->getSupplierLoginPage());
        }
    }

    public function indexAction() {

        if(Mage::getStoreConfig('supplierfrontendproductuploader_catalog/login/use_separated_login') != 1) {
            $this->getResponse()->setHeader('HTTP/1.1','404 Not Found');
            $this->getResponse()->setHeader('Status','404 File not found');
            $this->_forward('defaultNoRoute');
            return;
        }
        $this->_renderBlocks(false, true);
    }

    public function registerAction() {
        $this->_renderBlocks(false, true);
    }

    public function loginAction() {
        $session = Mage::getSingleton('customer/session');
        if(!Mage::getSingleton('customer/session')->isLoggedIn()) {
            if ($this->getRequest()->isPost()) {
                $login = $this->getRequest()->getPost();
                if (!empty($login['email']) && !empty($login['password'])) {
                    try {

                        $session->login($login['email'], $login['password']);
                        if ($session->getCustomer()->getIsJustConfirmed()) {
                            $this->_redirect(Mage::getUrl('supplierfrontendproductuploader'));
                        }
                        $this->_redirect('*');
                    } catch (Mage_Core_Exception $e) {
                        $session->addError($e->getMessage());
                        $session->setUsername($login['email']);
                        $this->_redirect('*');
                    } catch (Exception $e) {
                        $this->_redirect('*');
                    }
                } else {
                    $session->addError($this->__('Login and password are required.'));
                    $this->_redirect('*');
                }
            }
        } else {
            $helper = Mage::helper('marketplace');
            $isSupplier = $helper->isCustomerSupplier();
            if (!$isSupplier) {
                $session->addError($this->__('Sorry, You have not registered with us as a seller.'));
                $this->_redirect('supplierfrontendproductuploader/login');
                return;
            }
            if (!$helper->isSupplierUploadedDocument()) {
                $this->_redirect(Mage::getUrl('supplierfrontendproductuploader/settings/documents'));
                return;
            }
            $this->_redirect(Mage::getUrl('supplierfrontendproductuploader'));
        }
    }    
}
