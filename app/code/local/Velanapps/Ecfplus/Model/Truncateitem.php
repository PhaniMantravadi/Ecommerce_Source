<?php
class Velanapps_Ecfplus_Model_Truncateitem
{

	public function deleteItems($formid)
	{
		
		 $modelDeleteItem = Mage::getModel('ecfplus/items')->getCollection()->addFieldToSelect('item_id')->addFieldToFilter('form_id', array('eq' => $formid))->getData();
		
		if(!empty($modelDeleteItem))
		{
			foreach ($modelDeleteItem as $id)
			{
						$modelDelItem = Mage::getModel('ecfplus/items')->load($id);				
						$modelDelItem->delete();		
			}
		}		
		$modelDeleteOption = Mage::getModel('ecfplus/itemoptions')->getCollection()->addFieldToSelect('option_id')->addFieldToFilter('form_id', array('eq' => $formid))->getData();		
		if(!empty($modelDeleteOption))
		{
			foreach ($modelDeleteOption as $ids)
			{
						$modelDelOption = Mage::getModel('ecfplus/itemoptions')->load($ids);
						$modelDelOption->delete();		
			}
		} 
		 
				
		//truncate table edit
		
		return ;
	}
	
	
}