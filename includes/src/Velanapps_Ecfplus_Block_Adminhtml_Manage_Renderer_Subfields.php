<?php
class Velanapps_Ecfplus_Block_Adminhtml_Manage_Renderer_Subfields extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{

	public function render(Varien_Object $row)
	{
		$value =  $row->getData($this->getColumn()->getIndex());
		$decoded = unserialize($value);
		$ecfDataBody = "";
		foreach($decoded as $key=>$value)
		{
			$ecfDataBody.= "<b>".$key."</b>:".$value."<br>";
		}
		//$ret = substr($ecfDataBody, 0, 50)."...";
		return $ecfDataBody;
	}

}