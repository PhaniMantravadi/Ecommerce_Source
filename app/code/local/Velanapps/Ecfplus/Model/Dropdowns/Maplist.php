<?php
class  Velanapps_Ecfplus_Model_Dropdowns_Maplist
{
    public function toOptionArray()
	{		
		
		$ecfplus_map = Mage::getModel('ecfplus/storelocator')->getCollection()
						->addFieldToFilter('status', array('eq' => 1))
						->getData();
						
		if(!empty($ecfplus_map))
		{
			$formlist[] = array('value' => '', 'label' => 'Please Select');
			foreach($ecfplus_map as $value)
			{
				$maplist_id = $value['id']; 
				$maplist_name = $value['map_name']; 
				$maplist[] = array('value' => $maplist_id, 'label' => $maplist_name);
			}
		}
		else
		{
			$maplist[] = array('value' => '', 'label' => 'Forms does not exist');
		}
		return $maplist;
		
    }
}