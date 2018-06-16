<?php
class Cminds_Marketplace_Block_Adminhtml_Uploader extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_controller = 'adminhtml_uploader';
        $headerSuffix= '';
        $supplierId = $this->getRequest()->getParam('id', 0);
        if ($supplierId) {
            $customer = Mage::getModel('customer/customer')->load($supplierId);
            $headerSuffix .= ' For '.$customer->getName().'('.$customer->getEmail().')';
        }
        $this->_headerText = Mage::helper('marketplace')->__("Process Product CSV Files").$headerSuffix;
        $this->_blockGroup = 'marketplace';
        parent::__construct();
        $this->_removeButton('add');
        $this->_addButton('back', array(
            'label'     => 'Back To Supplier Page',
            'onclick'   => 'setLocation(\'' . $this->getSupplierGridUrl() .'\')',
            'class'     => '',
        ));
    }

    public function getSupplierGridUrl(){
        return $this->getUrl('adminhtml/suppliers');
    }
}
