<?php
class Velanapps_Ecfplus_Block_Adminhtml_Multiform_Edit_Tab_Form1 extends Mage_Adminhtml_Block_Widget_Grid implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('multiform_form1');
/*         $this->setDefaultSort('manage_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true); */
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
 
        /* $this->addColumn('store_id', array(
            'header'    => Mage::helper('ecfplus')->__('Store Id'),
            'align'     => 'left',
			'width'		=> '200px',
            'index'     => 'store_id',
        )); */
		
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
		/* $this->addColumn('View', array(
            'header'    => Mage::helper('ecfplus')->__('Action'),
            'index'     => 'Manage_id',
			'width'     => 25,
			'filter' => false,			
			'renderer' => 'Velanapps_Ecfplus_Block_Adminhtml_Manage_Renderer_Action', 
        )); */
 
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
	
	    /**
     * ######################## TAB settings #################################
     */
     public function getTabLabel()
    {
        return Mage::helper('ecfplus')->__('Manage Reports');
    }

    public function getTabTitle()
    {
        return Mage::helper('ecfplus')->__('Manage Reports');
    }

    public function canShowTab()
    {
        if ($this->getOrder()->getIsVirtual()) {
            return false;
        }
        return true;
    }

    public function isHidden()
    {
        return false;
    }
}