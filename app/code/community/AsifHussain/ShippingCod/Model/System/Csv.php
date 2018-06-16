<?php
class AsifHussain_ShippingCod_Model_System_Csv extends Mage_Core_Model_Config_Data
{
    public function _afterSave()
    {
        Mage::getResourceModel('asifhussain_shippingcod/postcode_import')->uploadAndImport($this);
    }
}
?>