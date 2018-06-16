<?php
class Velanapps_Ecfplus_Block_Adminhtml_Storelocator_Grid extends Mage_Adminhtml_Block_Widget_Grid 
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('storelocatorGrid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }
 
    protected function _prepareCollection()
    {
        $collection = Mage::getModel('ecfplus/storelocator')->getCollection();
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
			'filter'    => false,
			'sortable'  => false
        ));
		
		$this->addColumn('name', array(
            'header'    => Mage::helper('ecfplus')->__('Map Name'),
            'align'     =>'right',
			'type'		=> 'varchar',
            'index'     => 'map_name',
			'filter'    => false,
			'sortable'  => false
        ));
	    
		$this->addColumn('status', array(
            'header'    => Mage::helper('ecfplus')->__('status'),
            'align'     =>'right',
            'index'     => 'status',
			'type'      => 'options',
			'options'    => array('0' => 'Disabled','1' => 'Enabled'),
			'filter'    => false,
			'sortable'  => false
			
        ));
		
		$this->addColumn('location', array(
            'header'    => Mage::helper('ecfplus')->__('Location'),
            'align'     =>'right',
            'index'     => 'location',
			'renderer' => 'Velanapps_Ecfplus_Block_Adminhtml_Storelocator_Renderer_Location', 
			'filter'    => false,
			'sortable'  => false
			
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