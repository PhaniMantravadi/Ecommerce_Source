<?php

class Cminds_Marketplace_Block_Return_View extends Sz_Rma_Block_Form
{
    /**
     * Values for each visible attribute
     * @var array
     */
    protected $_realValueAttributes = array();
    protected $_rmaEntityId = null;

    public function _construct()
    {
        parent::_construct();
        $this->setTemplate('marketplace/return/view.phtml');
        $this->_setRMAEntityId();

        /** @var $collection Sz_Rma_Model_Resource_Item */
        $collection = Mage::getResourceModel('sz_rma/item_collection')
            ->addAttributeToSelect('*')
            ->addFilter('rma_entity_id', $this->getRma()->getEntityId())
        ;
        $productTable = Mage::getResourceModel('catalog/product')->getEntityTable();
        $creatorAttribute = Mage::getResourceModel('catalog/product')->getAttribute(
            'creator_id'
        );
        $supplierId = Mage::getSingleton('customer/session')->getId();
        $collection->getSelect()->joinInner(
            array('cp' => $productTable),
            "cp.sku = e.product_sku",
            array('')
        );

        $collection->getSelect()->joinInner(
            array('ct' => $productTable.'_'.$creatorAttribute->getBackendType()),
            'ct.entity_id = cp.entity_id AND ct.value = '.$supplierId.' AND
            ct.attribute_id = '.$creatorAttribute->getId(),
            array('')
        );


        $this->setItems($collection);

        /** @var $comments Sz_Rma_Model_Resource_Rma_Status_History_Collection */
        $comments = Mage::getResourceModel('sz_rma/rma_status_history_collection')
            ->addFilter('rma_entity_id', $this->getRma()->getEntityId())
        ;
        $this->setComments($comments);
    }

    private function _setRMAEntityId() {
        $entityId = (int) Mage::app()->getRequest()->getParam('entity_id', 0);
        if ($entityId) {
            $this->_rmaEntityId = $entityId;
            $this->setRma(Mage::getModel('sz_rma/rma')->load($entityId));
            $this->setOrder(Mage::getModel('sales/order')->load(
               $this->getRma()->getOrderId()
            ));
        }
    }
    /**
     * Returns attributes that static
     *
     * @return array
     */
    public function getAttributeFilter()
    {
        $array = array();

        /** @var $collection Sz_Rma_Model_Resource_Item */
        $collection = Mage::getResourceModel('sz_rma/item_collection')
            ->addFilter('rma_entity_id', $this->getRma()->getEntityId())
        ;
        foreach ($collection as $item) {
            foreach ($item->getData() as $attributeCode=>$value) {
                $array[] = $attributeCode;
            }
            break;
        }

        /* @var $itemModel Sz_Rma_Model_Item */
        $itemModel = Mage::getModel('sz_rma/item');

        /* @var $itemForm Sz_Rma_Model_Item_Form */
        $itemForm   = Mage::getModel('sz_rma/item_form');
        $itemForm->setFormCode('default')
            ->setStore($this->getStore())
            ->setEntity($itemModel);

        // add system required attributes
        foreach ($itemForm->getSystemAttributes() as $attribute) {
            /* @var $attribute Sz_Rma_Model_Item_Attribute */
            if ($attribute->getIsVisible()) {
                $array[] = $attribute->getAttributeCode();
            }
        }

        return $array;
    }

    /**
     * Gets values for each visible attribute
     *
     * $excludeAttr is optional array of attribute codes to
     * exclude them from additional data array
     *
     * @param array $excludeAttr
     * @return array
     */
    protected function _getAdditionalData(array $excludeAttr = array())
    {
        $data       = array();

        $items      = $this->getItems();

        $itemForm   = false;

        foreach ($items as $item) {
            if (!$itemForm) {
                /* @var $itemForm Sz_Rma_Model_Item_Form */
                $itemForm   = Mage::getModel('sz_rma/item_form');
                $itemForm->setFormCode('default')
                    ->setStore($this->getStore())
                    ->setEntity($item);
            }
            foreach ($itemForm->getAttributes() as $attribute) {
                $code = $attribute->getAttributeCode();
                if ($attribute->getIsVisible() && !in_array($code, $excludeAttr)) {
                    $value = $attribute->getFrontend()->getValue($item);
                    $data[$item->getId()][$code] = array(
                        'label' => $attribute->getStoreLabel(),
                        'value' => $value,
                        'html'  => ''
                    );
                    if ($attribute->getFrontendInput() == 'image') {
                        $data[$item->getId()][$code]['html'] = $this->setEntity($item)
                            ->getAttributeHtml($attribute);
                    }
                }
            }
        }

        return $data;
    }

