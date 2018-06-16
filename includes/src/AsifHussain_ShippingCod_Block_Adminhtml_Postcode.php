<?php
class AsifHussain_ShippingCod_Block_Adminhtml_Postcode
    extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    protected function _construct()
    {
        parent::_construct();

        /**
         * The $_blockGroup property tells Magento which alias to use to
         * locate the blocks to be displayed in this grid container.
         * In our example, this corresponds to PostcodeDirectory/Block/Adminhtml.
         */
        $this->_blockGroup = 'asifhussain_shippingcod_adminhtml';

        /**
         * $_controller is a slightly confusing name for this property.
         * This value, in fact, refers to the folder containing our
         * Grid.php and Edit.php - in our example,
         * PostcodeDirectory/Block/Adminhtml/Postcode. So, we'll use 'postcode'.
         */
        $this->_controller = 'postcode';

        /**
         * The title of the page in the admin panel.
         */
        $this->_headerText = Mage::helper('asifhussain_shippingcod')
            ->__('Postcode List');
    }

    public function getCreateUrl()
    {
        /**
         * When the "Add" button is clicked, this is where the user should
         * be redirected to - in our example, the method editAction of
         * PostcodeController.php in PostcodeDirectory module.
         */
        return $this->getUrl(
            'asifhussain_shippingcod_admin/postcode/edit'
        );
    }
}