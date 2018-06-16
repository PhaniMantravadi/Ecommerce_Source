<?php
class Cminds_Marketplace_Helper_Uploader extends Mage_Core_Helper_Abstract
{
    const CULTURES_WEBSITE_ID = 1;
    const IMPORTED_PRODUCT_CSV_FILE_DIRECTORY = 'import/supplier';
    const ARCHIVE_PRODUCT_CSV_FILE_DIRECTORY = 'import/archive/supplier';
    const IMPORTED_SUPPLIER_INVOICE_FILE_DIRECTORY = 'supplier/invoice';
    const PENDING = 0;
    const APPROVED = 1;
    const WORKING = 2;
    const COMPLETE = 3;
    const ERROR = 4;
    const PRODUCT_FILE_CSV = 1;
    const ATTRIBUTE_FILE_CSV = 2;
    const LOG_FILE_NAME = 'marketplace_import.log';

    const INVOICE_NOTIFICATION_IS_ENABLE     = 'marketplace_invoice/supplier_invoice/is_enable';
    const INVOICE_SENDER_EMAIL  = 'marketplace_invoice/supplier_invoice/sender';
    const BCC_EMAIL_ADDRESS  = 'marketplace_invoice/supplier_invoice/receiver_email_address';
    const INVOICE_NOTIFICATION_TEMPLATE = 'marketplace_invoice/supplier_invoice/email_template';

    private $_setMainPhoto = false;
    private $_usedImagesPaths = array();
    private $_fileImportStatus = false;
    private $_isEditMode = false;
    private $_doProcess = true;
    protected function _getHelper($helper = 'marketplace') {
        return Mage::helper($helper);
    }

    public function getConf($path = null, $storeId = 0){
        if (is_null(path)) return false;
        return Mage::getStoreConfig($path, $storeId);
    }
    /**
     *
     * Get the absolute path of the exported order file directory from configuration
     */
    public function getProductImportedFileDirectory()
    {
        $path = Mage::getBaseDir('var').DS.self::IMPORTED_PRODUCT_CSV_FILE_DIRECTORY.DS;
        $io = new Varien_Io_File();
        $io->checkAndCreateFolder($path);
        return $path;
    }

    /**
     *
     * Get the absolute path of the exported order file directory from configuration
     */
    public function getSupplierImportedFileDirectory()
    {
        $path = Mage::getBaseDir('media').DS.self::IMPORTED_SUPPLIER_INVOICE_FILE_DIRECTORY.DS;
        $io = new Varien_Io_File();
        $io->checkAndCreateFolder($path);
        return $path;
    }
    /**
     *
     * Get the absolute path of the archieve feed directory from configuration
     */
    public function getArchiveDirectory()
    {
        $path = Mage::getBaseDir('var').DS.self::ARCHIVE_PRODUCT_CSV_FILE_DIRECTORY.DS;
        $io = new Varien_Io_File();
        $io->checkAndCreateFolder($path);
        return $path;
    }

    public function importProduts($fileName = null, $fileData= null) {
        try {
            if (!is_null($fileName)) {
                $baseName = $fileName;
                $fileName = $this->getProductImportedFileDirectory().$fileName;

                if (!file_exists($fileName)) {
                    throw Mage::exception('File '.$baseName.' is not found');
                }
                $this->_handleUpload($baseName, $fileData);
                if ($this->_fileImportStatus) {
                    $fileData->setStatus(self::ERROR);
                    $fileData->save();
                } else {
                    $fileData->setStatus(self::COMPLETE);
                    $fileData->save();
                    Mage::helper('sz_notification')->sendProductFileProcessNotification(
                        $fileData,
                        Mage::getModel('customer/customer')->load($fileData->getSupplierId())
                    );
                    $this->archiveExportedOrderFiles($fileName);
                    $this->_fileImportStatus = false;
                }

            }
            return true;
        } catch (Exception $e) {
            throw Mage::exception($e->getMessage());
        }
    }

