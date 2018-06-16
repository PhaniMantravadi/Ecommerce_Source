<?php

class Cminds_Marketplacecustom_Block_Adminhtml_Customer_Edit_Tab_Profile_Edit_Form
    extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(
            array(
                'id'      => 'edit_form',
                'method'  => 'post',
            )
        );
        $form->setUseContainer(true);
        $this->setForm($form);

        $customer = Mage::registry('current_customer');

        $fieldset = $form->addFieldset(
            'configuration_fieldset',
            array(
                'legend' => Mage::helper('marketplace')->__('Company Data'),
            )
        );

        $fieldset->addField(
            'shop_name',
            'text',
            array(
                'label'    => Mage::helper('marketplace')->__('Shop Name'),
                'name'     => 'seller_data[shop_name]',
                'required' => true,
                'value'     => $customer->getData('shop_name')
            )
        );

        $fieldset->addField(
            'seller_category',
            'select',
            array(
                'label'    => Mage::helper('marketplace')->__('Seller Category'),
                'name'     => 'seller_data[seller_category]',
                'required' => true,
                'value'     => $customer->getData('seller_category'),
                'values' => Mage::helper('marketplacecustom')->getAllCategoriesArray(true)
            )
        );
        $fieldset->addField(
            'phone',
            'text',
            array(
                'label'    => Mage::helper('marketplace')->__('Mobile Number'),
                'name'     => 'seller_data[mobile_number]',
                'required' => true,
                'value'     => $customer->getData('mobile_number')
            )
        );
        $fieldset = $form->addFieldset(
            'configuration_fieldset_step2',
            array(
                'legend' => Mage::helper('marketplace')->__('Company Info'),
            )
        );

        $fieldset->addField(
            'company_name',
            'text',
            array(
                'label'    => Mage::helper('marketplace')->__('Company Name'),
                'name'     => 'seller_data[company_name]',
                'required' => true,
                'value'     => $customer->getData('company_name')
            )
        );

        $fieldset->addField(
            'about_shop',
            'text',
            array(
                'label'    => Mage::helper('marketplace')->__('About Shop'),
                'name'     => 'seller_data[about_shop]',
                'value'     => $customer->getData('about_shop')
            )
        );
        $fieldset->addField(
            'bank_account',
            'text',
            array(
                'label'    => Mage::helper('marketplace')->__('Bank Account Number'),
                'name'     => 'seller_data[bank_account]',
                'required' => true,
                'value'     => $customer->getData('bank_account')
            )
        );
        $fieldset->addField(
            'bank_name',
            'text',
            array(
                'label'    => Mage::helper('marketplace')->__('Bank Name'),
                'name'     => 'seller_data[bank_name]',
                'required' => true,
                'value'     => $customer->getData('bank_name')
            )
        );
        $fieldset->addField(
            'branch_address',
            'text',
            array(
                'label'    => Mage::helper('marketplace')->__('Branch Address'),
                'name'     => 'seller_data[branch_address]',
                'required' => true,
                'value'     => $customer->getData('branch_address')
            )
        );
        $fieldset->addField(
            'ifsc',
            'text',
            array(
                'label'    => Mage::helper('marketplace')->__('IFSC Code'),
                'name'     => 'seller_data[ifsc]',
                'required' => true,
                'value'     => $customer->getData('ifsc_code')
            )
        );
        $fieldset->addField(
            'vat',
            'text',
            array(
                'label'    => Mage::helper('marketplace')->__('VAT/TIN'),
                'name'     => 'seller_data[vat]',
                'value'     => $customer->getData('vat')
            )
        );
        $fieldset->addField(
            'pan',
            'text',
            array(
                'label'    => Mage::helper('marketplace')->__('PAN Number'),
                'name'     => 'seller_data[pan]',
                'required' => true,
                'value'     => $customer->getData('pan')
            )
        );
        $fieldset->addField(
            'cst',
            'text',
            array(
                'label'    => Mage::helper('marketplace')->__('CST'),
                'name'     => 'seller_data[cst]',
                'value'     => $customer->getData('cst')
            )
        );
        $fieldset = $form->addFieldset(
            'configuration_fieldset_documents',
            array(
                'legend' => Mage::helper('marketplace')->__('Uploaded Documents'),
            )
        );

        $fieldset->addField(
            'pan_document',
            'note',
            array(
                'label'    => Mage::helper('marketplace')->__('PAN Document'),
                'name'     => 'seller_data[pan_document]',
                'text'     => ($customer->getData('pan_document') ? '<a href="'.Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . 'documents/pan_document/'.$customer->getData('pan_document').'">Download</a>' : '')
            )
        );

        $fieldset->addField(
            'vat_document',
            'note',
            array(
                'label'    => Mage::helper('marketplace')->__('VAT Document'),
                'name'     => 'seller_data[vat_document]',
                'text'     => ($customer->getData('vat_document') ? '<a href="'.Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . 'documents/vat_document/'.$customer->getData('vat_document').'">Download</a>' : '')
            )
        );

        $fieldset->addField(
            'tin_document',
            'note',
            array(
                'label'    => Mage::helper('marketplace')->__('TIN Document'),
                'name'     => 'seller_data[tin_document]',
                'text'     => ($customer->getData('tin_document') ? '<a href="'.Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . 'documents/tin_document/'.$customer->getData('tin_document').'">Download</a>' : '')
            )
        );

        $fieldset->addField(
            'bank_can_chq',
            'note',
            array(
                'label'    => Mage::helper('marketplace')->__('Bank Can Chq'),
                'name'     => 'seller_data[bank_can_chq]',
                'text'     => ($customer->getData('bank_can_chq') ? '<a href="'.Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . 'documents/bank_can_chq/'.$customer->getData('bank_can_chq').'">Download</a>' : '')
            )
        );

        $fieldset->addField(
            'other_document',
            'note',
            array(
                'label'    => Mage::helper('marketplace')->__('Other Document'),
                'name'     => 'seller_data[other_document]',
                'text'     => ($customer->getData('other_document') ? '<a href="'.Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . 'documents/other_document/'.$customer->getData('other_document').'">Download</a>' : '')
            )
        );

        $fieldset->addField(
            'other_document_2',
            'note',
            array(
                'label'    => Mage::helper('marketplace')->__('Other Document 2'),
                'name'     => 'seller_data[other_document_2]',
                'text'     => ($customer->getData('other_document_2') ? '<a href="'.Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . 'documents/other_document_2/'.$customer->getData('other_document_2').'">Download</a>' : '')
            )
        );

        return parent::_prepareForm();
    }
}