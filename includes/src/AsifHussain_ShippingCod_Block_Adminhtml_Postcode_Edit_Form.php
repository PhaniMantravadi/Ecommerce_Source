<?php
class AsifHussain_ShippingCod_Block_Adminhtml_Postcode_Edit_Form
    extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        // Instantiate a new form to display our postcode for editing.
        $form = new Varien_Data_Form(array(
            'id' => 'edit_form',
            'action' => $this->getUrl(
                'asifhussain_shippingcod_admin/postcode/edit',
                array(
                    '_current' => true,
                    'continue' => 0,
                )
            ),
            'method' => 'post',
        ));
        $form->setUseContainer(true);
        $this->setForm($form);

        // Define a new fieldset. We need only one for our simple entity.
        $fieldset = $form->addFieldset(
            'general',
            array(
                'legend' => $this->__('Postcode Details')
            )
        );

        $postcodeSingleton = Mage::getSingleton(
            'asifhussain_shippingcod/postcode'
        );

        // Add the fields that we want to be editable.
        $this->_addFieldsToFieldset($fieldset, array(
            'postcode' => array(
                'label' => $this->__('Postcode'),
                'input' => 'text',
                'required' => true,
            ),
			'isshipable' => array(
                'label' => $this->__('Can Ship?'),
                'input' => 'select',
                'required' => true,
                'options' => $postcodeSingleton->getAvailableVisibilies()
            ),
			'iscod' => array(
                'label' => $this->__('Can COD?'),
                'input' => 'select',
                'required' => true,
                'options' => $postcodeSingleton->getAvailableVisibilies()
            ),
			'daystodeliver' => array(
                'label' => $this->__('Days to Deliver'),
                'input' => 'text',
                'required' => true,
            )
            /**
             * Note: we have not included created_at or updated_at.
             * We will handle those fields ourself in the model
       * before saving.
             */
        ));

        return $this;
    }

    /**
     * This method makes life a little easier for us by pre-populating
     * fields with $_POST data where applicable and wrapping our post data
     * in 'postcodeData' so that we can easily separate all relevant information
     * in the controller. You could of course omit this method entirely
     * and call the $fieldset->addField() method directly.
     */
    protected function _addFieldsToFieldset(
        Varien_Data_Form_Element_Fieldset $fieldset, $fields)
    {
        $requestData = new Varien_Object($this->getRequest()
            ->getPost('postcodeData'));

        foreach ($fields as $name => $_data) {
            if ($requestValue = $requestData->getData($name)) {
                $_data['value'] = $requestValue;
            }

            // Wrap all fields with postcodeData group.
            $_data['name'] = "postcodeData[$name]";

            // Generally, label and title are always the same.
            $_data['title'] = $_data['label'];

            // If no new value exists, use the existing postcode data.
            if (!array_key_exists('value', $_data)) {
                $_data['value'] = $this->_getPostcode()->getData($name);
            }

            // Finally, call vanilla functionality to add field.
            $fieldset->addField($name, $_data['input'], $_data);
        }

        return $this;
    }

    /**
     * Retrieve the existing postcode for pre-populating the form fields.
     * For a new postcode entry, this will return an empty postcode object.
     */
    protected function _getPostcode()
    {
        if (!$this->hasData('postcode')) {
            // This will have been set in the controller.
            $postcode = Mage::registry('current_postcode');

            // Just in case the controller does not register the postcode.
            if (!$postcode instanceof
                    AsifHussain_ShippingCod_Model_Postcode) {
                $postcode = Mage::getModel(
                    'asifhussain_shippingcod/postcode'
                );
            }

            $this->setData('postcode', $postcode);
        }

        return $this->getData('postcode');
    }
}