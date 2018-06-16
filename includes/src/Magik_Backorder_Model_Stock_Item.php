<?php
class Magik_Backorder_Model_Stock_Item extends Mage_CatalogInventory_Model_Stock_Item{


public function checkQty($qty)
{ 

    if (!$this->getManageStock() || Mage::app()->getStore()->isAdmin()) {
 
        return true;
    }

    if ($this->getQty() - $this->getMinQty() - $qty < 0) {
      
    }
    return true;
}


}  
