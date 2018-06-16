<?php
class Velanapps_Ecfplus_Block_Map extends Mage_Core_Block_Template implements Mage_Widget_Block_Interface
{
	public function address()
	{
		$selected_map = $this->getData('enable_ecfplusmap');
			$row = Mage::getModel('ecfplus/storelocator')->getCollection()
				->addFieldToFilter('id',$selected_map)
				->addFieldToSelect('status')
				->getData();
	
		if($row[0]['status'] == 1)
		{			
			$location = Mage::getModel('ecfplus/storelocator_locations')->getCollection()
						->addFieldToFilter('parent_id', $selected_map)
						->addFieldToSelect('latitude')
						->addFieldToSelect('longitude')
						->addFieldToSelect('address')
						 ->load();
			$jsonData = Mage::helper('core')->jsonEncode($location->getData());
		}
		else
		{
			$jsonData = Mage::helper('core')->jsonEncode(0);
		}
		
		return $jsonData;
	}
	public function getMarker()
	{
		$selected_map = $this->getData('enable_ecfplusmap');
			$row = Mage::getModel('ecfplus/storelocator')->getCollection()
				->addFieldToFilter('id',$selected_map)
				->addFieldToSelect('background_image')
				->addFieldToFilter('status',1)->getData();
		
		$jsonData = Mage::helper('core')->jsonEncode($row);
		return $jsonData;
	}
}