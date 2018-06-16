<?php

class Cminds_Marketplace_Block_Adminhtml_Customer_Edit_Tab_Paymentprofile_Edit_Form
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
            'payment_fieldset',
            array(
                'legend' => Mage::helper('marketplace')->__('Supplier Payment Information'),
            )
        );

        $fieldset->addField(
            'bank_account',
            'text',
            array(
                'label'    => Mage::helper('marketplace')->__('Bank Account Number'),
                'name'     => 'seller_data[bank_account]',
                'required' => false,
                'value'     => $customer->getData('bank_account')
            )
        );
        $fieldset->addField(
            'bank_name',
            'text',
            array(
                'label'    => Mage::helper('marketplace')->__('Bank Name'),
                'name'     => 'seller_data[bank_name]',
                'required' => false,
                'value'     => $customer->getData('bank_name')
            )
        );
        $fieldset->addField(
            'branch_address',
            'text',
            array(
                'label'    => Mage::helper('marketplace')->__('Branch Address'),
                'name'     => 'seller_data[branch_address]',
                'required' => false,
                'value'     => $customer->getData('branch_address')
            )
        );
        $fieldset->addField(
            'ifsc',
            'text',
            array(
                'label'    => Mage::helper('marketplace')->__('IFSC Code'),
                'name'     => 'seller_data[ifsc]',
                'required' => false,
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
                'required' => false,
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