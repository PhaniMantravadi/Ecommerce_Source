<?php
require_once(Mage::getModuleDir('controllers','Cminds_Supplierfrontendproductuploader').DS.'SettingsController.php');

class Cminds_Marketplacecustom_SettingsController extends Cminds_Supplierfrontendproductuploader_SettingsController {
    public function documentsAction() {
        if(!Mage::helper('marketplacecustom')->isCustomer()) {
            $this->norouteAction();
            return;
        }
        $this->_renderBlocks(false, true);
    }

    public function uploadDocumentsAction() {
        $customer = Mage::helper('marketplacecustom')->getCustomer();

        $path = Mage::getBaseDir('media') . DS . 'documents' . DS;

        foreach($_FILES AS $k => $file) {
            if (isset($file['name']) and (file_exists($file['tmp_name']))) {
                $dir = $path . $k . '/';

                if(!is_dir($path)) {
                    mkdir($path, 0777, true);
                }
                try {
                    $uploader = new Varien_File_Uploader($k);
                    $uploader->setAllowedExtensions(array('csv','pdf','docx','xlsx'));
                    $uploader->setAllowRenameFiles(false);
                    $uploader->setFilesDispersion(false);

                    $nameSplit = explode('.', $file['name']);
                    $ext = $nameSplit[count($nameSplit) - 1];
                    $newName = $customer->getId(). '-' . time() . '.' . $ext;
                    $customer->setData($k, $newName);
                    $uploader->save($dir, $newName);
                } catch (Exception $e) {
                    Mage::getSingleton('core/session')->addError($e->getMessage());
                    Mage::getSingleton('core/session')->addError(
                        'Please provide files with extension .csv,.pdf,.docx.xlsx'
                    );
                    $this->_redirect('*/settings/documents');
                    return;
                }

            }
        }
        $customer->save();

        $this->getResponse()->setRedirect(Mage::getUrl('*/index/index', array('_secure' => true)));
    }
}
