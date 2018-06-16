<?php
class Magik_Backorder_IndexController extends Mage_Core_Controller_Front_Action {
    public function IndexAction() {
	$this->loadLayout();   
	$this->renderLayout(); 
    }

    public function availabilitynoteAction() { 
	$post = $this->getRequest();
        if (!empty($post)) {
	    $globalNote=Mage::app()->getStore()->getConfig('bkordersection/bkordergeneral/globalnote');
	    $product_id=$post->getParam('prodid');
	    $_product = Mage::getModel('catalog/product')->load($product_id);
	    $bknote=$_product->getData('backorder_note');
	    $bkdate=$_product->getData('backorder_thur_date');
	    echo '<h2>'.$_product->getName().'</h2>';
	    if($globalNote != ''){
		  if($bknote !='' || $bkdate !=''){
			echo $bknote;
			echo $bkdate;
		  }else{ echo $globalNote;}
	    }else{		
		   echo $bknote;
		   echo $bkdate;
	    }

	}
    }
}