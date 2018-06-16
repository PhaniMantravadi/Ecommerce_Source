<?php 
class Velanapps_Ecfplus_Block_Adminhtml_Multiform_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
      
       
		$form = new Varien_Data_Form();
        $this->setForm($form);
		
        $fieldset = $form->addFieldset('multiform', array(
             'legend' =>Mage::helper('adminhtml')->__('General Information')
        ));
	
		
        $fieldset->addField('name', 'text', array(
            'label'     => Mage::helper('adminhtml')->__('Name'),
            'class'     => 'required-entry',
            'required'  => true,
            'name'      => 'name',
        ));     
	
        $fieldset->addField('status', 'select', array(
            'label'     => Mage::helper('adminhtml')->__('Status'),
			'class'     => 'required-entry',
			'required'  => true,
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
		$fieldset->addField('message_field', 'editor', array(
			'name'      => 'message_field',
			'label'     => Mage::helper('adminhtml')->__('Thankyou Message Field'),
			'title'     => Mage::helper('adminhtml')->__('Content'),
			'style'     => 'height:15em',
			'config'    => Mage::getSingleton('cms/wysiwyg_config')->getConfig(),
			'wysiwyg'   => true,
			'required'  => true,
		));
		$fieldset->addField('enable_email', 'select', array(
            'label'     => Mage::helper('adminhtml')->__('Enable Email For customers'),
			'class'     => 'required-entry',
            'name'      => 'enable_email',
            'values'    => array(
                array(
                    'value'     => 1,
                    'label'     => Mage::helper('adminhtml')->__('Yes'),
                ),
 
                array(
                    'value'     => 0,
                    'label'     => Mage::helper('adminhtml')->__('No'),
                ),
            ),
        ));
		
		/* $fieldset->addField('recaptcha', 'select', array(
            'label'     => Mage::helper('adminhtml')->__('Enable ReCaptcha'),
			'class'     => 'required-entry',
            'name'      => 'recaptcha',
            'values'    => array(
                array(
                    'value'     => 1,
                    'label'     => Mage::helper('adminhtml')->__('Yes'),
                ),
 
                array(
                    'value'     => 0,
                    'label'     => Mage::helper('adminhtml')->__('No'),
                ),
            ),
        )); */
		

		/* if($this->getRequest()->getParam('id') != "" ) {
			$data = Mage::registry('multiform_data')->getData();	
		}
		else {
			$data = array();		
		} */
		
		if ( Mage::getSingleton('adminhtml/session')->getMultiformData() )
        {
            $form->setValues(Mage::getSingleton('adminhtml/session')->getMultiformData());
            Mage::getSingleton('adminhtml/session')->setMultiformData(null);
        } elseif ( Mage::registry('multiform_data') ) {
            $form->setValues(Mage::registry('multiform_data')->getData());
        }
       // $form->setValues($data); 
        return parent::_prepareForm();	
    }
}