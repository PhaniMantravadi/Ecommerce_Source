<?php
class Cminds_Marketplacecustom_Model_Mysql4_Setup extends Mage_Catalog_Model_Resource_Eav_Mysql4_Setup {
    protected $_customerAttributes = array();

    public function setCustomerAttributes($customerAttributes)
    {
        $this->_customerAttributes = $customerAttributes;

        return $this;
    }

    public function installCustomerAttributes()
    {
        foreach ($this->_customerAttributes as $code => $attr) {
            $this->addAttribute('customer', $code, $attr);
        }

        return $this;
    }

    public function removeCustomerAttributes()
    {
        foreach ($this->_customerAttributes as $code => $attr) {
            $this->removeAttribute('customer', $code);
        }

        return $this;
    }
}