    public function archiveExportedOrderFiles($fileName = null) {
        if (!is_null($fileName)) {
            $archiveDir = $this->getArchiveDirectory().basename($fileName);
            $io = new Varien_Io_File();
            $io->open(array('path' => $io->dirname($fileName)));
            $io->chmod($fileName,0777);
            try {
                if ($io->mv($fileName, $archiveDir)) {
                }
            } catch (exception $e) {
                throw Mage::exception($e->getMessage());
            }
        }
    }

    private function _handleUpload($fileName = null, $supplierFileInfo)
    {
        if (is_null($fileName)) {
            return;
        }
        $baseName = $fileName;
        $fileName = $this->getProductImportedFileDirectory().$fileName;
        if (!file_exists($fileName)) {
            throw Mage::exception('File '.$baseName.' is not found');
        };
        if (isset($fileName) && ($fileName != NULL)) {
            $importResponse = array();
            $successCount = 0;
            $i = 0;
            $headers = array();
            if (($handle = fopen($fileName, "r")) !== FALSE) {
                if (is_int(Mage::getStoreConfig('marketplace_configuration/csv_import/product_limit')) &&
                    Mage::getStoreConfig('marketplace_configuration/csv_import/product_limit') > 0 &&
                    count(file($fileName)) > Mage::getStoreConfig('marketplace_configuration/csv_import/product_limit') + 1
                ) {
                    Mage::getSingleton('core/session')->addError(
                        Mage::helper('marketplace')->__("Too many products added to import.")
                    );
                } else {
                    while (($data = fgetcsv($handle)) !== FALSE) {
                        if ($i != 0) {
                            $res = $this->_parseCsv($data, $headers, $supplierFileInfo);
                            if ($res['success']) {
                                $successCount++;
                            }
                            $res['line'] = $i;
                            $importResponse[] = $res;
                        } else {
                            $s = $this->validateHeaders($data);
                            if (count($s) > 0) {
                                Mage::getSingleton('core/session')->addError(
                                    Mage::helper('marketplace')->__(
                                        "Attributes doesn't match all required attributes. Missing attribute : " .
                                        $s[0]
                                    )
                                );
                                break;
                            }
                            $headers = $data;
                        }
                        $i++;
                    }
                    fclose($handle);
                }
            }
            $customer = Mage::getModel('customer/customer')->load($supplierFileInfo->getSupplierId());

            $this->_getHelper('marketplace/email')->notifyAdminOnUploadingProducts($customer, $successCount);
        }
    }

    public function validateHeaders($headers)
    {
        $attributes = Mage::getModel('catalog/product_attribute_api')->items(Mage::getStoreConfig('supplierfrontendproductuploader_products/supplierfrontendproductuploader_catalog_config/attribute_set'));

        $required = array();

        /**
         * Internal
         */
        $headers[] = 'created_at';
        $headers[] = 'sku';
        $headers[] = 'sku_type';
        $headers[] = 'status';
        $headers[] = 'tax_class_id';
        $headers[] = 'updated_at';
        $headers[] = 'visibility';
        $headers[] = 'shipment_type';
        $headers[] = 'weight_type';
        $headers[] = 'price_type';
        $headers[] = 'price_view';
        $headers[] = 'weight_type';
        $headers[] = 'links_purchased_separately';
        $headers[] = 'links_title';

        foreach ($attributes as $attribute) {
            if ($attribute['required']) {
                $required[] = $attribute['code'];
            }
        }

        foreach ($headers AS $k => $header) {
            $headers[$k] = $this->_prepareHeader($header);
        }

        return array_values(array_diff($required, $headers));
    }

    private function _prepareHeader($header)
    {
        return str_replace(' (*)', '', $header);
    }

