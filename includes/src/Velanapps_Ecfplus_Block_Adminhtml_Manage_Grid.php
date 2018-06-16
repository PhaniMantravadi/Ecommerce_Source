<?php
class Velanapps_Ecfplus_Block_Adminhtml_Manage_Grid extends Mage_Adminhtml_Block_Widget_Grid 
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('manageGrid');
		$this->setDefaultSort('manage_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(false); 
        $this->setUseAjax(true);
    }
 
    protected function _prepareCollection()
    {
		$id = $this->getRequest()->getParam('id');
        $collection = Mage::getModel('ecfplus/manage')->getCollection();
        $this->setCollection($collection);		
		$collection->getSelect('*')->where('form_id IN ('.$id.')');
        return parent::_prepareCollection();
    }
 
    protected function _prepareColumns()
    {
        $this->addColumn('manage_id', array(
            'header'    => Mage::helper('ecfplus')->__('ID'),
            'align'     =>'right',
			'type'		=> 'number',
            'index'     => 'manage_id',
        ));
		
		if (!Mage::app()->isSingleStoreMode()) {
			$this->addColumn('store_id', array(
				'header'        => Mage::helper('adminhtml')->__('Store View'),
				'index'         => 'store_id',
				'type'          => 'store',
				'store_all'     => true,
				'store_view'    => true,
				'sortable'      => true,
				'filter_condition_callback' => array($this,
					'_filterStoreCondition'),
			));
		}
 
        $this->addColumn('subfields', array(
            'header'    => Mage::helper('ecfplus')->__('Customer Contact Records'),
            'index'     => 'subfields',
			 'renderer' => 'Velanapps_Ecfplus_Block_Adminhtml_Manage_Renderer_Subfields', 
        ));
		
		
        return parent::_prepareColumns();
    }
	
    // public function getRowUrl($row)
    // {
        // return $this->getUrl('*/adminhtml_manage/view', array('id' => $row->getId()));
    // }
 
    // public function getGridUrl()
    // {
      // return $this->getUrl('*/adminhtml_manage/grid', array('_current'=>true));
    // }
	protected function _filterStoreCondition($collection, $column)
	{
		if (!$value = $column->getFilter()->getValue())
		{
			return;
		}
		$this->getCollection()->addStoreFilter($value);
	}
	
   
	
	protected function _getSelectedCustomers()   // Used in grid to return selected customers values.
    {
        $customers = array_keys($this->getSelectedCustomers());
        return $customers;
    }
 
    public function getSelectedCustomers()
    {
        // Customer Data
        $tm_id = $this->getRequest()->getParam('id');
        if(!isset($tm_id)) {
            $tm_id = 0;
        }
        $customers = array(1,2); // This is hard-coded right now, but should actually get values from database.
        $custIds = array();
 
        foreach($customers as $customer) {
            foreach($customer as $cust) {
                $custIds[$cust] = array('position'=>$cust);
            }
        }
        return $custIds;
    }
}