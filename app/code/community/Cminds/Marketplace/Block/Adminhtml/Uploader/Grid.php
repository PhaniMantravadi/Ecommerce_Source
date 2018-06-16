<?php
class Cminds_Marketplace_Block_Adminhtml_Uploader_Grid extends Mage_Adminhtml_Block_Widget_Grid {
    protected $_supplierId = null;

    public function __construct(){
        parent::__construct();
        $this->setId('supplierGrid');
        $this->setUseAjax(true);
        $this->setDefaultDir('ASC');
        $this->setDefaultSort('id');
        $this->_setSupplierId();
        if (!$this->_supplierId) {
            $this->setDefaultFilter(array('status' => 0));
        }
        $this->setSaveParametersInSession(true);
        $this->_emptyText = Mage::helper('marketplace')->__('No Records Found.');
    }

    private function _setSupplierId() {
        $this->_supplierId = $this->getRequest()->getParam('id', 0);
        return $this->_supplierId;
    }
    protected function _prepareCollection(){
        $collection = Mage::getModel('marketplace/uploader')->getCollection();
        $prefix = Mage::getConfig()->getTablePrefix();
        $fnameid = Mage::getModel("eav/entity_attribute")->loadByCode("1", "firstname")->getAttributeId();
        $lnameid = Mage::getModel("eav/entity_attribute")->loadByCode("1", "lastname")->getAttributeId();
        $collection->getSelect()
                ->join(array("ce1" => $prefix."customer_entity_varchar"),"ce1.entity_id = main_table.supplier_id",array("fname" => "value"))->where("ce1.attribute_id = ".$fnameid)
                ->join(array("ce2" => $prefix."customer_entity_varchar"),"ce2.entity_id = main_table.supplier_id",array("lname" => "value"))->where("ce2.attribute_id = ".$lnameid)
                ->columns(new Zend_Db_Expr("CONCAT(`ce1`.`value`, ' ',`ce2`.`value`) AS fullname"));
        if (!$this->_supplierId) {
            $collection->getSelect()
                ->join(
                    array("ce" => $prefix."customer_entity"),
                    "ce.entity_id = main_table.supplier_id",
                    array("email" => "email")
                );
        }

        $collection->addFilterToMap("fullname","`ce1`.`value`");
        if ($this->_supplierId) {
            $collection->getSelect()->where('supplier_id = '.$this->_supplierId);
        }
        $this->setCollection($collection);
        parent::_prepareCollection();
    }

    protected function _prepareColumns() {
        $this->addColumn('file_id', array(
            'header'    => Mage::helper('marketplace')->__('ID'),
            'width'     => '50px',
            'index'     => 'id',
            'type'  => 'number',
            'filter_index' => 'main_table.id'
        ));
       
        $this->addColumn('customer_name', array(
            'header'    => Mage::helper('marketplace')->__('Vendor Name'),
            'index'     => 'fullname',
            'type'  => 'text',
        ));
        if (!$this->_supplierId) {
            $this->addColumn('email', array(
                'header'    => Mage::helper('marketplace')->__('Vendor Email'),
                'index'     => 'email',
                'type'  => 'text',
            ));
        }
        $this->addColumn('attr_set', array(
            'header'    => Mage::helper('marketplace')->__('Attribute Set'),
            'index'     => 'attribute_set',
            'type'  => 'options',
            'options'   =>$this->getAllowedSets()
        ));
        $this->addColumn('file_type', array(
            'header'    => Mage::helper('marketplace')->__('File Type'),
            'index'     => 'file_type',
            'type'      => 'options',
            'options'   => array(1 => 'Product Data File', 2 => 'Attribute Data File'),
            "align"     => "center"
            // "sortable"  => false
        ));
         $this->addColumn('file_name', array(
             'header'    => Mage::helper('marketplace')->__('File'),
             'index'     => 'file_name',
             'type'  => 'text',
        ));
        $this->addColumn('status', array(
            'header'    => Mage::helper('marketplace')->__('Status'),
            'index'     => 'status',
            'type'      => 'options',
            'options'   => array(0 => 'Pending', 1=> 'Approved', 2=>'Working', 3=>'Complete', '4'=> 'Error'),
            'frame_callback' => array($this, 'decorateStatus'),
            "align"     => "center"
            // "sortable"  => false
        ));
        $this->addColumn('action',
            array(
                'header'    =>  Mage::helper('customer')->__('Action'),
                'width'     => '100',
                'type'      => 'action',
                'getter'    => 'getId',
                'actions'   => array(
                    array(
                        'caption'   => Mage::helper('customer')->__('Download File'),
                        'url'       => array('base'=> 'adminhtml/suppliers/downloadcsv'),
                        'field'     => 'file_id',
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
                'is_system' => true,
            ));
        return parent::_prepareColumns();
    }
    public function getAllowedSets(){
        $entityTypeId = Mage::getModel('eav/entity')
            ->setType('catalog_product')
            ->getTypeId();
        $data=array();
        $entityType = Mage::getModel('catalog/product')->getResource()->getTypeId();
        $attributeSetCollection = Mage::getResourceModel('eav/entity_attribute_set_collection')
            ->setEntityTypeFilter($entityType)->addFieldToFilter('available_for_supplier', 1);

        foreach($attributeSetCollection as $_attributeSet){
            $data[$_attributeSet->getData('attribute_set_id')] = $_attributeSet->getData('attribute_set_name');
        }
        return $data;
    }
    public function decorateStatus($value, $row, $column, $isExport)
    {
        $class = '';
        switch ($row->getStatus()) {
            case Cminds_Marketplace_Helper_Uploader::PENDING :
                $class = 'grid-severity-notice';
                break;
            case Cminds_Marketplace_Helper_Uploader::APPROVED :
                $class = 'grid-severity-notice';
                break;
            case Cminds_Marketplace_Helper_Uploader::WORKING :
                $class = 'grid-severity-major';
                break;
            case Cminds_Marketplace_Helper_Uploader::COMPLETE :
                $class = 'grid-severity-notice';
                break;
            case Cminds_Marketplace_Helper_Uploader::ERROR:
                $class = 'grid-severity-critical';
                break;
        }
        return '<span class="'.$class.'"><span>'.$value.'</span></span>';
    }

    protected function _prepareMassaction()  {
        $this->setMassactionIdField('main_table.fileid');
        $this->getMassactionBlock()->setFormFieldName('file_id');
        $supplierId = $this->getRequest()->getParam('id', 0);
        $this->getMassactionBlock()->addItem('approved_for_processing', array(
            'label'    => Mage::helper('marketplace')->__('Approved For Processing'),
            'url'      => $this->getUrl(
                'adminhtml/suppliers/approvefiles',
                array('supplier_id'=>$supplierId)
            )
        ));
        $this->getMassactionBlock()->addItem('process', array(
           'label'    => Mage::helper('marketplace')->__('Process Files'),
           'url'      => $this->getUrl(
               'adminhtml/suppliers/process', array('supplier_id'=>$supplierId)
           )
        ));
        return $this;
    }

    public function getGridUrl(){
        return $this->getUrl("*/*/processcsvgrid",array("_current"=>true));
    }


}