    private function _parseCsv($line, $headers, $supplierFileInfo)
    {
        try {
            $this->_setMainPhoto = false;
            $this->_isEditMode = false;
            $this->_doProcess = true;
            $productModel = $this->_findProduct($headers, $line, $supplierFileInfo);
            if (!$this->_doProcess) {
                $this->logMessage($supplierFileInfo->getFileName().$this->__(' File data is not properly present. SKU is not valid'));
                Mage::getSingleton('adminhtml/session')->addError(
                    $supplierFileInfo->getFileName().$this->__(' File data is not properly present. SKU is not valid')
                );
                $this->_fileImportStatus = true;
                return false;
            }
            $isNew = false;
            if (!$productModel) {
                $isNew = true;
                $productModel = Mage::getModel("catalog/product");
                $productModel->setTypeId('simple');
                $productModel->setWebsiteIds(array(self::CULTURES_WEBSITE_ID));
                $attributeSetId = $supplierFileInfo->getData('attribute_set');
                $productModel->setAttributeSetId($attributeSetId);
                $productModel->setStatus(Mage_Catalog_Model_Product_Status::STATUS_ENABLED);
                $productModel->setVisibility(Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE);
                $productModel->setTaxClassId(Mage::getStoreConfig('supplierfrontendproductuploader_products/supplierfrontendproductuploader_catalog_config/tax_class_id'));
                $productModel->setData('frontendproduct_product_status', Cminds_Supplierfrontendproductuploader_Model_Product::STATUS_PENDING);
                $productModel->setData('creator_id', $supplierFileInfo->getSupplierId());

                if (!Mage::getStoreConfig('supplierfrontendproductuploader_products/supplierfrontendproductuploader_catalog_config/can_define_sku') == 2) {
                    $productModel->setSku(Mage::helper('supplierfrontendproductuploader')->generateSku());
                }
            }
            Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
            $foundCategories = false;
            foreach ($headers AS $i => $header) {
                $missLine = false;
                $attributeCode = trim($this->_prepareHeader($header));
                if (isset($line[$i])) {
                    if (strtolower($attributeCode) == 'category' && $line[$i] != "") {
                        $foundCategories = true;
                        $missLine = true;
                        $categories = $this->_validateCategories($line[$i]);
                        if ($categories) {
                            $productModel->setCategoryIds($categories);
                        }
                    }

                    $value = $this->_validateAttributeValue($attributeCode, $line[$i]);

                    if (strtolower($attributeCode) == 'qty') {
                        if ($this->_isEditMode && !$line[$i]) {
                        } else {
                            $productModel->setStockData(array(
                                'is_in_stock' => ($line[$i] > 0) ? 1 : 0,
                                'qty' => $line[$i]
                            ));
                        }

                    }

                    if ((strtolower($attributeCode) == 'image') && $line[$i]) {

                        $path = $this->_getHelper('supplierfrontendproductuploader')->getImageCacheDir(null);
                        $path = $path . DS . strtolower($line[$i]);
                        $this->_usedImagesPaths[] = $path;
                        if ($path && file_exists($path)) {
                            $attrs = null;

                            if (!$this->_setMainPhoto) {
                                $attrs = array('image', 'small_image', 'thumbnail');
                                $this->_setMainPhoto = true;
                            }
                            $productModel->addImageToMediaGallery($path, $attrs, true, false);
                        }

                    }
                    if (!$missLine) {
                        if($value) {
                            $productModel->setData($attributeCode, $value);
                        } else if($line[$i]) {
                            $productModel->setData($attributeCode, $line[$i]);
                        }
                    }
                } else {
                    if ($this->_isRequired($attributeCode) && !$this->_isEditMode) {
                        $this->logMessage($this->__("Value for attribute : %s is not valid", $attributeCode));
                        return;
                    }
                }
            }
            if (!$foundCategories) {
                $this->logMessage($this->__('No categories found'));
                Mage::getSingleton('adminhtml/session')->addError($this->__('No categories found'));

            }
            $productModel->save();
            if ($isNew) {
                $mediaGallery = $productModel->getMediaGallery();
                if (isset($mediaGallery['images'])) {
                    foreach ($mediaGallery['images'] as $image) {
                        Mage::getSingleton('catalog/product_action')->updateAttributes(array($productModel->getId()), array('image' => $image['file']), 0);
                        break;
                    }
                }
            }
            $this->_removeUsedImages();

            return array('success' => true, 'product_id' => $productModel->getId(), 'sku' => $productModel->getSku(), 'product_name' => $productModel->getName());
        } catch (Exception $e) {
            Mage::log($line, null, 'marketplace_import_data.log');

            $this->_removeUsedImages();
            $this->_fileImportStatus = true;
            if (method_exists($e, 'getAttributeCode')) {
                return array('success' => false, 'message' => $e->getMessage(), 'attribute_code' => $e->getAttributeCode());
            } else {
                return array('success' => false, 'message' => $e->getMessage(), 'attribute_code' => 'unknown');
            }
        }
    }

