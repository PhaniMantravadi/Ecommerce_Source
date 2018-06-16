<?php
class Velanapps_Ecfplus_Block_Adminhtml_Multiform_Grid extends Mage_Adminhtml_Block_Widget_Grid 
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('multiformGrid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }
 
    protected function _prepareCollection()
    {
        $collection = Mage::getModel('ecfplus/multiform')->getCollection();
		$this->setCollection($collection);            
        return parent::_prepareCollection(); 
    }
 
    protected function _prepareColumns()
    {
        $this->addColumn('id', array(
            'header'    => Mage::helper('ecfplus')->__('Id'),
            'align'     =>'right',
			'type'		=> 'number',
            'index'     => 'id',
        ));
		
		$this->addColumn('name', array(
            'header'    => Mage::helper('ecfplus')->__('Name'),
            'align'     =>'right',
			'type'		=> 'varchar',
            'index'     => 'name',
        ));
	    
		$this->addColumn('status', array(
            'header'    => Mage::helper('ecfplus')->__('status'),
            'align'     =>'right',
            'index'     => 'status',
			'type'      => 'options',
			'options'    => array('0' => 'Disabled','1' => 'Enabled')

			
        ));
	
		
		$this->addColumn('actions_edit_form',array(
			'header'    => Mage::helper('adminhtml')->__('Actions Edit form'),
			'type'      => 'action',
			'getter'    => 'getId',
			'actions'   => array(
				array(
					'caption' => Mage::helper('adminhtml')->__('Edit Form'),
					'url'     => array(
						'base'=>'*/*/edit',						
					),
					'field'   => 'id'
				)
			),
			'filter'    => false,
			'sortable'  => false,
		));
		
		$this->addColumn('actions_edit_form_fields',array(
			'header'    => Mage::helper('adminhtml')->__('Actions Edit Form Fields'),
			'type'      => 'action',
			'getter'    => 'getId',
			'actions'   => array(
				array(
					'caption' => Mage::helper('adminhtml')->__('Edit Form Fields'),
					'url'     => array(
						'base'=>'*/adminhtml_multiform/add/',
					),
					'field'   => 'id'
				)
			),
			'filter'    => false,
			'sortable'  => false,
		));
		
		
        return parent::_prepareColumns();
    }
	
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }
 
    public function getGridUrl()
    {
      return $this->getUrl('*/*/grid', array('_current'=>true));
    }
	
	protected function _filterStoreCondition($collection, $column)
	{
		if (!$value = $column->getFilter()->getValue())
		{
			return;
		}
		$this->getCollection()->addStoreFilter($value);
	}
	
 
}