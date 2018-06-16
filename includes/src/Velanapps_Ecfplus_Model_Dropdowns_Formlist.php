<?php
class  Velanapps_Ecfplus_Model_Dropdowns_Formlist
{
    public function toOptionArray()
	{		
		
		$ecfplus_form = Mage::getModel('ecfplus/multiform')->getCollection()
						->addFieldToFilter('status', array('eq' => 1))
						->getData();
						
		if(!empty($ecfplus_form))
		{
			$formlist[] = array('value' => '', 'label' => 'Please Select');
			foreach($ecfplus_form as $value)
			{
				$formlist_id = $value['id']; 
				$formlist_name = $value['name']; 
				$formlist[] = array('value' => $formlist_id, 'label' => $formlist_name);
			}
		}
		else
		{
			$formlist[] = array('value' => '', 'label' => 'Forms does not exist');
		}
		return $formlist;
		
    }
}