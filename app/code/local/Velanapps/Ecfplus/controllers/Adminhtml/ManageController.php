<?php
class Velanapps_Ecfplus_Adminhtml_ManageController extends Mage_Adminhtml_Controller_Action
{   
    public function indexAction() 
	{       
		$this->loadLayout();
		$this->_addContent($this->getLayout()->createBlock('ecfplus/adminhtml_manage'));		
        $this->renderLayout();
    }    
}