    /**
     * Gets attribute value by rma item id and attribute code
     *
     * @param  $itemId
     * @param  $attributeCode
     * @return string
     */
    public function getAttributeValue($itemId, $attributeCode)
    {
        if (empty($this->_realValueAttributes)) {
            $this->_realValueAttributes = $this->_getAdditionalData();
        }

        if (!empty($this->_realValueAttributes[$itemId][$attributeCode]['html'])) {
            $html = $this->_realValueAttributes[$itemId][$attributeCode]['html'];
        } else {
            $html = $this->escapeHtml($this->_realValueAttributes[$itemId][$attributeCode]['value']);
        }
        return $html;
    }
    /**
     * Gets values for each visible attribute depending on item id
     *
     * @param null|int $itemId
     * @return array
     */
    public function getRealValueAttributes($itemId = null) {
        if (empty($this->_realValueAttributes)) {
            $this->_realValueAttributes = $this->_getAdditionalData();
        }
        if ($itemId && isset($this->_realValueAttributes[$itemId])) {
            return $this->_realValueAttributes[$itemId];
        } else {
            return $this->_realValueAttributes;
        }
    }

    /**
     * Gets attribute label by rma item id and attribute code
     *
     * @param  $itemId
     * @param  $attributeCode
     * @return string | bool
     */
    public function getAttributeLabel($itemId, $attributeCode)
    {
        if (empty($this->_realValueAttributes)) {
            $this->_realValueAttributes = $this->_getAdditionalData();
        }

        if (isset($this->_realValueAttributes[$itemId][$attributeCode])) {
            return $this->_realValueAttributes[$itemId][$attributeCode]['label'];
        }

        return false;
    }

    /**
     * Gets item options
     *
     * @param  $item Sz_Rma_Model_Item
     * @return array | bool
     */
    public function getItemOptions($item)
    {
        return $item->getOptions();
    }

    public function getOrderUrl($rma)
    {
        return $this->getUrl('sales/order/view/', array('order_id' => $rma->getOrderId()));
    }

    public function getBackUrl()
    {
        if (Mage::getSingleton('customer/session')->isLoggedIn()) {
            return $this->getUrl('rma/return/history');
        } else {
            return $this->getUrl('rma/guest/returns');
        }
    }

    public function getAddress()
    {
        return  Mage::helper('sz_rma')->getReturnAddress();
    }

    public function getSubmitUrl()
    {
        return $this->getUrl('*/*/addComment', array('entity_id' => (int)$this->getRequest()->getParam('entity_id')));
    }

    public function getCustomerName()
    {
        if (Mage::getSingleton('customer/session')->isLoggedIn()) {
            return Mage::helper('customer')->getCustomerName();
        } else {
            $billingAddress = Mage::registry('current_order')->getBillingAddress();

            $name = '';
            $config = Mage::getSingleton('eav/config');
            if ($config->getAttribute('customer', 'prefix')->getIsVisible() && $billingAddress->getPrefix()) {
                $name .= $billingAddress->getPrefix() . ' ';
            }
            $name .= $billingAddress->getFirstname();
            if ($config->getAttribute('customer', 'middlename')->getIsVisible() && $billingAddress->getMiddlename()) {
                $name .= ' ' . $billingAddress->getMiddlename();
            }
            $name .=  ' ' . $billingAddress->getLastname();
            if ($config->getAttribute('customer', 'suffix')->getIsVisible() && $billingAddress->getSuffix()) {
                $name .= ' ' . $billingAddress->getSuffix();
            }
            return $name;
        }
    }

