<?php
class Velanapps_Ecfplus_Block_Adminhtml_Multiform_Options extends Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Options
{
	public function __construct()
    {
        $this->setTemplate('ecfplus/options.phtml');
    }
	
	
	protected function _prepareLayout()
    {
		$this->setChild('back_button',
                $this->getLayout()->createBlock('adminhtml/widget_button')
                    ->setData(array(
                        'label'     => Mage::helper('catalog')->__('Close Window'),
                        'onclick'   => 'window.close()',
                        'class' => 'cancel'
                    ))
        );
		
		$this->setChild('save_button',
                $this->getLayout()->createBlock('adminhtml/widget_button')
                    ->setData(array(
                        'label'     => Mage::helper('catalog')->__('Save'),
                        'class' => 'save',
						//'onclick'   => 'this.submit();',
						'onclick' => 'productForm.submit()',
                    ))
        );
			
        $this->setChild('add_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label' => Mage::helper('catalog')->__('Add New Option'),
                    'class' => 'add',
                    'id'    => 'add_new_contact_option'
                ))
        );
		
        $this->setChild('options_box', $this->getLayout()->createBlock('ecfplus/adminhtml_multiform_options_option'));
		
        return;
    }
	
	public function getSaveUrl()
    {
        return $this->getUrl('ecfplus/adminhtml_multiform/postData', array('_current'=>true, 'back'=>null));
    }

	
	public function getHeader()
    {
        return 'Form Fields :';
    }
	
	public function getBackButtonHtml()
    {
        return $this->getChildHtml('back_button');
    }
	
	public function getSaveButtonHtml()
    {
        return $this->getChildHtml('save_button');
    }

    public function getAddButtonHtml()
    {
        return $this->getChildHtml('add_button');
    }

    public function getOptionsBoxHtml()
    {
        return $this->getChildHtml('options_box');
    }
}
?>