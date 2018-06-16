<?php
class Velanapps_Ecfplus_Block_Formlist extends Mage_Core_Block_Template implements Mage_Widget_Block_Interface
{
	public function ecfplusOptionsExist()
	{
		$get_service = $this->getData('enable_ecfplusform');  
		$contactItem = Mage::getModel('ecfplus/items')->getCollection()->addFilter('form_id', $get_service)->setOrder('sort_order', 'ASC')->getData();
		return $contactItem;
	}
	public function ecfplusOptions()
	{			
		$contactItemValue = $this->ecfplusOptionsExist();
		
		foreach($contactItemValue as $value)
		{	 
			//** Call the inner option function
			$options = $this->itemOptions($value['item_id']);
			$value['type'] = ($value['type'] == "drop_down") ? "select" : $value['type'] ;
			$value['type'] = ($value['type'] == "area")	? "textarea" : $value['type'] ;
			$value['type'] = ($value['type'] == "field") ? "text" : $value['type'] ;	
			$ecfplusItems[] = array(
						"name" => $value['title'],
						"type" => $value['type'],
						"required" => $value['is_require'],						
						"validation" => $options[0],
						"maxlen" => $options[1],
						"option" => $options[2]					
						);
		}

		//** Sort fields array
		ksort($ecfplusItems);
		
		foreach($ecfplusItems as $val)
		{
			if($val['type'] == "text")
			{
				$labelVal = "";
				$required = "";
				$maxlen = $val['maxlen'];
				$adminValid = $val['validation'];
				$name = str_replace(' ', '_',strtolower($val['name']));		
				echo "<li class='fields'><div class='field'>";
				if($val['required'] == true)
				{
					$labelVal = " class = 'required' ><em>*</em ";
					$required = " class = 'required-entry  ".$adminValid." '";		
				}
				else		
				{			
					$required = "class = '".$adminValid."'";
				}
				echo " <label for=$name $labelVal >".$this->__($val['name'])."</label>";
				echo "<div class='input-box'><input type='text' name='$name' $required maxlength='$maxlen' /></div>";	
				echo "</div></li>";
			}

			else if($val['type'] == "textarea")
			{
				$required = "";
				$labelVal = "";
				$maxlen = $val['maxlen'];
				$adminValid = $val['validation'];
				$name = str_replace(' ', '_',strtolower($val['name']));	
				echo "<li class='wide'>";
				if($val['required'] == true)
				{
					$labelVal = " class = 'required' ><em>*</em ";
					$required = " class = 'required-entry input-text ".$adminValid." '";	
				}
				echo "<label for=$name $labelVal >".$this->__($val['name'])."</label>";
				echo "<div class='input-box'><textarea name='$name' cols='5' rows='3 id='msg' $required  maxlength=$maxlen ></textarea></div>";
				echo "</li>";
			}

			else if($val['type'] == "select" || $val['type'] == "multiple")
			{
				$required = "";
				$multiSelect = "";
				$labelVal = "";
				$maxlen = $val['maxlen'];
				$name = str_replace(' ', '_',strtolower($val['name']));
				echo "<li>";
				
				if($val['required'] == true)
				{
					$labelVal = " class = 'required' ><em>*</em ";
					$required = " class='required-entry input-text' ";
				}
				
				if($val['type'] == "multiple")
						$multiSelect = " multiple ";
				echo "<label for=$name $labelVal >".$this->__($val['name'])."</label>";
				echo "<div class='input-box'><select class='caret' name='$name' $required $multiSelect >";
				ksort($val['option']);
				foreach($val['option'] as $opt)
				{
					echo "<option value='$opt'>$opt</option>";
				}
				echo "</select>";
				echo "</div></li>";
			}
			else if($val['type'] == "radio" || $val['type'] == "checkbox")
			{
				ksort($val['option']);
				$req=1;
				$type = $val['type'];
				$labelVal = "";
				echo "<li>";
				if($val['required'] == true && $req == 1)
					$labelVal = " class = 'required' ><em>*</em ";
				echo "<label for=$name $labelVal >".$this->__($val['name'])."</label>";
				
				
				echo "<div class='input-box'>";
				foreach($val['option'] as $opt)
				{
					$name = str_replace(' ', '_',strtolower($val['name']));
					$required = "";
					if($val['required'] == true && $req == 1)
							$required = " class='validate-one-required ' ";
					echo "<div class='checkbox'><input type='$type' name='$name' $required value='$opt' /> $opt </div>";
					$req++;
				}
				echo "</div></li>";
			}

		} 
		
	}
	
	public function itemOptions($id)
	{
		//** Option getting Query
		$contactItemOption = Mage::getModel('ecfplus/itemoptions')->getCollection()->addFieldToFilter('item_id', array('eq' => $id))->load()->setOrder('sort_order', 'ASC')->getData();

		if($contactItemOption[0]['title'] == "")
		{
			$valid  = $contactItemOption[0]['validation'];
			$maxlen = $contactItemOption[0]['max_characters'];
			$option = "";
		}
		else
		{
			$valid  = "";
			$maxlen = "";
			unset($option);
			foreach($contactItemOption as $value)
			{
				$option[] = $value['title'];
			}
		}
		
		$returnOptions[0] = $valid;
		$returnOptions[1] = $maxlen;
		$returnOptions[2] = $option;
		
		return $returnOptions;
	}
	
