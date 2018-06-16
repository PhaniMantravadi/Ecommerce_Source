<?php
class AsifHussain_ShippingCod_Model_Resource_Postcode_Import extends Mage_Core_Model_Resource_Db_Collection_Abstract {
    
	protected function _construct()
    {
        parent::_construct();

        /**
         * Tell Magento the model and resource model to use for
         * this collection. Because both aliases are the same,
         * we can omit the second paramater if we wish.
         */
        $this->_init(
            'asifhussain_shippingcod/postcode'
        );
    } 
	 
   public function uploadAndImport(Varien_Object $object)
   {
        if (empty($_FILES['groups']['tmp_name']['shippingcod_group']['fields']['import']['value'])) {
            return $this;
        }
		
		if (!isset($_FILES['groups'])) {
            return false;
        }
		
		$csvFile = $_FILES['groups']['tmp_name']['shippingcod_group']['fields']['import']['value']; 
		
        if (!empty($csvFile)) {
			$csv = trim(file_get_contents($csvFile));
 			$table = Mage::getSingleton('core/resource')->getTableName('asifhussain_shippingcod/postcode');
            
            $storeCode = $object->getData('store_code');
            $websiteId = $object->getData('scope_id');
            
            $storeIds = array();

            if ($storeCode){
                $storeIds[] = Mage::app()->getStore($storeCode)->getId();
                $websiteId = Mage::app()->getStore($storeCode)->getWebsiteId();
            } else {
                $websiteModel = Mage::app()->getWebsite($websiteId);
                $websiteStores = $websiteModel->getStores();
                
                foreach ($websiteStores as $store) {
                    /*if (!$store->getIsActive()) {
                        continue;
                    }*/
                    $storeIds[] = $store->getId();
                }
            }
			
			if (!empty($csv)) {
                $exceptions = array();
                $csvLines = explode("\n", $csv);
                $csvLine = array_shift($csvLines);
                $csvLine = $this->_getCsvValues($csvLine);
                
                if (count($csvLine) < 1) {
                    $exceptions[0] = Mage::helper('asifhussain_shippingcod')->__('Invalid File Format');
                }
				
				foreach ($csvLines as $k=>$csvLine) {
                    $csvLine = $this->_getCsvValues($csvLine);
                    if (count($csvLine) > 0 && count($csvLine) < 4) {
                        $exceptions[0] = Mage::helper('asifhussain_shippingcod')->__('Invalid File Format'. count($csvLine));
                    }
                }
				
			if (empty($exceptions)) {
                    $data = array();
                    $postCodeToIds = array();
                    $k = 0;
                    
                    $processed = 0;
                    
                    foreach ($csvLines as $k=>$csvLine) {
                        $csvLine = $this->_getCsvValues($csvLine);
						
                        $postcode_id = '';
                        $strpostcode = '';
                        $iscod = '';
                        $isship = '';
                        $daystodeliver = '';
                        
                        $error_found = false;
                        if (trim($csvLine[0]=='')) {
                            $exceptions[] = Mage::helper('asifhussain_shippingcod')->__('Postcode is missing in the row #%s', ($k+1));
                            $error_found = true;
                        } else {
                            $postcode = Mage::getModel('asifhussain_shippingcod/postcode')->setWebsiteId($websiteId)->load(trim($csvLine[0]),'postcode');
                            if ($postcode == null) {
                                $exceptions[] = Mage::helper('asifhussain_shippingcod')->__('Invalid postcode "%s" in the Row #%s (postcode might not exist)', trim($csvLine[0]), ($k+1));
                                $error_found = true;
                            } else {
                                $postcode_id = $postcode->getId();
								$strpostcode = trim($csvLine[0]);
                            }
                        }
						
						if (trim($csvLine[1]=='')) {
                            $exceptions[] = Mage::helper('asifhussain_shippingcod')->__('Is Ship is missing at row #%s', ($k+1));
                            $error_found = true;
                        } else {
                            if (!is_numeric(trim($csvLine[1]))) {
                                $exceptions[] = Mage::helper('asifhussain_shippingcod')->__('Invalid Is Ship format "%s" in the Row #%s', trim($csvLine[1]), ($k+1));
                                $error_found = true;
                            } else {
                                $isship = trim($csvLine[1]);
                            }
                        }

                        if (trim($csvLine[2]=='')) {
                            $exceptions[] = Mage::helper('asifhussain_shippingcod')->__('Is COD is missing at row #%s', ($k+1));
                            $error_found = true;
                        } else {
                            if (!is_numeric(trim($csvLine[2]))) {
                                $exceptions[] = Mage::helper('asifhussain_shippingcod')->__('Invalid Is COD format "%s" in the Row #%s', trim($csvLine[2]), ($k+1));
                                $error_found = true;
                            } else {
                                $iscod = trim($csvLine[2]);
                            }
                        }
						
						
						if (trim($csvLine[3]=='')) {
                            $exceptions[] = Mage::helper('asifhussain_shippingcod')->__('Days to deliver is missing in the row #%s', ($k+1));
                            $error_found = true;
                        } else {
	                        $daystodeliver = trim($csvLine[3]);
                        }
                       
                        if (!$error_found){
	                        $data[] = array('entity_id' => $postcode_id, 'postcode' => $strpostcode, 'isshipable' => $isship, 'iscod' => $iscod, 'daystodeliver' => $daystodeliver);
                        }
                        
                        $k++;
                        
                    }
                }
                
                
                if (sizeof($data)) {
                    foreach($data as $k=>$dataLine) {
                        try {
//                            $this->_getWriteAdapter()->insert($table, $dataLine);
						$model = Mage::getModel('asifhussain_shippingcod/postcode')->setData($dataLine)->save();
                        } catch (Exception $e) {
                            $exceptions[] = Mage::helper('asifhussain_shippingcod')->__('Problem importing Row #%s (customer "%s")', ($k+1), $dataDetails[$k]['entity_id']);
                        }
                    }
                }
                
                if (sizeof($data)){
//                    $this->_getWriteAdapter()->commit();
                    $message = Mage::helper('asifhussain_shippingcod')->__('%s line(s) processed', sizeof($data));
                    Mage::getSingleton('adminhtml/session')->addSuccess($message);
                }

                if (!empty($exceptions)) {
                    Mage::getSingleton('adminhtml/session')->addError(implode("<br />", $exceptions));
                } else {
                    //$message = Mage::helper('asifhussain_shippingcod')->__('%s line(s) processed', sizeof($data));
                    //Mage::getSingleton('adminhtml/session')->addSuccess($message);
                }
            }
        }
        return $this;
		   
		}
	
	 protected function _getCsvValues($string, $separator=",")
    {
        
        $elements = explode($separator, trim($string));
        for ($i = 0; $i < count($elements); $i++) {
            $nquotes = substr_count($elements[$i], '"');
            if ($nquotes %2 == 1) {
                for ($j = $i+1; $j < count($elements); $j++) {
                    if (substr_count($elements[$j], '"') > 0) {
                        // Put the quoted string's pieces back together again
                        array_splice($elements, $i, $j-$i+1, implode($separator, array_slice($elements, $i, $j-$i+1)));
                        break;
                    }
                }
            }
            if ($nquotes > 0) {
                // Remove first and last quotes, then merge pairs of quotes
                $qstr =& $elements[$i];
                $qstr = substr_replace($qstr, '', strpos($qstr, '"'), 1);
                $qstr = substr_replace($qstr, '', strrpos($qstr, '"'), 1);
                $qstr = str_replace('""', '"', $qstr);
            }
            $elements[$i] = trim($elements[$i]);
        }
        return $elements;
        
    }
	
	
}
?>