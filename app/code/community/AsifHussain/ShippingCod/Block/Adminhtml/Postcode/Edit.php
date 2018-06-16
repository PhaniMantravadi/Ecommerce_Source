<?php
class AsifHussain_ShippingCod_Block_Adminhtml_Postcode_Edit
    extends Mage_Adminhtml_Block_Widget_Form_Container
{
    protected function _construct()
    {
        $this->_blockGroup = 'asifhussain_shippingcod_adminhtml';
        $this->_controller = 'postcode';

        /**
         * The $_mode property tells Magento which folder to use
         * to locate the related form blocks to be displayed in
         * this form container. In our example, this corresponds
         * to ShippingCod/Block/Adminhtml/Postcode/Edit/.
         */
        $this->_mode = 'edit';

        $newOrEdit = $this->getRequest()->getParam('id')
            ? $this->__('Edit')
            : $this->__('New');
        $this->_headerText =  $newOrEdit . ' ' . $this->__('Postcode');
    }
}