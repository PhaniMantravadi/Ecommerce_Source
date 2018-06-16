<?php 
class Velanapps_Ecfplus_Block_Adminhtml_Storelocator_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
      
		$form = new Varien_Data_Form();
        $this->setForm($form);
		$product = Mage::registry('storelocator_data');
		
		$fieldset = $form->addFieldset('storelocator', array(
             'legend' =>Mage::helper('adminhtml')->__('General Information')
        ));
		$fieldset->addField('map_name', 'text', array(
            'label'     => Mage::helper('adminhtml')->__('Map Name'),
            'class'     => 'required-entry',
            'required'  => true,
            'name'      => 'map_name',
        ));  
		$fieldset->addField(
			'background_image', 
			'image',
			array(
				'label' => $this->__("Background Image"),
				'name' => 'background_image',
				'note' => $this->__("Background Image should be 45X75")
			)
		);		
		$fieldset->addField('status', 'select', array(
            'label'     => Mage::helper('adminhtml')->__('Status'),
			'class'     => 'required-entry',
            'name'      => 'status',
			'value'  => '1',
            'values'    => array(
				 array(
                    'value'     => '',
                    'label'     => Mage::helper('adminhtml')->__('Please Select'),
                ),
                array(
                    'value'     => '0',
                    'label'     => Mage::helper('adminhtml')->__('Disabled'),
                ),
				array(
                    'value'     => '1',
                    'label'     => Mage::helper('adminhtml')->__('Enabled'),
                ),
            ),
        ));
			
		 $locationField = $fieldset->addField('location', 'text', array(
            'name'      => 'location',
            'label'     => Mage::helper('adminhtml')->__('Location'),
            'required'  => false,
        ));

		$locationField = $form->getElement('location');

		$locationField->setRenderer(
			$this->getLayout()->createBlock('ecfplus/adminhtml_storelocator_edit_tab_location_value')
		);

	
		
		if ( Mage::getSingleton('adminhtml/session')->getStorelocatorData() )
        {
            $form->setValues(Mage::getSingleton('adminhtml/session')->getStorelocatorData());
            Mage::getSingleton('adminhtml/session')->setStorelocatorData(null);
        } elseif ( Mage::registry('storelocator_data') ) {
            $form->setValues(Mage::registry('storelocator_data')->getData());
        }
       // $form->setValues($data); 
        return parent::_prepareForm();	
    }
}