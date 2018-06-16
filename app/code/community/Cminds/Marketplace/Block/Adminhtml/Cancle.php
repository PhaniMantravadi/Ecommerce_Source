<?php
class Cminds_Marketplace_Block_Adminhtml_Cancle extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_controller = 'adminhtml_cancle';
        $headerSuffix= '';
        $supplierId = $this->getRequest()->getParam('id', 0);

        $this->_headerText = Mage::helper('marketplace')->__("Manage Order Cancelation Request");
        $this->_blockGroup = 'marketplace';
        parent::__construct();
        $this->_removeButton('add');

    }

}