    private function _removeUsedImages()
    {
        foreach ($this->_usedImagesPaths AS $path) {
            if (file_exists($path)) {
                unlink($path);
            }
        }
    }

    private function _isRequired($attribute_code)
    {
        $attributeModel = Mage::getSingleton("eav/config")->getAttribute('catalog_product', $attribute_code);
        return $attributeModel->getIsRequired();
    }

    protected function _findProduct($headers, $line, $supplierFileInfo)
    {
        $foundIdValue = false;
        $foundSkuValue = false;
        $productOrgSku = '';
        foreach ($headers AS $i => $header) {

            if (strtolower($this->_prepareHeader($header)) == 'product_id') {
                $foundIdValue = $line[$i];
            }
            if (strtolower($this->_prepareHeader($header)) == 'sku') {
                $foundSkuValue = $line[$i];
            }

        }

        if ($foundIdValue || is_numeric($foundIdValue)) {
            $product = Mage::getModel('catalog/product')->load($foundIdValue);
            $productOrgSku = $product->getSku();
            $this->_isEditMode = true;
        } else if ($foundSkuValue) {
            $product = Mage::getModel('catalog/product')->loadByAttribute('sku', $foundSkuValue);
            $this->_isEditMode = true;
        }  else {
            $this->_isEditMode = false;
            return false;
        }

        if (!$product->getId()) {
            $this->logMessage($this->__("Product does not exists"));
            Mage::getSingleton('adminhtml/session')->addError($this->__('Product does not exists'));
            return false;
        }
        if ($productOrgSku != $foundSkuValue){
            $this->_doProcess = false;
            return false;
        }

        if ($product->getCreatorId() != $supplierFileInfo->getSupplierId()) {
            $this->logMessage($this->__("Product does not exists"));
            Mage::getSingleton('adminhtml/session')->addError($this->__('Product does not exists'));
            return false;
        }

        return $product;
    }

    public function logMessage($message = null)
    {
        if (is_null($message)) return;
        Mage::log($message, null, self::LOG_FILE_NAME);
    }
    private function _validateCategories($categories_ids)
    {
        $categories = explode(';', $categories_ids);
        $validCategoriesIds = array();

        $isValid = false;
        foreach ($categories AS $category) {
            $categoryModel = Mage::getModel('catalog/category')->loadByAttribute('name', $category);
            if ($categoryModel && $categoryModel->getId()) {
                $isValid = true;
                $validCategoriesIds[] = $categoryModel->getId();
            }
        }

        if (!$isValid && !$this->_isEditMode) {
            $this->logMessage($this->__('No valid category'));
            Mage::getSingleton('adminhtml/session')->addError($this->__('No valid category'));

            return;
        }

        return $validCategoriesIds;
    }

    private function _validateAttributeValue($attribute_code, $value)
    {
        $attributeModel = Mage::getSingleton("eav/config")->getAttribute('catalog_product', $attribute_code);

        if ($attributeModel->getIsRequired() && $value == '' && !$this->_isEditMode) {
            $this->logMessage("Attribute " . $attribute_code . " is required");
            Mage::getSingleton('adminhtml/session')->addError($this->__("Attribute " . $attribute_code . " is required"));
            return;
        }

        if ($attributeModel->getFrontendInput() == 'select') {

            if($value != '') {
                $attribute = Mage::getModel('catalog/resource_eav_attribute')->load($attributeModel->getId());
                $attributeOptions = $attribute->getSource()->getAllOptions(false);
                $availableLabels = array();

                foreach ($attributeOptions AS $attributeOption) {
                    $availableLabels[strtolower($attributeOption['label'])] = $attributeOption['value'];
                }

                if (count($availableLabels) > 0) {
                    if (!in_array(strtolower($value), array_keys($availableLabels))) {
                        $this->logMessage("Value of attribute " . $attribute_code . " is not valid . Value : " . $value);
                        Mage::getSingleton('adminhtml/session')->addError(
                            "Value of attribute " . $attribute_code . " is not valid . Value : " . $value
                        );
                        return;
                    }
                }

                return $availableLabels[strtolower($value)];
            }
        }

        if ($attributeModel->getBackendType() == 'decimal') {
            if($value != '') {
                if (!is_numeric($value)) {
                    $this->logMessage("Value of attribute " . $attribute_code . " is not valid. Should be numeric.");
                    Mage::getSingleton('adminhtml/session')->addError(
                        "Value of attribute " . $attribute_code . " is not valid. Should be numeric."
                    );

                    return false;
                }
            }
        }

        return false;
    }


