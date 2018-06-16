<?php
class Cminds_Marketplace_Block_Import_Attributes extends Mage_Core_Block_Template {
    public function _construct() {
        $this->setTemplate('marketplace/import/attributes.phtml');
    }

    public function getReport() {
        return Mage::registry('import_data');
    }

    public function isExists() {
        return (is_array($this->getReport()) && count($this->getReport()) > 0);
    }

    public function getSuccessFull() {
        $success = array();


        foreach($this->getReport() AS $report) {
            if(!$report['success']) continue;
            $success[] = $report;
        }

        return $success;
    }

    public function getFailed() {
        $failed = array();


        foreach($this->getReport() AS $report) {
            if($report['success']) continue;
            $failed[] = $report;
        }

        return $failed;
    }

    public function getMaxImagesCount() {
        return Mage::helper('marketplace')->getMaxImages();
    }
    
    public function isUploadDone() {
        return Mage::registry('upload_done');
    }
    
    public function getSelectedAttributeSetId() {
        return Mage::registry('attributeSetId');
    }
}