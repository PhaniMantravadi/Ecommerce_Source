<?php
class Cminds_Marketplacecustom_Adminhtml_MarketplacesettingsController extends Mage_Adminhtml_Controller_Action
{
    public function registrationAction() {
        $this->_title($this->__('Customers'))->_title($this->__('Customer Rates'));

        $customerId = (int) $this->getRequest()->getParam('id');
        $customer = Mage::getModel('customer/customer');

        if ($customerId) {
            $customer->load($customerId);
        }

        Mage::register('current_customer', $customer);

        $this->loadLayout();
        $this->_setActiveMenu('suppliers');
        $this->getLayout()->getBlock('admin.customer.customfields');
        $this->renderLayout();
    }

    public function paymentProfileAction() {
        $this->_title($this->__('Customers'))->_title($this->__('Manage Supplier Payment Profile'));

        $customerId = (int) $this->getRequest()->getParam('id');
        $customer = Mage::getModel('customer/customer');

        if ($customerId) {
            $customer->load($customerId);
        }

        Mage::register('current_customer', $customer);

        $this->loadLayout();
        $this->getLayout()->getBlock('admin.customer.payment.profile');
        $this->renderLayout();
    }
}
