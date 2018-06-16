<?php

class Magestore_Productfile_Block_Adminhtml_Productfile_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
      parent::__construct();
      $this->setId('productfileGrid');
      $this->setDefaultSort('productfile_id');
      $this->setDefaultDir('ASC');
      $this->setSaveParametersInSession(true);
  }

  protected function _prepareCollection()
  {
      $collection = Mage::getModel('productfile/productfile')->getCollection();
      $this->setCollection($collection);
      return parent::_prepareCollection();
  }

  protected function _prepareColumns()
  {
      $this->addColumn('productfile_id', array(
          'header'    => Mage::helper('productfile')->__('ID'),
          'align'     =>'right',
          'width'     => '50px',
          'index'     => 'productfile_id',
      ));

      $this->addColumn('title', array(
          'header'    => Mage::helper('productfile')->__('Name'),
          'align'     =>'left',
          'index'     => 'title',
      ));
	  
	  $this->addColumn('filename', array(
          'header'    => Mage::helper('productfile')->__('File Name'),
          'align'     =>'left',
          'index'     => 'filename',
	  ));
	  
	  $this->addColumn('fileextension', array(
          'header'    => Mage::helper('productfile')->__('File Extension'),
          'align'     =>'left',
          'index'     => 'fileextension',
      ));
	  
	  	  
	  $this->addColumn('filesize', array(
          'header'    => Mage::helper('productfile')->__('File Size'),
		  'align'     =>'left',
		  'type'	  => 'number',
          'index'     => 'filesize',
      ));
	  
	  
	  $this->addColumn('status', array(
          'header'    => Mage::helper('productfile')->__('Status'),
          'align'     => 'left',
          'width'     => '80px',
          'index'     => 'status',
          'type'      => 'options',
          'options'   => array(
              1 => 'Enabled',
              2 => 'Disabled',
          ),
      ));
	  
        $this->addColumn('action',
            array(
                'header'    =>  Mage::helper('productfile')->__('Action'),
                'width'     => '100',
                'type'      => 'action',
                'getter'    => 'getId',
                'actions'   => array(
                    array(
                        'caption'   => Mage::helper('productfile')->__('Edit'),
                        'url'       => array('base'=> '*/*/edit'),
                        'field'     => 'id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
                'is_system' => true,
        ));
		
		$this->addExportType('*/*/exportCsv', Mage::helper('productfile')->__('CSV'));
		$this->addExportType('*/*/exportXml', Mage::helper('productfile')->__('XML'));
	  
      return parent::_prepareColumns();
  }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('productfile_id');
        $this->getMassactionBlock()->setFormFieldName('productfile');

        $this->getMassactionBlock()->addItem('delete', array(
             'label'    => Mage::helper('productfile')->__('Delete'),
             'url'      => $this->getUrl('*/*/massDelete'),
             'confirm'  => Mage::helper('productfile')->__('Are you sure?')
        ));

        $statuses = Mage::getSingleton('productfile/status')->getOptionArray();

        array_unshift($statuses, array('label'=>'', 'value'=>''));
        $this->getMassactionBlock()->addItem('status', array(
             'label'=> Mage::helper('productfile')->__('Change status'),
             'url'  => $this->getUrl('*/*/massStatus', array('_current'=>true)),
             'additional' => array(
                    'visibility' => array(
                         'name' => 'status',
                         'type' => 'select',
                         'class' => 'required-entry',
                         'label' => Mage::helper('productfile')->__('Status'),
                         'values' => $statuses
                     )
             )
        ));
        return $this;
    }

  public function getRowUrl($row)
  {
      return $this->getUrl('*/*/edit', array('id' => $row->getId()));
  }

}