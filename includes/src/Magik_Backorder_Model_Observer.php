<?php
class Magik_Backorder_Model_Observer {
protected $_bundled_option = array();
  public function onListBlockHtmlBefore($observer) {
    
    $extEnable=Mage::helper('backorder')->isEnabled();
    $extadvanceEnable=Mage::helper('backorder')->isadvanceEnabled();
    $addtocartText=Mage::app()->getStore()->getConfig('bkordersection/bkordergeneral/bktext');
    $changeAddtocart=Mage::app()->getStore()->getConfig('bkordersection/bkordergeneral/changebutton');
    $backorderGlobalnote=Mage::app()->getStore()->getConfig('bkordersection/bkordergeneral/globalnote');
    if($changeAddtocart==1){
	    if($addtocartText != ''){
		$globaladdtocartText=$addtocartText;
	    }else{
		$globaladdtocartText="Backorder";
	    }
    }else{
	    if($addtocartText != ''){
		$globaladdtocartText=$addtocartText;
	    }else{
		$globaladdtocartText="Backorder";

	    }
    }

    $availabilityText=Mage::app()->getStore()->getConfig('bkordersection/bkordergeneral/availabilitytext');
    if($availabilityText!=''){$finalavailabilityText=$availabilityText;}else{$finalavailabilityText='In Stock'; }
    $enableSimple=Mage::app()->getStore()->getConfig('bkordersection/bkordercontent/enablesimple');
    $enableConfigurable=Mage::app()->getStore()->getConfig('bkordersection/bkordercontent/enableconfig');
    $enableBundle=Mage::app()->getStore()->getConfig('bkordersection/bkordercontent/enablebundle');
    $enableGrouped=Mage::app()->getStore()->getConfig('bkordersection/bkordercontent/enablegrouped');
      
    if($extEnable!=1){
	  return false;
    }
    /* Product View Page */
    if ($observer->getBlock() instanceof Mage_Catalog_Block_Product_View) {
	
	
	    $html = $observer->getTransport()->getHtml();
	    $doc = new DomDocument();
	    //$doc->loadHTML($html);
	    $doc->loadHTML('<meta http-equiv="content-type" content="text/html; charset=utf-8">'.$html);
	    $xpath = new DomXpath($doc);
	    $product = Mage::registry('product');
	    $base_url = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_JS);
	    if($product){
	      $_product = Mage::getModel('catalog/product')->load($product->getId());
		$productType=$_product->getTypeID();
		$inventory =  Mage::getModel('cataloginventory/stock_item')->loadByProduct($_product);
		$prodview_addtocart=$_product->getData('backorder_addtocart');
		$prodview_carttext=$_product->getData('backorder_carttext');

		if((int)$inventory->getQty()<=0 && $inventory->getBackorders()>= 1 && $productType =='simple' && $enableSimple==1) {

		    /* Replace Availability Text */
		    //if($availabilityText != ''){ 
			$entrieStock = $xpath->query("//p[@class='availability in-stock']");
			$entrieStock->item(0)->nodeValue=$finalavailabilityText;
		    //}
		    /* backorder availability popup */
		    $entriesBox = $xpath->query("//div[@class='add-to-box']");
			foreach($entriesBox as $entryBox){
			      $availabilityPopup='<p><a id="mgkbkpopup" class="mgkbackpopup"  prod-id="'.$product->getId().'" href="#" onclick="return false;">Backorder availability</a></p>';
			      $template = $doc->createDocumentFragment();
			      $template->appendXML($availabilityPopup);
			      $entryBox->appendChild($template);
			 }
		   /* Replace Add to cart button */

		    if($prodview_addtocart==1 && $prodview_carttext !='' ){
			    $globaladdtocartText=$prodview_carttext;

		    }else{
			  $globaladdtocartText;
		    }
		   $entries = $xpath->query("//div[@class='add-to-cart-buttons']");
		      foreach ($entries as $entry) {
			    $node = $xpath->query("button[@class='button btn-cart']/span/span", $entry);
			    $node->item(0)->nodeValue=$globaladdtocartText;
			}
		   
		}

		if($productType =='grouped' && $enableGrouped==1){
		  $associatedProducts = $_product->getTypeInstance(true)->getAssociatedProducts($product);
		  $assoc_backorder_id=array();
		  $assoc_prod_name=array();
		  foreach($associatedProducts as $assoc_id) {
		      $_assocproduct = Mage::getModel('catalog/product')->load($assoc_id->getId());
		      $productType=$_assocproduct->getTypeID();
		      $inventory =  Mage::getModel('cataloginventory/stock_item')->loadByProduct($_assocproduct);
		      if((int)$inventory->getQty()<=0 && $inventory->getBackorders()>= 1 && $productType =='simple') {
			      array_push($assoc_backorder_id,$assoc_id->getId());
			      array_push($assoc_prod_name,$assoc_id->getName());
		      }
		  }
		 $getBackorderProd=implode(' ',$assoc_prod_name);
		  if(count($assoc_backorder_id) > 0){ 
		      
		      //if($availabilityText != ''){ 
			$entrieStock = $xpath->query("//p[@class='availability in-stock']");
			$entrieStock->item(0)->nodeValue=$finalavailabilityText;
		      //}
		      $entries = $xpath->query("//div[@class='add-to-cart-buttons']");
		      foreach ($entries as $entry) {
			  $node = $xpath->query("button[@class='button btn-cart']/span/span", $entry);
			  $node->item(0)->nodeValue=$globaladdtocartText;
		      }
		  }
		}

		if($enableConfigurable==1 && $productType=='configurable') { 
		    $backordercartChange = array();
		    $backorderstockChange = array();
		    $backorderGNote=array();
		    $simpleProducts = $_product->getTypeInstance(true)->getUsedProducts(null, $_product);
		   
		    foreach ($simpleProducts as $smproduct){ 
			  $inventory =  Mage::getModel('cataloginventory/stock_item')->loadByProduct($smproduct->getId());
			  $cofigview_addtocart=$smproduct->getData('backorder_addtocart');
			  $cofigview_carttext=$smproduct->getData('backorder_carttext');
			if((int)$inventory->getQty()<=0 && $inventory->getBackorders()>= 1) {

			    /* Replace Add to cart button for individual product */
			   if($cofigview_addtocart==1 && $cofigview_carttext !='' ){
				  $finaladdtocartText=$cofigview_carttext;
			   }else{  $finaladdtocartText=$globaladdtocartText; }

			   $finalavailableText=$finalavailabilityText;
			   $finalbgnote=$backorderGlobalnote;
			 }else{
				$finaladdtocartText="Add to Cart";
				$finalavailableText="In Stock";
				$finalbgnote='';
			 }
			  
			 $backordercartChange = $this->array_push_assoc($backordercartChange, $smproduct->getId(), $finaladdtocartText);
			 $backorderstockChange = $this->array_push_assoc($backorderstockChange, $smproduct->getId(), $finalavailableText);
			 $backorderGNote = $this->array_push_assoc($backorderGNote, $smproduct->getId(), $finalbgnote);
		      }
		   
		      $cartchange='<script type="text/javascript" src="'.$base_url.'magikbackorder/mgkbkorderconfigurable.js"></script>';
		      $cartchange.='<script type="text/javascript">var backorderDetail='.Zend_Json::encode($backordercartChange).'</script>';
		      $cartchange.='<script type="text/javascript">var bkorderAvailable='.Zend_Json::encode($backorderstockChange).'</script>';
		      $cartchange.='<script type="text/javascript">var bkorderGnote='.htmlentities(Zend_Json::encode($backorderGNote)).'</script>';

		      $entriesBox1 = $xpath->query("//div[@class='product-view']");
		      foreach($entriesBox1 as $entryBox1){ 
			      $template1 = $doc->createDocumentFragment();
			      $template1->appendXML($cartchange);
			      $entryBox1->appendChild($template1);
		      }
		     
		}//enable

		if($enableBundle==1 && $productType=='bundle') { 

		    $collectionBundle = $product->getTypeInstance(true)->getSelectionsCollection(
		    $product->getTypeInstance(true)->getOptionsIds($product), $product);
		    $defaultStockCart=array();
		    $bkorderdId=array();
		    $cartoptionId=array();
		    $stockoptionId=array();
		    $bkproductId=array();
		    foreach ($collectionBundle as $item) { 
		      $inventoryBundle =  Mage::getModel('cataloginventory/stock_item')->loadByProduct($item->product_id);
		      $eachProd = Mage::getModel('catalog/product')->load($item->product_id);
		      $bundleview_addtocart=$eachProd->getData('backorder_addtocart');
		      $bundleview_carttext=$eachProd->getData('backorder_carttext');
		      if((int)$inventoryBundle->getQty()<=0 && $inventoryBundle->getBackorders()>= 1) {
			  array_push($bkorderdId,$item->selection_id);

			  /* Replace Add to cart button for individual product */
			   if($bundleview_addtocart==1 && $bundleview_carttext !='' ){
				  $finaladdtocartText=$bundleview_carttext;
			   }else{  $finaladdtocartText=$globaladdtocartText; }

			  $finalavailableText=$finalavailabilityText;

			  $entriesBun = $xpath->query("//div[@class='product-shop']");
			      foreach($entriesBun as $entryBun){
				  $availabilitybunPopup='<p> <a id="mgkbkpopup" class="mgkbackpopup"  prod-id="'.$item->product_id.'" href="#" onclick="return false;">'.$item->name.' Backorder availability</a></p>';
				    $templatebun = $doc->createDocumentFragment();
				      $templatebun->appendXML($availabilitybunPopup);
				      $entryBun->appendChild($templatebun);
			      }

		      }else{
			      $finalavailableText='In Stock';
			      $finaladdtocartText="Add to Cart"; 
		      }

		      $cartoptionId = $this->array_push_assoc($cartoptionId, $item->selection_id, $finaladdtocartText);
		      $stockoptionId = $this->array_push_assoc($stockoptionId, $item->selection_id, $finalavailableText);
		      $bkproductId = $this->array_push_assoc($bkproductId, $item->selection_id, $item->product_id);
		    }

		    $defaultStockCart=array($globaladdtocartText, $finalavailabilityText);
		    $bundlejs = '<script type="text/javascript" src="' . $base_url . 'magikbackorder/mgkbkorderbundle.js"></script>';
		    $bundlejs .= '<script type="text/javascript">var defaultstockcart = ' . Zend_Json::encode($defaultStockCart) . '</script>';
		    $bundlejs .= '<script type="text/javascript">var bundlebkId = ' . Zend_Json::encode($bkorderdId) . '</script>';
		    $bundlejs .= '<script type="text/javascript">var cartoptionId = ' . Zend_Json::encode($cartoptionId) . '</script>';
		    $bundlejs .= '<script type="text/javascript">var stockoptionId = ' . Zend_Json::encode($stockoptionId) . '</script>';
		    $bundlejs .= '<script type="text/javascript">var allproductId = ' . Zend_Json::encode($bkproductId) . '</script>';
		    $entriesBox2 = $xpath->query("//div[@class='product-view']");
		      foreach($entriesBox2 as $entryBox2){
			      $template2 = $doc->createDocumentFragment();
			      $template2->appendXML($bundlejs);
			      $entryBox2->appendChild($template2);
		      }
		}//enable

		$html= $doc->saveHTML();
		$observer->getTransport()->setHtml($html);

	}
}
    /* Category Page */
    if (($observer->getBlock() instanceof Mage_Catalog_Block_Product_List)){
	
	/* Enable for Simple Product */
	if($enableSimple==1) {

	    $html = $observer->getTransport()->getHtml();
	    $doc = new DOMDocument();
	    $doc->loadHTML('<meta http-equiv="content-type" content="text/html; charset=utf-8">'.$html);
	    //$doc->loadHTML($html);

	    foreach ($doc->getElementsByTagName('button') as $button) {
		
		$onclick=$button->getAttribute('onclick'); 
		$prod=explode('product/',$onclick);
		$ProductId=explode('/',$prod[1]);
		$_product = Mage::getModel('catalog/product')->load($ProductId[0]);
		$productType=$_product->getTypeID();
		$prod_addtocart=$_product->getData('backorder_addtocart');
		$prod_carttext=$_product->getData('backorder_carttext');

		$inventory =  Mage::getModel('cataloginventory/stock_item')->loadByProduct($_product);

		if((int)$inventory->getQty()<=0 && $inventory->getBackorders()>= 1 && $productType =='simple') {

		    /* Each Product Override Button Text */
		    if($prod_addtocart==1 && $prod_carttext !='' ){

			      $button->setAttribute('title',$prod_carttext);
			      $button->nodeValue = $prod_carttext;
		    }else{
			      $button->setAttribute('title',$globaladdtocartText);
			      $button->nodeValue = $globaladdtocartText;
		    }
		}
	    }
	    $html= $doc->saveHTML();
	    $observer->getTransport()->setHtml($html);
	 }
     }


  }//function close
  function array_push_assoc($array, $key, $value){
    $array[$key] = $value;
    return $array;
  }
}//class close

?>
