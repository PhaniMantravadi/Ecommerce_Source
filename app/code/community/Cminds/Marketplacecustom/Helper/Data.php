<?php
class Cminds_Marketplacecustom_Helper_Data extends Mage_Core_Helper_Abstract {
    public function isCustomer() {
        return $this->getCustomer()->getId();
    }

    public function getCustomer() {
        return Mage::helper('customer')->getCustomer();
    }

    public function getAllCategoriesArray($optionList = false)
    {
        $categoriesArray = Mage::getModel('catalog/category')
            ->getCollection()
            ->addAttributeToSelect('name')
            ->addAttributeToSort('path', 'asc')
            ->addFieldToFilter('is_active', array('eq'=>'1'))
            ->load()
            ->toArray();

        if (!$optionList) {
            return $categoriesArray;
        }

        $categories = array();

        foreach ($categoriesArray as $categoryId => $category) {
            if (isset($category['name'])) {
                $categories[] = array(
                    'value' => $category['entity_id'],
                    'label' => Mage::helper('marketplacecustom')->__($category['name'])
                );
            }
        }

        return $categories;
    }
}
