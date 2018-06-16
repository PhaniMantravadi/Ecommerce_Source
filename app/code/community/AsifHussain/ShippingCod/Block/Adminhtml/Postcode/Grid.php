<?php
class AsifHussain_ShippingCod_Block_Adminhtml_Postcode_Grid
    extends Mage_Adminhtml_Block_Widget_Grid
{
    protected function _prepareCollection()
    {
        /**
         * Tell Magento which collection to use to display in the grid.
         */
        $collection = Mage::getResourceModel(
            'asifhussain_shippingcod/postcode_collection'
        );
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }
	
	
	protected function _prepareMassaction()
	{
		$this->setMassactionIdField('entity_id');
		$this->getMassactionBlock()->setFormFieldName('postcode_id');
	 
		$this->getMassactionBlock()->addItem('delete', array(
			'label'=> Mage::helper('asifhussain_shippingcod')->__('Delete'),
			'url'  => $this->getUrl('*/*/massDelete', array('' => '')),
			'confirm' => Mage::helper('asifhussain_shippingcod')->__('Are you sure?')
		));
 
	return $this;
	}
	
    public function getRowUrl($row)
    {
        /**
         * When a grid row is clicked, this is where the user should
         * be redirected to - in our example, the method editAction of
         * PostcodeController.php in ShippingCod module.
         */
        return $this->getUrl(
            'asifhussain_shippingcod_admin/postcode/edit',
            array(
                'id' => $row->getId()
            )
        );
    }

    protected function _prepareColumns()
    {
        /**
         * Here, we'll define which columns to display in the grid.
         */
		$this->addExportType('*/*/exportCsv', Mage::helper('asifhussain_shippingcod')->__('CSV'));
		$this->addExportType('*/*/exportExcel', Mage::helper('asifhussain_shippingcod')->__('Excel XML'));
		 
        $this->addColumn('entity_id', array(
            'header' => $this->_getHelper()->__('ID'),
            'type' => 'number',
            'index' => 'entity_id',
        ));

        $this->addColumn('postcode', array(
            'header' => $this->_getHelper()->__('Postcode'),
            'type' => 'text',
            'index' => 'postcode',
        ));
		$postcodeSingleton = Mage::getSingleton(
            'asifhussain_shippingcod/postcode'
        );
		
        $this->addColumn('isshipable', array(
            'header' => $this->_getHelper()->__('Can Ship'),
            'type' => 'options',
            'index' => 'isshipable',
            'options' => $postcodeSingleton->getAvailableVisibilies()
        ));
		
		$this->addColumn('iscod', array(
            'header' => $this->_getHelper()->__('Can COD'),
            'type' => 'options',
            'index' => 'iscod',
            'options' => $postcodeSingleton->getAvailableVisibilies()
        ));

        $this->addColumn('daystodeliver', array(
            'header' => $this->_getHelper()->__('Days to Deliver'),
            'type' => 'text',
            'index' => 'daystodeliver',
        ));


        /**
         * Finally, we'll add an action column with an edit link.
         */
        $this->addColumn('action', array(
            'header' => $this->_getHelper()->__('Action'),
            'width' => '50px',
            'type' => 'action',
            'actions' => array(
                array(
                    'caption' => $this->_getHelper()->__('Edit'),
                    'url' => array(
                        'base' => 'asifhussain_shippingcod_admin'
                                  . '/postcode/edit',
                    ),
                    'field' => 'id'
                ),
            ),
            'filter' => false,
            'sortable' => false,
            'index' => 'entity_id',
			'is_system' => true
        ));

        return parent::_prepareColumns();
    }

    protected function _getHelper()
    {
        return Mage::helper('asifhussain_shippingcod');
    }
}