    public function importAttributes($fileName = null, $fileData= null)
    {
        try {
            if (!is_null($fileName)) {
                $baseName = $fileName;
                $fileName = $this->getProductImportedFileDirectory().$fileName;

                if (!file_exists($fileName)) {
                    throw Mage::exception('File '.$baseName.' is not found');
                }
                $this->_handleAttrUpload($baseName, $fileData);
                $this->archiveExportedOrderFiles($fileName);
            }
            return true;
        } catch (Exception $e) {
            throw Mage::exception($e->getMessage());
        }
    }
    private function _handleAttrUpload($fileName = null, $supplierFileInfo)
    {
        if (is_null($fileName)) {
            return;
        }

        $baseName = $fileName;
        $fileName = $this->getProductImportedFileDirectory().$fileName;
        if (!file_exists($fileName)) {
            throw Mage::exception('File '.$baseName.' is not found');
        };

        if (isset($fileName) && ($fileName != NULL)) {
            if (($handle = fopen($fileName, "r")) !== FALSE) {
                $headers = array_flip(fgetcsv($handle, 4086, ","));
                while (($data = fgetcsv($handle)) !== FALSE) {
                    $attrData = array();
                    foreach ($headers as $key => $value) {
                        if (isset($data[$value])) {
                            $attrData[$this->_prepareHeader($key)] = $data[$value];
                        }
                    }
                    $this->_createAttribute($attrData, $supplierFileInfo->getAttributeSet());
                }
                fclose($handle);

            }
        }
    }
    private function _createAttribute($attributeInfo = null, $attributeSetId = null) {
        try {
            if (is_null($attributeInfo) || is_null($attributeSetId)) {
                return;
            }
            if (isset($attributeInfo['code']) && $attributeInfo['code']) {
                $attributeModel = Mage::getSingleton("eav/config")->getAttribute(
                    'catalog_product', $attributeInfo['code']
                );

                if ($attributeModel->getId()) {
                    $this->_findAttribute($attributeInfo, $attributeSetId);
                    return;
                }
            }

            $_attribute_data = array(
                'attribute_code' => $attributeInfo['code'],
                'is_global' => '1',
                'frontend_input' => isset($attributeInfo['frontend_input'])?$attributeInfo['frontend_input']:'text', //'boolean',
                'default_value_text' => '',
                'default_value_yesno' => '0',
                'default_value_date' => '',
                'default_value_textarea' => '',
                'is_unique' => '0',
                'is_required' => '0',
                'is_configurable' => '0',
                'is_searchable' => '0',
                'is_visible_in_advanced_search' => '0',
                'is_comparable' => '0',
                'is_used_for_price_rules' => '0',
                'is_wysiwyg_enabled' => '0',
                'is_html_allowed_on_front' => '1',
                'is_visible_on_front' => isset($attributeInfo['is_visible_on_front'])?$attributeInfo['is_visible_on_front']:'0',
                'used_in_product_listing' => '0',
                'used_for_sort_by' => '0',
                'frontend_label' => $attributeInfo['frontend_label'],
            );
            if (isset($attributeInfo['frontend_input']) && strtolower($attributeInfo['frontend_input']) == 'select') {
                if (isset($attributeInfo['option']) && $attributeInfo['option']) {
                    $_attribute_data['option'] = array (
                        'values' => explode(';', $attributeInfo['option'])
                    );
                }

            }
            $model = Mage::getModel('catalog/resource_eav_attribute');

            $_attribute_data['is_configurable'] = 0;
            $_attribute_data['is_filterable'] = 0;
            $_attribute_data['is_filterable_in_search'] = 0;

            if (is_null($model->getIsUserDefined()) || $model->getIsUserDefined() != 0) {
                $_attribute_data['backend_type'] = $model->getBackendTypeByInput($_attribute_data['frontend_input']);
            }

            $model->addData($_attribute_data);

            $model->setEntityTypeId(Mage::getModel('eav/entity')->setType('catalog_product')->getTypeId());
            $model->setIsUserDefined(1);

            try {
                $model->save();
                $attributeModel = Mage::getSingleton("eav/config")->getAttribute(
                    'catalog_product', $attributeInfo['code']
                );
                $setup = new Mage_Eav_Model_Entity_Setup('core_setup');
                $attribute_group_id = $setup->getAttributeGroupId('catalog_product', $attributeSetId, 'General');
                $setup->addAttributeToSet('catalog_product',$attributeSetId, $attribute_group_id, $attributeModel->getId());
            } catch (Exception $e) {
                $this->logMessage(
                    $this->__('Sorry, error occured while trying to save the attribute. Error: '.$e->getMessage())
                );
            }

        } catch (Exception $e) {
            $this->logMessage($e->getMessage());
        }
    }

