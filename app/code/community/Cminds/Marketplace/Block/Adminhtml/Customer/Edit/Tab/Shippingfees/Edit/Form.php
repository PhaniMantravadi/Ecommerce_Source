<?php

class Cminds_Marketplace_Block_Adminhtml_Customer_Edit_Tab_Shippingfees_Edit_Form
    extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(
            array(
                'id'      => 'edit_form',
                'method'  => 'post',
                'enctype' => 'multipart/form-data',
            )
        );
        $customer = Mage::registry('current_customer');
        $customerShippingData = Mage::getModel('marketplace/methods')->load($customer->getId(), 'supplier_id');
        $form->setUseContainer(true);
        $this->setForm($form);

        $fieldset = $form->addFieldset(
            'flatrate_fieldset',
            array(
                'legend' => Mage::helper('marketplace')->__('Flat rate'),
            )
        );

        $fieldset->addField(
            'flat_rate_enabled',
            'select',
            array(
                'label'    => Mage::helper('marketplace')->__('Enabled'),
                'class'    => 'required-entry',
                'required' => true,
                'name'     => 'flat_rate_enabled',
                'options'   => Mage::getModel('adminhtml/system_config_source_yesno')->toArray(),
                'value'     => $customerShippingData->getData('flat_rate_available')
            )
        );

        $fieldset->addField(
            'flat_rate_fee',
            'text',
            array(
                'label'    => Mage::helper('marketplace')->__('Handling Fee'),
                'name'     => 'flat_rate_fee',
                'value'     => $customerShippingData->getData('flat_rate_fee')
            )
        );
        $fieldset = $form->addFieldset(
            'tablerate_fieldset',
            array(
                'legend' => Mage::helper('marketplace')->__('Table rate'),
            )
        );

        $fieldset->addField(
            'table_rate_enabled',
            'select',
            array(
                'label'    => Mage::helper('marketplace')->__('Enabled'),
                'class'    => 'required-entry',
                'required' => true,
                'name'     => 'table_rate_enabled',
                'options'   => Mage::getModel('adminhtml/system_config_source_yesno')->toArray(),
                'value'     => $customerShippingData->getData('table_rate_available')
            )
        );
        $fieldset->addField(
            'table_rate_file',
            'file',
            array(
                'label'    => Mage::helper('marketplace')->__('Upload CSV file'),
                'name'     => 'table_rate_file',
            )
        );
        $fieldset->addField(
            'table_rate_fee',
            'text',
            array(
                'label'    => Mage::helper('marketplace')->__('Default Handling Fee'),
                'name'     => 'table_rate_fee',
                'value'     => $customerShippingData->getTableRateFee()

            )
        );
        $fieldset->addField(
            'table_rate_condition',
            'select',
            array(
                'label'    => Mage::helper('marketplace')->__('Condition'),
                'name'     => 'table_rate_condition',
                'options'   => array(
                    1 => Mage::helper('marketplace')->__('Weight vs. Destination'),
                    2 => Mage::helper('marketplace')->__('Price vs. Destination'),
                    3 => Mage::helper('marketplace')->__('# of Items vs. Destination'),
                ),
                'value'     => $customerShippingData->getTableRateCondition()
            )
        );
        $fieldset = $form->addFieldset(
            'freeshipping_fieldset',
            array(
                'legend' => Mage::helper('marketplace')->__('Freeshipping'),
            )
        );

        $fieldset->addField(
            'freeshipping_enabled',
            'select',
            array(
                'label'     => Mage::helper('marketplace')->__('Enabled'),
                'class'     => 'required-entry',
                'required'  => true,
                'name'      => 'freeshipping_enabled',
                'options'   => Mage::getModel('adminhtml/system_config_source_yesno')->toArray(),
                'value'     => $customerShippingData->getData('free_shipping')
            )
        );


        return parent::_prepareForm();
    }
}