    /**
     * Get html data of tracking info block. Namely list of rows in table
     *
     * @return string
     */
    public function getTrackingInfo()
    {
       return $this->getBlockHtml('rma.return.tracking');
    }

    /**
     * Get collection of tracking numbers of RMA
     *
     * @return Sz_Rma_Model_Resource_Shipping_Collection
     */
    public function getTrackingNumbers()
    {
        return $this->getRma()->getTrackingNumbers();
    }

    /**
     * Get shipping label of RMA
     *
     * @return Sz_Rma_Model_Shipping
     */
    public function getShippingLabel()
    {
        return $this->getRma()->getShippingLabel();
    }

    /**
     * Get shipping label of RMA
     *
     * @return Sz_Rma_Model_Shipping
     */
    public function canShowButtons()
    {
        return (bool)(
            $this->getShippingLabel()->getId()
            && (!($this->getRma()->getStatus() == Sz_Rma_Model_Rma_Source_Status::STATE_CLOSED
                || $this->getRma()->getStatus() == Sz_Rma_Model_Rma_Source_Status::STATE_PROCESSED_CLOSED))
        );
    }


    /**
     * Get print label button html
     *
     * @return string
     */
    public function getPrintLabelButton()
    {
        $data['id'] = $this->getRma()->getId();
        $url = $this->getUrl('*/rma/printLabel', $data);
        return $this->getLayout()
            ->createBlock('core/html_link')
            ->setData(array(
                'label'   => Mage::helper('sz_rma')->__('Print Shipping Label'),
                'onclick' => 'setLocation(\'' . $url . '\')'
            ))
            ->setAnchorText(Mage::helper('sz_rma')->__('Print Shipping Label'))
            ->toHtml();
    }

    /**
     * Show packages button html
     *
     * @return string
     */
    public function getShowPackagesButton()
    {
        return $this->getLayout()
            ->createBlock('core/html_link')
            ->setData(array(
                'href'      => "javascript:void(0)",
                'title'     => Mage::helper('sz_rma')->__('Show Packages'),
                'onclick'   => "popWin(
                        '".$this->helper('sz_rma')->getPackagePopupUrlByRmaModel($this->getRma())."',
                        'package',
                        'width=800,height=600,top=0,left=0,resizable=yes,scrollbars=yes'); return false;"
            ))
            ->setAnchorText(Mage::helper('sz_rma')->__('Show Packages'))
            ->toHtml();
    }

    /**
     * Show print shipping label html
     *
     * @return string
     */
    public function getPrintShippingLabelButton()
    {
        return $this->getLayout()
            ->createBlock('core/html_link')
            ->setData(array(
                'href'      => $this->helper('sz_rma')->getPackagePopupUrlByRmaModel(
                    $this->getRma(),
                    'printlabel'
                ),
                'title'     => Mage::helper('sz_rma')->__('Print Shipping Label'),
            ))
            ->setAnchorText(Mage::helper('sz_rma')->__('Print Shipping Label'))
            ->toHtml();
    }

    /**
     * Get list of shipping carriers for select
     *
     * @return array
     */
    public function getCarriers()
    {
        return Mage::helper('sz_rma')->getShippingCarriers($this->getRma()->getStoreId());
    }

    /**
     * Get url for add label action
     *
     * @return string
     */
    public function getAddLabelUrl()
    {
        return $this->getUrl('*/*/addLabel/', array('entity_id' => $this->getRma()->getEntityId()));
    }

    /**
     * Get whether rma and allowed
     *
     * @return bool
     */
    public function isPrintShippingLabelAllowed()
    {
        return $this->getRma()->isAvailableForPrintLabel();
    }

    public function getSupplierName($supplierId = null)
    {
        if (is_null($supplierId)) {
            return false;
        }
        return Mage::getModel('customer/customer')->load($supplierId)->getName();
    }
}