    private function _findAttribute($attributeInfo = null, $attributeSetId = null)
    {
        try {
            if (is_null($attributeInfo) || is_null($attributeSetId)) {
                return;
            }
            if (isset($attributeInfo['code']) && $attributeInfo['code']) {
                $attributeModel = Mage::getSingleton("eav/config")->getAttribute(
                    'catalog_product', $attributeInfo['code']
                );
                $attr_model = Mage::getModel('catalog/resource_eav_attribute');
                $attr_model->load($attributeModel->getId());
                $data = array();
                if (isset($attributeInfo['is_visible_on_front']) && $attributeInfo['is_visible_on_front']) {
                    $data['is_visible_on_front'] = 1;
                } else {
                    $data['is_visible_on_front'] = 0;
                }
                if (isset($attributeInfo['frontend_label']) && $attributeInfo['frontend_label']) {
                    $data['frontend_label'] = $attributeInfo['frontend_label'];
                }
                if (isset($attributeInfo['frontend_input']) && strtolower($attributeInfo['frontend_input']) == 'select') {
                    if (isset($attributeInfo['option']) && $attributeInfo['option']) {
                        $values = explode(';', $attributeInfo['option']);
                        foreach ($values as $value) {
                            $this->addAttributeValue($attributeInfo['code'], $value);
                        }
                    }

                }
                try {
                     $attr_model->save();
                     $session = Mage::getSingleton('adminhtml/session');
                     $session->addSuccess(
                         Mage::helper('catalog')->__('The product attribute has been saved.'));
                     Mage::app()->cleanCache(array(Mage_Core_Model_Translate::CACHE_TAG));
                     $session->setAttributeData(false);
                     return true;
                 } catch (Exception $e) {
                     $session->addError($e->getMessage());
                     $session->setAttributeData($data);
                     return;
                 }
            }
        } catch (Exception $e) {
            $this->logMessage($e->getMessage());
            return;
        }
    }

    public function addAttributeValue($arg_attribute, $arg_value)
    {
        $attribute_model        = Mage::getModel('eav/entity_attribute');
        $attribute_code         = $attribute_model->getIdByCode('catalog_product', $arg_attribute);
        $attribute              = $attribute_model->load($attribute_code);
        if(!$this->attributeValueExists($arg_attribute, $arg_value))
        {
            $value['option'] = array($arg_value,$arg_value);
            $result = array('value' => $value);
            $attribute->setData('option',$result);
            $attribute->save();
        }

    }

    public function attributeValueExists($arg_attribute, $arg_value)
    {
        $attribute_model        = Mage::getModel('eav/entity_attribute');
        $attribute_options_model= Mage::getModel('eav/entity_attribute_source_table') ;

        $attribute_code         = $attribute_model->getIdByCode('catalog_product', $arg_attribute);
        $attribute              = $attribute_model->load($attribute_code);

        $attribute_table        = $attribute_options_model->setAttribute($attribute);
        $options                = $attribute_options_model->getAllOptions(false);

        foreach($options as $option)
        {
            if ($option['label'] == $arg_value)
            {
                return $option['value'];
            }
        }

        return false;
    }
}
