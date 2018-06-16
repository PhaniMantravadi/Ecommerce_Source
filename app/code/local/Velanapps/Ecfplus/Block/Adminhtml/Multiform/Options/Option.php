
<?php
class Velanapps_Ecfplus_Block_Adminhtml_Multiform_Options_Option extends Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Options_Option
{

	protected $_product;

    protected $_productInstance;

    protected $_values;

    protected $_itemCount = 1;

    /**
     * Class constructor
     */
    public function __construct()
    {
       
		$this->setTemplate('ecfplus/options/option.phtml');
        $this->setCanReadPrice(true);
        $this->setCanEditPrice(true);
    }

    public function getItemCount()
    {
        return $this->_itemCount;
    }

    public function setItemCount($itemCount)
    {
        $this->_itemCount = max($this->_itemCount, $itemCount);
        return $this;
    }

    /**
     * Get Product
     *
     * @return Mage_Catalog_Model_Product
     */
    public function getProduct()
    {
        if (!$this->_productInstance) {
            if ($product = Mage::registry('product')) {
                $this->_productInstance = $product;
            } else {
                $this->_productInstance = Mage::getSingleton('catalog/product');
            }
        }

        return $this->_productInstance;
    }

    public function setProduct($product)
    {
        $this->_productInstance = $product;
        return $this;
    }

    /**
     * Retrieve options field name prefix
     *
     * @return string
     */
    public function getFieldName()
    {
        //return 'product[options]';
		return 'ecfplus';
    }

    /**
     * Retrieve options field id prefix
     *
     * @return string
     */
    public function getFieldId()
    {
        return 'product_option';
    }

    /**
     * Check block is readonly
     *
     * @return boolean
     */
    public function isReadonly()
    {
         return $this->getProduct()->getOptionsReadonly();
    }

