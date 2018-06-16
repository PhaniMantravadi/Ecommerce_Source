<?php
class Velanapps_Ecfplus_Helper_Data extends Mage_Core_Helper_Abstract
{
	public function adminEmail()
	{
	  return Mage::getStoreConfig('trans_email/ident_general/email'); 	
	}
	
	public function adminName()
	{
	  return Mage::getStoreConfig('trans_email/ident_general/name'); 	
	}
	
	public function ecfplusActive()
	{
		return Mage::getStoreConfig('ecfplus_tab/ecfplus_setting/ecfplus_popup_active');
	}
	
	public function ecfplusPosition()
	{
		return Mage::getStoreConfig('ecfplus_tab/ecfplus_setting/ecfplus_popup_position');
	}
	
	public function ecfplusTitlePallet()
	{
		return Mage::getStoreConfig('ecfplus_tab/ecfplus_setting/ecfplus_title_color');
	}
	
	public function ecfplusContentPallet()
	{
		return Mage::getStoreConfig('ecfplus_tab/ecfplus_setting/ecfplus_content_color');
	}
	
	public function ecfplusSubmitPallet()
	{
		return Mage::getStoreConfig('ecfplus_tab/ecfplus_setting/ecfplus_submit_color');
	}
	
	public function ecfplusForm()
	{
		return Mage::getStoreConfig('ecfplus_tab/ecfplus_setting/ecfplus_form_list');
	}
	
	public function ecfplusContactInfo()
	{
		return Mage::getStoreConfig('ecfplus_tab/ecfplus_setting/ecfplus_contact_info');
	}
	
	public function ecfplusContactContent()
	{
		return Mage::getStoreConfig('ecfplus_tab/ecfplus_setting/ecfplus_contact_content');
	}
	public function getImageUrl($fileName){
		$mediaUrl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA);
		$imageUrl = $mediaUrl . "velanapps/" . $fileName;
		return $imageUrl;
	}
	
}