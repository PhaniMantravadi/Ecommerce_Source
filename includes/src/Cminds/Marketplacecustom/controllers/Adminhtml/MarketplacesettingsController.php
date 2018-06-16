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
        $this->_addContent($this->getLayout()->createBlock('marketplace/adminhtml_supplier_customfields'))
            ->renderLayout();
    }
}
