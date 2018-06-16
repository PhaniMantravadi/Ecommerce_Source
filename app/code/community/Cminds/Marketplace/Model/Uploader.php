<?php
class Cminds_Marketplace_Model_Uploader extends Mage_Core_Model_Abstract
{

    public function _construct()
    {
        parent::_construct();
        $this->_init('marketplace/uploader');
    }

    public function initiateImportProcess()
    {
        try {
            $importProductFiles = $this->getCollection();
            $importProductFiles->getSelect()->Where(
                'status = ' . Cminds_Marketplace_Helper_Uploader::APPROVED
            );
            $supplierUploaderHelper = Mage::helper('marketplace/uploader');
            foreach ($importProductFiles as $fileData) {
                if ($fileData->getFileName() &&
                    ($fileData->getStatus() != Cminds_Marketplace_Helper_Uploader::COMPLETE)
                ) {
                    $fileData->setStatus(Cminds_Marketplace_Helper_Uploader::WORKING);
                    $fileData->save();
                    try {
                        if ($fileData->getFileType() == Cminds_Marketplace_Helper_Uploader::PRODUCT_FILE_CSV) {
                            $status = $supplierUploaderHelper->importProduts($fileData->getFileName(), $fileData);
                        }
                        if ($fileData->getFileType() == Cminds_Marketplace_Helper_Uploader::ATTRIBUTE_FILE_CSV) {
                            $status = $supplierUploaderHelper->importAttributes($fileData->getFileName(), $fileData);
                        }

                    } catch (Exception $e) {
                        $fileData->setStatus(Cminds_Marketplace_Helper_Uploader::ERROR);
                        $fileData->save();
                        Mage::log(__LINE__, null, 'file_import_cron.log', true);
                        Mage::log($e, null, 'file_import_cron.log', true);
                    }
                }

            }

        } catch (Exception $e) {
            Mage::log(__LINE__, null, 'file_import_cron.log', true);
            Mage::log($e, null, 'file_import_cron.log', true);
        }
    }


}