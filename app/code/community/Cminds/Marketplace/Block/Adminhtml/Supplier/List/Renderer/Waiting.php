<?php

class Cminds_Marketplace_Block_Adminhtml_Supplier_List_Renderer_Waiting
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        $waiting = $row->getData('supplier_profile_approved');

        return $waiting == 1 ? "<span>".$this->__('No')."</span>" : "<span style='color:red;'>".$this->__('Yes')."</span>";
    }
}

?>