<?php
class Velanapps_Ecfplus_Block_Adminhtml_Storelocator_Renderer_Location extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{

	public function render(Varien_Object $row)
	{
		$id =  $row->getId();
		$location = Mage::getModel('ecfplus/storelocator_locations')->getCollection()
					 ->addFieldToFilter('parent_id', $id)
					->addFieldToSelect('latitude')
					->addFieldToSelect('longitude')
					->addFieldToSelect('address')
					 ->load();
		$address="";
		$i=1;
		foreach($location->getData() as $value)
		{
			$address.= "<b class='left'>Address: ".$i."</b><br/><b>Latitude</b>".":".$value['latitude']."<br/>"."<b>Longitude</b>".":".$value['longitude']."<br/>"."<b>Address</b>".":".$value['address']."<br/>";
			$i++;
		}
		/* $addresses.="<table><tr><td class='a-left'>Address</td></tr>";		
		foreach($address as $key => $addressValue)
		{
			$addresses.="<tr><td>".$addressValue."</td></tr>";
		}
		$addresses.="</table>"; */
		return $address;
	}

}