	public function formName($formId)
	{
		$formName = Mage::getModel('ecfplus/multiform')->getCollection()->addFieldToFilter('id', $formId)->addFieldToSelect('name')->getData();		
		$formIdsName = $formName[0]['name']; 
		return $formIdsName;
	}
	
	public function thankyouMessage($formId)
	{
		$thankyouCollection = Mage::getModel('ecfplus/multiform')->getCollection()->addFieldToFilter('id', $formId)->addFieldToSelect('message_field')->getData();		
		$thankyouMessage = $thankyouCollection[0]['message_field']; 
		return $thankyouMessage;
	}
	
	public function formEnable($formId)
	{
		$formStatus = Mage::getModel('ecfplus/multiform')->getCollection()->addFieldToFilter('id', $formId)->addFieldToSelect('status')->getData();		
		$status = $formStatus[0]['status']; 
		if($status == 1)
		{
		   return 1;
		}
		else
		{
		  return 0;
		}
	}
	public function ecfplusPopupOptionsExist($formId)
	{		
		$contactItem = Mage::getModel('ecfplus/items')->getCollection()->addFilter('form_id', $formId)->setOrder('sort_order', 'ASC')->getData();
		return $contactItem;
	}
	
	
	
	public function ecfplusPopupOptions($formId)
	{			
		$contactItemValue = $this->ecfplusPopupOptionsExist($formId);
		
		foreach($contactItemValue as $value)
		{	 
			//** Call the inner option function
			$options = $this->itemOptions($value['item_id']);
			$value['type'] = ($value['type'] == "drop_down") ? "select" : $value['type'] ;
			$value['type'] = ($value['type'] == "area")	? "textarea" : $value['type'] ;
			$value['type'] = ($value['type'] == "field") ? "text" : $value['type'] ;	
			$ecfplusItems[] = array(
						"name" => $value['title'],
						"type" => $value['type'],
						"required" => $value['is_require'],						
						"validation" => $options[0],
						"maxlen" => $options[1],
						"option" => $options[2]					
						);
		}

		//** Sort fields array
		ksort($ecfplusItems);
		
		foreach($ecfplusItems as $val)
		{
			echo "<div class='contact_each_row'><div>";
			
			if($val['type'] == "text")
			{
				$required = "";
				$maxlen = $val['maxlen'];
				$adminValid = $val['validation'];
				$name = str_replace(' ', '_',strtolower($val['name']));
				$namePlaceholder = $val['name'] ;
				if($val['required'] == true)
						$required = " class = 'required-entry ".$adminValid." '";
				echo "<div class='form-fields'><label>".$this->__($val['name'])."";
				if($val['required']){ echo "<em>*</em></label>";} else { echo "</label>"; }
				echo "<input type='text' name='$name' $required maxlength='$maxlen' placeholder='$namePlaceholder'/></div>";	
			}
			else if($val['type'] == "textarea")
			{
				$required = "";
				$maxlen = $val['maxlen'];
				$adminValid = $val['validation'];
				$name = str_replace(' ', '_',strtolower($val['name']));	
				$namePlaceholder = $val['name'] ;
				if($val['required'] == true)
						$required = " class = 'required-entry formMsg".$adminValid." '";
				echo "<div class='form-fields'><label>".$this->__($val['name'])."";
				if($val['required']){ echo "<em>*</em></label>";} else { echo "</label>"; }
				echo "<textarea name='$name' id='msg' $required  maxlength='$maxlen' placeholder='$namePlaceholder'></textarea></div>";
			}
			else if($val['type'] == "select" || $val['type'] == "multiple")
			{
				$required = "";
				$multiSelect = "";
				$maxlen = $val['maxlen'];
				$name = str_replace(' ', '_',strtolower($val['name']));
				if($val['required'] == true)
						$required = " class='required-entry ' ";
				if($val['type'] == "multiple")
						$multiSelect = " multiple ";
				echo "<div class='ddown-options'><label>".$this->__($val['name'])."";
				if($val['required']){ echo "<em>*</em></label>";} else { echo "</label>"; }
				if($val['type'] == "select") echo "<select class='caret' name='$name' $required $multiSelect><option value=''>-- Please Select --</option>";
				else echo "<select name='$name' $required $multiSelect>";
				ksort($val['option']);
				foreach($val['option'] as $opt)
				{
					echo "<option value='$opt'>$opt</option>";
				}
				echo "</select></div>";
			}
			else if($val['type'] == "radio" || $val['type'] == "checkbox")
			{
				ksort($val['option']);
				$req=1;
				$type = $val['type'];
				echo "<div class='radio-options'><label>".$this->__($val['name'])."";
				if($val['required']){ echo "<em>*</em></label>";} else { echo "</label>"; }
				echo "<div class='radio-input'>";
				foreach($val['option'] as $opt)
				{
					$name = str_replace(' ', '_',strtolower($val['name']));
					$required = "";
					if($val['required'] == true && $req == 1)
							$required = " class='validate-one-required ' ";
					echo "<div class='checkbox'><input type='$type' name='$name' $required value='$opt' /> $opt </div><br />";
					$req++;
				}
				echo "</div></div>";
			}
			echo "</div></div> ";	
		}
		
		
	}
}