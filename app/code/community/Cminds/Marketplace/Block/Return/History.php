<?php

class Cminds_Marketplace_Block_Return_History extends Mage_Core_Block_Template
{
    public function _construct()
    {
        parent::_construct();
        $this->setTemplate('marketplace/return/history.phtml');
        $productTable = Mage::getResourceModel('catalog/product')->getEntityTable();
        $rmaItemTable = Mage::getResourceModel('sz_rma/item')->getEntityTable();
        $creatorAttribute = Mage::getResourceModel('catalog/product')->getAttribute(
            'creator_id'
        );
        $supplierId = Mage::getSingleton('customer/session')->getId();
        $returns = Mage::getResourceModel('sz_rma/rma_grid_collection')
            ->addFieldToSelect('*')
            ->setOrder('date_requested', 'desc')
        ;

        $returns->getSelect()->joinInner(
            array('ri' => $rmaItemTable),
            "ri.rma_entity_id = main_table.entity_id",
            array('')
        );

        $returns->getSelect()->joinInner(
            array('cp' => $productTable),
            "cp.sku = ri.product_sku",
            array('')
        );

        $returns->getSelect()->joinInner(
            array('ct' => $productTable.'_'.$creatorAttribute->getBackendType()),
            'ct.entity_id = cp.entity_id AND ct.value = '.$supplierId.' AND
            ct.attribute_id = '.$creatorAttribute->getId(),
            array('')
        );
        $returns->getSelect()->group('main_table.entity_id');
        $this->setReturns($returns);
    }

    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        $pager = $this->getLayout()
            ->createBlock('page/html_pager', 'sales.order.history.pager')
            ->setCollection($this->getReturns());
        $this->setChild('pager', $pager);
        $this->getReturns()->load();
        return $this;
    }

    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    public function getViewUrl($return)
    {
        return $this->getUrl('*/*/view', array('entity_id' => $return->getId()));
    }

    public function getBackUrl()
    {
        return $this->getUrl('customer/account/');
    }
}