    protected function _prepareLayout()
    {
		//parent::_prepareLayout();
		
        $this->setChild('delete_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label' => Mage::helper('catalog')->__('Delete Option'),
                    'class' => 'delete delete-product-option '
                ))
        );
		

		$this->setChild('select_option_type',
                $this->getLayout()->createBlock('ecfplus/adminhtml_multiform_options_type_select')
            );
	
		$this->setChild('file_option_type',
                $this->getLayout()->createBlock('ecfplus/adminhtml_multiform_options_type_file')
            );
	
		$this->setChild('date_option_type',
                $this->getLayout()->createBlock('ecfplus/adminhtml_multiform_options_type_date')
            );
		
		$this->setChild('text_option_type',
                $this->getLayout()->createBlock('ecfplus/adminhtml_multiform_options_type_text')
            );	
		
		/**
        $path = 'global/catalog/product/options/custom/groups';
		Mage::getConfig()->getNode($path)->children();

        foreach (Mage::getConfig()->getNode($path)->children() as $group) 
		{
            $this->setChild($group->getName() . '_option_type',
			 $this->getLayout()->createBlock(
                    (string) Mage::getConfig()->getNode($path . '/' . $group->getName() . '/render')
                )
                
            );
        }
		*/
		
        return;
    }

    public function getAddButtonId()
    {
		/**
        $buttonId = $this->getLayout()
                ->getBlock('admin.product.options')
                ->getChild('add_button')->getId();
		*/
        return 'add_new_contact_option';
    }

    public function getDeleteButtonHtml()
    {
        return $this->getChildHtml('delete_button');
    }

    public function getTypeSelectHtml()
    {
        $select = $this->getLayout()->createBlock('adminhtml/html_select')
            ->setData(array(
                'id' => $this->getFieldId().'_{{id}}_type',
                'class' => 'select select-product-option-type required-option-select'
            ))
            ->setName($this->getFieldName().'[{{id}}][type]')
            ->setOptions(Mage::getSingleton('ecfplus/system_config_source_product_options_type')->toOptionArray());
        return $select->getHtml();
		
    }

    public function getRequireSelectHtml()
    {
        $select = $this->getLayout()->createBlock('adminhtml/html_select')
            ->setData(array(
                'id' => $this->getFieldId().'_{{id}}_is_require',
                'class' => 'select'
            ))
            ->setName($this->getFieldName().'[{{id}}][is_require]')
            ->setOptions(Mage::getSingleton('adminhtml/system_config_source_yesno')->toOptionArray());

        return $select->getHtml();
    }

    /**
     * Retrieve html templates for different types of product custom options
     *
     * @return string
     */
    public function getTemplatesHtml()
    {
		$templates = $this->getChildHtml('text_option_type') . "\n" .
        $this->getChildHtml('file_option_type') . "\n" .
        $this->getChildHtml('select_option_type') . "\n" .
        $this->getChildHtml('date_option_type');
		
        return $templates;
    }

     public function getOptionValues($formid)
    {
		$optionsArr = Mage::getModel('ecfplus/items')->getCollection()->addFieldToFilter('form_id', array('eq' => $formid))->getData();
		foreach ($optionsArr as $option)
		{
			$value = array();
			$value['id'] = $option['item_id'];
			$value['item_count'] = $option['item_id'];
			$value['option_id'] = $option['item_id'];
			$value['title'] = $option['title'];
			$value['type'] = $option['type'];
			$value['is_require'] = $option['is_require'];
			$value['sort_order'] = $option['sort_order'];
			$value['can_edit_price'] = true;
			$value['is_email'] = $option['is_email'];
			
			$i = 0;
			$itemCount = 0;
			$contactItemOption = Mage::getModel('ecfplus/itemoptions')->getCollection()->addFieldToFilter('item_id', array('eq' => $option['item_id']))->addFieldToFilter('form_id', array('eq' => $formid))->load()->getData();
			
			foreach ($contactItemOption as $_value)
			{
				$value['optionValues'][$i] = array(
					'item_count' => ++$itemCount,
					'option_id' => $option['item_id'],
					'option_type_id' => ++$itemCount,
					'title' => $_value['title'],
					'sort_order' => $_value['sort_order'],
				);
				$i++;
			}
			$value['max_characters'] = $_value['max_characters'];
			$value['validation'] = $_value['validation'];
			$values[] = new Varien_Object($value);
		}
		$this->_values = $values;
        return $this->_values;
    }

    /**
     * Retrieve html of scope checkbox
     *
     * @param string $id
     * @param string $name
     * @param boolean $checked
     * @param string $select_id
     * @return string
     */
    public function getCheckboxScopeHtml($id, $name, $checked=true, $select_id='-1')
    {
        $checkedHtml = '';
        if ($checked) {
            $checkedHtml = ' checked="checked"';
        }
        $selectNameHtml = '';
        $selectIdHtml = '';
        if ($select_id != '-1') {
            $selectNameHtml = '[values][' . $select_id . ']';
            $selectIdHtml = 'select_' . $select_id . '_';
        }
        $checkbox = '<input type="checkbox" id="' . $this->getFieldId() . '_' . $id . '_' .
            $selectIdHtml . $name . '_use_default" class="product-option-scope-checkbox" name="' .
            $this->getFieldName() . '['.$id.']' . $selectNameHtml . '[scope][' . $name . ']" value="1" ' .
            $checkedHtml . '/>';
        $checkbox .= '<label class="normal" for="' . $this->getFieldId() . '_' . $id . '_' .
            $selectIdHtml . $name . '_use_default">' . $this->__('Use Default Value') . '</label>';
        return $checkbox;
    }

    public function getPriceValue($value, $type)
    {
        if ($type == 'percent') {
            return number_format($value, 2, null, '');
        } elseif ($type == 'fixed') {
            return number_format($value, 2, null, '');
        }
    }
	
	public function getIsMail($formid)
	{
		$option = Mage::getModel('ecfplus/items')->getCollection()->addFieldToSelect('item_id')->addFieldToFilter('is_mail', array('eq' => 1))->addFieldToFilter('form_id', array('eq' => $formid))->getData();
		
        return $option[0]['item_id'];
	}
	
	/* public function getOptionValues()
    {
		$optionsArr = Mage::getModel('ecfplus/items')->getCollection()->getData();
		foreach ($optionsArr as $option)
		{
			$value = array();
			$value['id'] = $option['item_id'];
			$value['item_count'] = $option['item_id'];
			$value['option_id'] = $option['item_id'];
			$value['title'] = $option['title'];
			$value['type'] = $option['type'];
			$value['is_require'] = $option['is_require'];
			$value['sort_order'] = $option['sort_order'];
			$value['can_edit_price'] = true;
			$value['is_email'] = $option['is_email'];
			
			$i = 0;
			$itemCount = 0;
			$contactItemOption = Mage::getModel('ecfplus/itemoptions')->getCollection()->addFieldToFilter('item_id', array('eq' => $option['item_id']))->addFieldToFilter('form_id', array('eq' => $formid))->load()->getData();
			
			foreach ($contactItemOption as $_value)
			{
				$value['optionValues'][$i] = array(
					'item_count' => ++$itemCount,
					'option_id' => $option['item_id'],
					'option_type_id' => ++$itemCount,
					'title' => $_value['title'],
					'sort_order' => $_value['sort_order'],
				);
				$i++;
			}
			$value['max_characters'] = $_value['max_characters'];
			$value['validation'] = $_value['validation'];
			$values[] = new Varien_Object($value);
		}
		$this->_values = $values;
        return $this->_values;
    } */

}

?>