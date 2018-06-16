<?php
class Velanapps_Ecfplus_IndexController extends Mage_Core_Controller_Front_Action
{
	Public function addAction()
	{	
		$ecfplusHelper = Mage::helper('ecfplus/data');
		$adminEmail = $ecfplusHelper->adminEmail();
		$adminName = $ecfplusHelper->adminName(); 
		$data = $this->getRequest()->getParams('ecfplusform');		
		$storeId = $data['storeid'];
		$formId = $data['formid'];
		
		$formName = Mage::getModel('ecfplus/multiform')->getCollection()->addFieldToFilter('id', $formId)->addFieldToSelect('name')->getData();
		$formIdsName = $formName[0]['name']; 		
		
		$customerMail = 'disabled';
		$emailEnabled = Mage::getModel('ecfplus/multiform')->getCollection()->addFieldToFilter('enable_email', array('eq' => 1))->addFieldToFilter('id', array('eq'=>$formId))->getData(); 
		if(!empty($emailEnabled))
		{
			$emailFieldName = Mage::getModel('ecfplus/items')->getCollection()->addFieldToFilter('is_mail', array('eq' => 1))->addFieldToFilter('form_id', array('eq'=>$formId))->getData();
			if(!empty($emailFieldName))
			{
				$emailFieldTitle = strtolower($emailFieldName[0]['title']);
				$customerMail = 'enabled';
			}
		}
		foreach($data as $key=>$value)
		{
		if(($key != 'formid' ) &&($key != 'storeid'))
			{
			if($key == $emailFieldTitle ){$customerMailId = $value; }	
				$val = $value;
				if(is_array($value))
				{
					$val = "";
					foreach($value as $k=>$v)
					{
						$val .= $v . ',';
						unset($value);
					}
					$val = substr($val,0,-1);
				}
				$key = ucwords(str_replace('_', ' ',($key)));
				$ecfDataBody.= "<b>".$key."</b>:".$val." <br/>";
				$ecfData[$key] = $val;
			}
		}
		
		$enCodeData = serialize($ecfData);		
		$emailTemplateVariables['contact'] = $ecfDataBody;
		$adminTemplate  = Mage::getModel('core/email_template')->loadDefault('ecfplus_admin_template');
		$adminEmailTemplate = $adminTemplate->getProcessedTemplate($emailTemplateVariables);
		// Send the EMail to Notify
		$mail = Mage::getModel('core/email')
			->setToName($adminName)
			->setToEmail($adminEmail)
			->setBody($adminEmailTemplate)
			->setSubject($formIdsName)
			->setFromEmail($data['email'])
			->setFromName(Mage::app()->getStore()->getName())
			->setType('html');
		try
		{				
			$manageData = Mage::getModel('ecfplus/manage')->setSubfields($enCodeData)->setStoreId($storeId)->setFormId($formId)->save();
			//Confimation E-Mail Send
			$mail->send();
		}
		catch(Exception $error)
		{
			Mage::getSingleton('adminhtml/session')->addError($error->getMessage());
			return false;
		}	
		$customerTemplate  = Mage::getModel('core/email_template')->loadDefault('ecfplus_customer_template');
		$customerEmailTemplate = $customerTemplate->getProcessedTemplate();
		
		if($customerMail == 'enabled')
		{	
			$customerMail = Mage::getModel('core/email')
						->setToName($emailFieldTitle)
						->setToEmail($customerMailId)
						->setBody($customerEmailTemplate)
						->setSubject($formIdsName)
						->setFromEmail($data['email'])
						->setFromName(Mage::app()->getStore()->getName())
						->setType('html');
			try
			{				
				//Confimation E-Mail Send
				$customerMail->send();
			}
			catch(Exception $error)
			{
				Mage::getSingleton('adminhtml/session')->addError($error->getMessage());
				return false;
			}		
		}
		return;
	}
}
?>