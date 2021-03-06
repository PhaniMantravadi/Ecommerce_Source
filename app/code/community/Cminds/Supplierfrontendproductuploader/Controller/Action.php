<?php

class Cminds_Supplierfrontendproductuploader_Controller_Action extends Mage_Core_Controller_Front_Action {
    protected $forceHeader = false;
    protected $forceFooter = false;
    protected function _renderBlocks($isForm = false, $allowedAction = false) {
        $includejQuery      = Mage::getStoreConfig('supplierfrontendproductuploader_catalog/supplierfrontendproductuploader_presentation/include_jquery');
        $this->loadLayout();

        if($isForm) {
            $this->getLayout()->getBlock('head')->addItem('skin_js', 'js/supplierfrontendproductuploader/wysihtml5-0.3.0.min.js');
        }

        if($includejQuery) {
            $this->getLayout()->getBlock('head')->addItem('skin_js', 'js/supplierfrontendproductuploader/jquery-1.11.0.min.js');
        }

        $this->getLayout()->getBlock('head')->addItem('skin_js', 'js/supplierfrontendproductuploader/no-conflict.js');
        $this->getLayout()->getBlock('head')->addItem('skin_js', 'js/supplierfrontendproductuploader/bootstrap.min.js');
        $this->getLayout()->getBlock('head')->addItem('skin_js', 'js/supplierfrontendproductuploader/plot/jquery.flot.min.js');
        $this->getLayout()->getBlock('head')->addItem('skin_js', 'js/supplierfrontendproductuploader/plot/jquery.flot.time.min.js');

        if($isForm) {
            $this->getLayout()->getBlock('head')->addItem('skin_js', 'js/supplierfrontendproductuploader/bootstrap-datepicker.js');
            $this->getLayout()->getBlock('head')->addItem('skin_js', 'js/supplierfrontendproductuploader/wysiwyg/bootstrap-wysihtml5.js');
            $this->getLayout()->getBlock('head')->addItem('skin_js', 'js/supplierfrontendproductuploader/jquery.ui.widget.js');
            $this->getLayout()->getBlock('head')->addItem('skin_js', 'js/supplierfrontendproductuploader/jquery.iframe-transport.js');
            $this->getLayout()->getBlock('head')->addItem('skin_js', 'js/supplierfrontendproductuploader/jquery.fileupload.js');
            
            $this->getLayout()->getBlock('head')->addCss('css/supplierfrontendproductuploader/bootstrap-wysihtml5.css');
            $this->getLayout()->getBlock('head')->addCss('css/supplierfrontendproductuploader/datepicker.css');
        }

        $footerVisibility   = Mage::getStoreConfig('supplierfrontendproductuploader_catalog/supplierfrontendproductuploader_presentation/show_footer');
        $headerVisibility   = Mage::getStoreConfig('supplierfrontendproductuploader_catalog/supplierfrontendproductuploader_presentation/show_header');

        if(
            $footerVisibility == Cminds_Supplierfrontendproductuploader_Model_Config_Source_Presentation_Visibility::DONT_SHOW &&
            $headerVisibility == Cminds_Supplierfrontendproductuploader_Model_Config_Source_Presentation_Visibility::DONT_SHOW &&
            !$this->forceHeader &&
            !$this->forceFooter
        ) {
            $this->getLayout()->getBlock('head')->removeItem('skin_css', 'css/styles.css');
        } else {
            $this->getLayout()->getBlock('root')->addBodyClass('supplierfrontendproductuploader-body');
        }

        if(!$this->forceHeader) {
            switch($headerVisibility) {
                case Cminds_Supplierfrontendproductuploader_Model_Config_Source_Presentation_Visibility::SHOW_CUSTOM:
                    $this->getLayout()->getBlock('root')->addBodyClass('supplierfrontendproductuploader-with-custom-header');
                    $this->getLayout()->getBlock('header')->setTemplate('supplierfrontendproductuploader/page/html/header.phtml');
                    break;
                case Cminds_Supplierfrontendproductuploader_Model_Config_Source_Presentation_Visibility::DONT_SHOW:
                    $this->getLayout()->getBlock('root')->unsetChild('header');
                    break;
                default :
                    $this->getLayout()->getBlock('root')->addBodyClass('supplierfrontendproductuploader-with-default-header');
                    break;
            }
        } else {
            $this->getLayout()->getBlock('root')->addBodyClass('supplierfrontendproductuploader-with-default-header');
        }

        if(!$this->forceFooter) {
            switch ($footerVisibility) {
                case Cminds_Supplierfrontendproductuploader_Model_Config_Source_Presentation_Visibility::SHOW_CUSTOM:
                    $this->getLayout()->getBlock('root')->addBodyClass('supplierfrontendproductuploader-with-custom-footer');
                    $this->getLayout()->getBlock('footer')->setTemplate('supplierfrontendproductuploader/page/html/footer.phtml');
                    break;
                case Cminds_Supplierfrontendproductuploader_Model_Config_Source_Presentation_Visibility::DONT_SHOW:
                    $this->getLayout()->getBlock('root')->unsetChild('footer');
                    break;
                default :
                    $this->getLayout()->getBlock('root')->addBodyClass('supplierfrontendproductuploader-with-default-footer');
                    break;
            }
        } else {
            $this->getLayout()->getBlock('root')->addBodyClass('supplierfrontendproductuploader-with-default-footer');
        }
        $helper = $this->_getHelper('marketplace');

        if (!$helper->isSupplierApproved()) {
            if (!$allowedAction) {
                $this->getLayout()->getBlock('content')->unsetChildren();
                $navigationBlock = $this->getLayout()->createBlock(
                    'supplierfrontendproductuploader/navbar',
                    'supplierfrontendproductuploader_nav',
                    array('template' => 'marketplace/nav.phtml')
                );
                $noAccessBlock = $this->getLayout()->createBlock(
                    'Mage_Core_Block_Template',
                    'no_access',
                    array('template' => 'marketplace/noaccess.phtml')
                );
                $this->getLayout()->getBlock('content')->append($navigationBlock);
                $this->getLayout()->getBlock('content')->append($noAccessBlock);
            }

        }
        $this->renderLayout();
    }
    
    protected function _getHelper($helper = 'supplierfrontendproductuploader') {
        return Mage::helper($helper);
    }
    
    protected function _getSupplierHelper() {
        return Mage::helper('supplierfrontendproductuploader');
    }
}
