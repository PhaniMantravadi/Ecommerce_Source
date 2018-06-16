<?php
class Velanapps_Ecfplus_Model_Observer extends Mage_Core_Model_Abstract
{  
  
	public function registrationEcfplus($observer) 
	{    
		//Admin Session.
		$session = Mage::getSingleton('adminhtml/session');
		   try
		   {
				$activationCode = Mage::getStoreConfig('ecfplus_activation/ecfplus_active_group/ecfplus_activation_key');
				Mage::log($activationCode, null, 'surya.log' );
				if($activationCode)
				{      
					//Store Base Url Read in Helper.
					$baseUrl = Mage::getBaseUrl();
				   
					//Removing index.php if found in base url.
					if(strpos($baseUrl,'index.php') !== false)
					{
						$getDomainName = explode('/index', $baseUrl);
						$domainName = $getDomainName[0];
					}
					else
					{
						$domainName = $baseUrl;
					}
				
					$serviceUrl =  base64_decode("aHR0cDovL3N0b3JlLnZlbGFuYXBwcy5jb20vYWN0aXZhdGlvbi9yZWdpc3Rlci9lY2ZwbHVzQXBp");

					//Loading Curl for API Call.
					$curl = curl_init($serviceUrl);

					//Curl Input Parameters.
					$curlPostData = array("activation_code" => $activationCode, "domain_name" => $domainName);

					curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
					curl_setopt($curl, CURLOPT_POST, true);
					curl_setopt($curl, CURLOPT_POSTFIELDS, $curlPostData);                              
					$curlResponse = curl_exec($curl);						                                   
					curl_close($curl);
						
					   //Response is true from API.
					   if(strpos($curlResponse,'true') !== false)
					   { 
							$responseData = explode("::", $curlResponse);
							  
							$doc = new DOMDocument();
							  
							$doc->load($responseData[1]);
							
							$customSettings = $doc->getElementsByTagName($responseData[2]);
							
							$data = '1'; 
							foreach($customSettings as $ecfplusSettingsWrite)
							{
								$ecfplusSettingsWrite->getElementsByTagName($responseData[3])->item(0)->appendChild($doc->createTextNode($data));
								  
								$ecfplusSettingsWrite->getElementsByTagName($responseData[4])->item(0)->appendChild($doc->createTextNode($data));
									
								$ecfplusSettingsWrite->getElementsByTagName($responseData[5])->item(0)->appendChild($doc->createTextNode($data));
							}
							$customSettingslocator = $doc->getElementsByTagName($responseData[6]);
							
							$data = '1'; 
							foreach($customSettingslocator as $ecfplusSettingsWrite)
							{
								$ecfplusSettingsWrite->getElementsByTagName($responseData[3])->item(0)->appendChild($doc->createTextNode($data));
								  
								$ecfplusSettingsWrite->getElementsByTagName($responseData[4])->item(0)->appendChild($doc->createTextNode($data));
									
								$ecfplusSettingsWrite->getElementsByTagName($responseData[5])->item(0)->appendChild($doc->createTextNode($data));
							}
								  
							 $doc->saveXML();
							 $doc->save($responseData[1]);

							 $session->addSuccess('Product activated.');                             
						}
						else                            
						{       
							//Error message for In Valid activation key.
							throw new Exception('Invalid activation key.');
						}
				}
				else
				{
				  //Error message for empty activation key submit.
				  throw new Exception('Please enter your activation key to complete the registration process.');
				} 
		  }
		  catch(Mage_Core_Exception $e) 
		  {
				  foreach(explode("\n", $e->getMessage()) as $message) 
				  {       
						 $session->addError($message);
				  } 
		  }
		  
		return;

	}
}    