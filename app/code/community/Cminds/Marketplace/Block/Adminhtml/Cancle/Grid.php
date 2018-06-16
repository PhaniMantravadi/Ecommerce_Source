<?php
class Cminds_Marketplace_Block_Adminhtml_Cancle_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct(){
        parent::__construct();
        $this->setId('orderCancleGrid');
        $this->setUseAjax(true);
        $this->setDefaultDir('ASC');
        $this->setDefaultSort('id');
        $this->setSaveParametersInSession(true);
        $this->_emptyText = Mage::helper('marketplace')->__('No Records Found.');
    }


    protected function _prepareCollection(){
        $collection = Mage::getModel('marketplace/cancle')->getCollection();
        $prefix = Mage::getConfig()->getTablePrefix();
        $fnameid = Mage::getModel("eav/entity_attribute")->loadByCode("1", "firstname")->getAttributeId();
        $lnameid = Mage::getModel("eav/entity_attribute")->loadByCode("1", "lastname")->getAttributeId();
        $supplierAttribute = Mage::getResourceModel('catalog/product')->getAttribute(
            'creator_id'
        );
        $productTable = Mage::getResourceModel('catalog/product')->getEntityTable();
        $collection->getSelect()
            ->joinLeft(
                array("so" => $prefix."sales_flat_order"),
                "so.entity_id = main_table.order_id",
                array("increment_id" => "so.increment_id")
            );
        $collection->getSelect()
            ->joinLeft(
                array("soe" => $prefix."sales_flat_order_item"),
                "soe.item_id = main_table.order_item_id",
                array("product_name" => "soe.name", "sku"=>"soe.sku")
            );
        $collection->getSelect()
            ->joinLeft(
                array("prt" => $productTable.'_'.$supplierAttribute->getBackendType()),
                "soe.product_id = prt.entity_id AND prt.attribute_id = ".$supplierAttribute->getId(),
                array("seller_id" => "prt.value")
            );
        $collection->getSelect()
                ->joinLeft(array("ce1" => $prefix."customer_entity_varchar"),"ce1.entity_id = prt.value AND ce1.attribute_id =".$fnameid,array("fname" => "value"))
                ->joinLeft(array("ce2" => $prefix."customer_entity_varchar"),"ce2.entity_id = prt.value AND ce2.attribute_id = ".$lnameid,array("lname" => "value"))
                ->columns(new Zend_Db_Expr("CONCAT(`ce1`.`value`, ' ',`ce2`.`value`) AS supplier_fullname"));

        $collection->getSelect()
            ->joinLeft(
                array("ce" => $prefix."customer_entity"),
                "ce.entity_id = prt.value",
                array("ce.email as supplier_email")
            );

        $collection->getSelect()
            ->joinLeft(
                array("ceo" => $prefix."customer_entity"),
                "ceo.entity_id = so.customer_id",
                array("ceo.email as customer_email")
            );
        $collection->getSelect()
            ->joinLeft(array("ceo1" => $prefix."customer_entity_varchar"),"ceo1.entity_id =  so.customer_id AND ceo1.attribute_id =".$fnameid,array())
            ->joinLeft(array("ceo2" => $prefix."customer_entity_varchar"),"ceo2.entity_id =  so.customer_id AND ceo2.attribute_id = ".$lnameid,array())
            ->columns(new Zend_Db_Expr("CONCAT(`ceo1`.`value`, ' ',`ceo2`.`value`) AS customer_fullname"));


        $collection->addFilterToMap("fullname","`ce1`.`value`");
        $this->setCollection($collection);
        parent::_prepareCollection();
    }

    protected function _prepareColumns() {
        $this->addColumn('id', array(
            'header'    => Mage::helper('marketplace')->__('ID'),
            'width'     => '50px',
            'index'     => 'id',
            'type'  => 'number',
            'filter_index' => 'main_table.id'
        ));
       
        $this->addColumn('supplier_name', array(
            'header'    => Mage::helper('marketplace')->__('Vendor Name'),
            'index'     => 'supplier_fullname',
            'type'  => 'text',
            'filter_index' => new Zend_Db_Expr(
                'CONCAT(`ce1`.`value`, " ",`ce2`.`value`)'
            ),
        ));

        $this->addColumn('supplier_email', array(
            'header'    => Mage::helper('marketplace')->__('Vendor Email'),
            'index'     => 'supplier_email',
            'type'  => 'text',
            'filter_index' => 'ce.email'

        ));

        $this->addColumn('customer_name', array(
            'header'    => Mage::helper('marketplace')->__('Customer Name'),
            'index'     => 'customer_fullname',
            'type'  => 'text',
            'filter_index' => new Zend_Db_Expr(
                'CONCAT(`ceo1`.`value`, " ",`ceo2`.`value`)'
            ),
        ));

        $this->addColumn('customer_email', array(
            'header'    => Mage::helper('marketplace')->__('Customer Email'),
            'index'     => 'customer_email',
            'type'  => 'text',
            'filter_index' =>'ceo.email'
        ));

        $this->addColumn('increment_id', array(
            'header'    => Mage::helper('marketplace')->__('Order Number'),
            'index'     => 'increment_id',
            'type'  => 'text',
            'filter_index' => 'so.increment_id'
        ));
        $this->addColumn('product_name', array(
            'header'    => Mage::helper('marketplace')->__('Product Name'),
            'index'     => 'product_name',
            'type'  => 'text',
            'filter_index' => 'soe.name'
        ));
        $this->addColumn('sku', array(
            'header'    => Mage::helper('marketplace')->__('SKU'),
            'index'     => 'sku',
            'type'  => 'text',
            'filter_index' => 'soe.sku'
        ));

        $this->addColumn('requestor', array(
            'header'    => Mage::helper('marketplace')->__('Requestor '),
            'index'     => 'is_supplier_request',
            'type'      => 'options',
            'options'   => array(
                Cminds_Marketplace_Model_Cancle::STATE_COMPLETE => 'Supplier',
                Cminds_Marketplace_Model_Cancle::STATE_PARTIAL => 'Customer',
            ),
            "align"     => "center"
            // "sortable"  => false
        ));

        $this->addColumn('status', array(
            'header'    => Mage::helper('marketplace')->__('Status'),
            'index'     => 'status',
            'filter_index' => 'main_table.status',
            'type'      => 'options',
            'options'   => array(
                Cminds_Marketplace_Model_Cancle::STATUS_PENDING => 'Pending',
                Cminds_Marketplace_Model_Cancle::STATUS_COMPLETE => 'Complete',
                Cminds_Marketplace_Model_Cancle:: STATUS_REJECTED => 'Rejected'
            ),
            'frame_callback' => array($this, 'decorateStatus'),
            "align"     => "center"
            // "sortable"  => false
        ));

        return parent::_prepareColumns();
    }

    public function decorateStatus($value, $row, $column, $isExport)
    {
        $class = '';
        switch ($row->getStatus()) {
            case Cminds_Marketplace_Model_Cancle::STATUS_PENDING :
                $class = 'grid-severity-major';
                break;
            case Cminds_Marketplace_Model_Cancle::STATUS_COMPLETE :
                $class = 'grid-severity-notice';
                break;
            case Cminds_Marketplace_Model_Cancle::STATUS_REJECTED:
                $class = 'grid-severity-critical';
                break;
        }
        return '<span class="'.$class.'"><span>'.$value.'</span></span>';
    }

    protected function _prepareMassaction()  {
        $this->setMassactionIdField('main_table.id');
        $this->getMassactionBlock()->setFormFieldName('id');
        $this->getMassactionBlock()->addItem('cancle_order', array(
            'label'    => Mage::helper('marketplace')->__('Cancel Order'),
            'url'      => $this->getUrl(
                'adminhtml/suppliers/cancel'
            )
        ));
        $this->getMassactionBlock()->addItem('reject_request', array(
           'label'    => Mage::helper('marketplace')->__('Reject Request'),
           'url'      => $this->getUrl(
               'adminhtml/suppliers/reject'
           )
        ));
        return $this;
    }

    public function getGridUrl(){
        return $this->getUrl("*/*/ordercanclegrid",array("_current"=>true));
    }


}