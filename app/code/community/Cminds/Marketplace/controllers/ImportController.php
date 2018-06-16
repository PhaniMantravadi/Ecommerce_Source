<?php

class Cminds_Marketplace_ImportController extends Cminds_Marketplace_Controller_Action
{
    private $_setMainPhoto = false;
    private $_usedImagesPaths = array();
    private $_fileType = Cminds_Marketplace_Helper_Uploader::PRODUCT_FILE_CSV;
    public function preDispatch()
    {
        parent::preDispatch();
        $hasAccess = $this->_getHelper()->hasAccess();

        if (!$hasAccess) {
            $this->getResponse()->setRedirect($this->_getHelper('supplierfrontendproductuploader')->getSupplierLoginPage());
        }
    }

    public function indexAction()
    {
        $this->_renderBlocks();
    }

    public function productsAction()
    {

        if (Mage::getStoreConfig('marketplace_configuration/csv_import/csv_import_enabled') == 1) {
            $this->_storeSupplierFileInfo();
            foreach ($_FILES['files']['name'] AS $key => $file) {
                $this->_uploadImage($key);
            }
            $this->_renderBlocks(false, false, true);
        } else {
            $this->force404();
        }

    }

    public function statusAction() {
        $this->_renderBlocks(false, true);
    }
    private function _storeSupplierFileInfo() {
        if (isset($_FILES['file']['name']) && ($_FILES['file']['tmp_name'] != NULL)) {
            if (!$this->_validateSalt()) return false;
            $vendorHelper = Mage::helper('marketplace/uploader');
            $destinationPath = $vendorHelper->getProductImportedFileDirectory();
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 777, true);
            }
            try {

                $fileName = date('ymdhms') . '-product.csv';
                $uploader = new Varien_File_Uploader($_FILES['file']);
                $uploader->setAllowedExtensions(array('csv'));
                $uploader->setAllowRenameFiles(false);
                $uploader->setFilesDispersion(false);
                if ($uploader->save($destinationPath, $fileName)) {
                    $supplierFileModel = Mage::getModel('marketplace/uploader');
                    $supplierFileModel->setSupplierId(Mage::getSingleton('customer/session')->getCustomerId());
                    $supplierFileModel->setFileName($fileName);
                    $supplierFileModel->setFileType($this->_fileType);
                    $supplierFileModel->setAttributeSet($this->getRequest()->getParam('attributeSetId'));
                    $supplierFileModel->setStatus(Cminds_Marketplace_Helper_Uploader::PENDING);
                    if ($supplierFileModel->save()) {
                        Mage::getModel('core/session')->addSuccess(
                            $_FILES['file']['name'].' has been uploaded in system and will get process soon.'
                        );
                        Mage::helper('sz_notification')->sendProductFileUploadNotification(
                            $supplierFileModel,
                            Mage::getModel('customer/customer')->load($supplierFileModel->getSupplierId())
                        );
                    }
                }
            } catch (Exception $e) {
                Mage::log($e, null, 'file-import-product.log', true);
            }
        }

    }
    public function attributesAction()
    {

        if (Mage::getStoreConfig('marketplace_configuration/csv_import/csv_import_enabled') == 1) {
            $this->_fileType = Cminds_Marketplace_Helper_Uploader::ATTRIBUTE_FILE_CSV;
            $this->_storeSupplierFileInfo();
            $this->_renderBlocks(false, false, true);
        } else {
            $this->force404();
        }

    }

    public function downloadAttributeCsvAction()
    {
        $avoidAttributes = array('created_at', 'updated_at', 'sku_type', 'price_type', 'weight_type', 'shipment_type', 'links_purchased_separately', 'links_title', 'price_view', 'url_key', 'url_path', 'creator_id', 'tax_class_id', 'visibility', 'status', 'admin_product_note', 'supplier_actived_product', 'frontendproduct_product_status',);
        header("Content-type: text/csv");
        header("Content-Disposition: attachment; filename=attribute_schema.csv");
        header("Pragma: no-cache");
        header("Expires: 0");
        $attributesCollection = array(
            'frontend_label (*)',
            'code (*)',
            'frontend_input',
            'is_visible_on_front',
            'option'
        );
        echo implode(',', $attributesCollection);


    }


    public function downloadProductCsvAction()
    {
        $avoidAttributes = array('created_at', 'updated_at', 'sku_type', 'price_type', 'weight_type', 'shipment_type', 'links_purchased_separately', 'links_title', 'price_view', 'url_key', 'url_path', 'creator_id', 'tax_class_id', 'visibility', 'status', 'admin_product_note', 'supplier_actived_product', 'frontendproduct_product_status',);
        header("Content-type: text/csv");
        header("Content-Disposition: attachment; filename=products_schema.csv");
        header("Pragma: no-cache");
        header("Expires: 0");

        $attributeSetId = $this->getRequest()->getParam('attributeSetId');


        $api = Mage::getModel('catalog/product_attribute_api');
        $attributes = $api->items($attributeSetId);
        $attributesCollection = array();
        //$attributesCollection[] = 'product_id';
        $attributesCollection[] = '"seller_sku"';

        foreach ($attributes as $_attribute) {
            if (in_array($_attribute['code'], $avoidAttributes)) continue;

            if ($_attribute['code'] == 'sku') {
                if (!Mage::getStoreConfig('supplierfrontendproductuploader_products/supplierfrontendproductuploader_catalog_config/can_define_sku') == 2) {
                    continue;
                }
            }

            if ($_attribute['required'] == 1) {
                $str = trim($_attribute['code']);
                $str .= ($_attribute['required'] == 1) ? ' (*)' : '';

                $attributesCollection[] = '"' . $str . '"';
            } else {
                try {
                    $model = Mage::getResourceModel('catalog/eav_attribute')
                        ->setEntityTypeId(Mage::getModel('eav/entity')->setType('catalog_product')->getTypeId())
                        ->load($_attribute['code'], 'attribute_code');
                    if ($model->getData('is_user_defined') && (strstr($model->getData('apply_to'), 'simple') || !$model->getData('apply_to'))) {
                        $str = trim($_attribute['code']);
                        $attributesCollection[] = '"' . $str . '"';
                    }
                } catch (Exception $e) {

                }
            }
        }
        $attributesCollection[] = '"seller_tax_type"';
        $attributesCollection[] = '"seller_tax_rate"';
        $attributesCollection[] = '"seller_tax_type_1"';
        $attributesCollection[] = '"seller_tax_rate_1"';
        $attributesCollection[] = '"category (*)"';
        $attributesCollection[] = '"qty (*)"';

        for ($i = 0; $i < Mage::getStoreConfig('supplierfrontendproductuploader_products/supplierfrontendproductuploader_catalog_config/images_count'); $i++) {
            $attributesCollection[] = '"image"';
        }

        echo implode(',', $attributesCollection);


    }

    public function exportAttributeCsvAction()
    {
        $avoidAttributes = array(
            'created_at', 'page_layout', 'country_of_manufacture', 'updated_at', 'sku_type',
            'price_type', 'weight_type', 'shipment_type', 'links_purchased_separately', 'links_title',
            'price_view', 'url_key', 'url_path', 'creator_id', 'tax_class_id', 'visibility', 'status',
            'admin_product_note', 'supplier_actived_product', 'frontendproduct_product_status',
            'thumbnail_label', 'small_image_label', 'image_label', 'category_ids', 'price', 'group_price',
            'special_price', 'special_from_date', 'special_to_date', 'cost', 'tier_price', 'minimal_price',
            'msrp_enabled', 'msrp_display_actual_price_type', 'msrp', 'meta_title', 'meta_keyword',
            'meta_description', 'is_recurring', 'recurring_profile', 'custom_design', 'custom_design_from',
            'custom_design_to', 'custom_layout_update', 'options_container', 'gift_message_available',
            'news_from_date', 'sku', 'news_from_date', 'name', 'description', 'short_description',
            'news_from_date', 'news_to_date'
        );
        header("Content-type: text/csv");
        header("Content-Disposition: attachment; filename=attributes.csv");
        header("Pragma: no-cache");
        header("Expires: 0");

        $attributeSetId = $this->getRequest()->getParam('attributeSetId');


        $api = Mage::getModel('catalog/product_attribute_api');
        $attributes = $api->items($attributeSetId);

        $attributesCollection = array(
            'frontend_label (*)',
            'code (*)',
            'frontend_input',
            'is_visible_on_front',
            'option'
        );
        echo implode(',', $attributesCollection);
        echo "\r\n";
        foreach ($attributes as $_attribute) {
            if (in_array($_attribute['code'], $avoidAttributes)) continue;
            $attributeModel = Mage::getModel('eav/entity_attribute')->loadByCode(
                'catalog_product', $_attribute['code']
            );
            if (!$attributeModel->getData('frontend_label')) {
               continue;
            }
            $attrInfo = array();
            $attrInfo[] = $attributeModel->getData('frontend_label')?$attributeModel->getData('frontend_label'):'';
            $attrInfo[] = $_attribute['code']?$_attribute['code']:'';
            $attrInfo[] = $attributeModel->getData('frontend_input')?$attributeModel->getData('frontend_input'):'';
            $attrInfo[] = $attributeModel->getData('is_visible_on_front')?$attributeModel->getData('is_visible_on_front'):'';
            if ($attributeModel->getData('frontend_input') == 'select') {
                $attributeOp = Mage::getModel('catalog/resource_eav_attribute')->load($attributeModel->getId());
                $attributeOptions = $attributeOp->getSource()->getAllOptions(false);
                $availableLabels = array();
                foreach ($attributeOptions AS $attributeOption) {
                    $availableLabels[] = $attributeOption['label'];
                }
                $attrInfo[] = implode(';', $availableLabels);
            } else {
                $attrInfo[] = '';
            }
            echo implode(',', $attrInfo);
            echo "\r\n";
        }
        exit;

    }


    public function exportProductCsvAction()
    {
        $avoidAttributes = array('created_at', 'updated_at', 'sku_type', 'price_type', 'weight_type', 'shipment_type', 'links_purchased_separately', 'links_title', 'price_view', 'url_key', 'url_path', 'creator_id', 'tax_class_id', 'visibility', 'status', 'admin_product_note', 'supplier_actived_product', 'frontendproduct_product_status',);
        header("Content-type: text/csv");
        header("Content-Disposition: attachment; filename=products.csv");
        header("Pragma: no-cache");
        header("Expires: 0");

        $attributeSetId = $this->getRequest()->getParam('attributeSetId');
        $productTable = Mage::getResourceModel('catalog/product')->getEntityTable();
        $supplierTable = Mage::getResourceModel('catalog/product')->getAttribute(
            'creator_id'
        );
        $collection = Mage::getModel('catalog/product')->getCollection();
        $collection->getSelect()->joinInner(
            array('sp' => $productTable.'_'.$supplierTable->getBackendType()),
            'sp.entity_id = e.entity_id AND sp.value = '.
            Mage::getSingleton('customer/session')->getCustomerId()
            .' AND sp.attribute_id = '.$supplierTable->getId(),
            array('sp.value')
        );
        $api = Mage::getModel('catalog/product_attribute_api');
        $attributes = $api->items($attributeSetId);
        $attributesCollection = array();
        $attributesCollection[] = 'product_id';
        $attributesCollection[] = '"seller_sku"';
        $attributeTable = array();
        foreach ($attributes as $_attribute) {
            if (in_array($_attribute['code'], $avoidAttributes)) continue;

            $attributeTable[$_attribute['code']] = Mage::getResourceModel('catalog/product')->getAttribute(
                $_attribute['code']
            );
            if ($attributeTable[$_attribute['code']]->getBackendType() != 'static' ) {
                $allias = $_attribute['code'].'_tb';
                $collection->getSelect()->joinLeft(
                    array($allias => $productTable.'_'. $attributeTable[$_attribute['code']]->getBackendType()),
                    $allias.'.entity_id = e.entity_id AND '.$allias.'.attribute_id = '. $attributeTable[$_attribute['code']]->getId(),
                    array($allias.'.value as '.$_attribute['code'] )
                );
            }


            if ($_attribute['required'] == 1) {
                $str = trim($_attribute['code']);
                $str .= ($_attribute['required'] == 1) ? ' (*)' : '';

                $attributesCollection[] = '"' . $str . '"';
            } else {
                try {
                    $model = Mage::getResourceModel('catalog/eav_attribute')
                        ->setEntityTypeId(Mage::getModel('eav/entity')->setType('catalog_product')->getTypeId())
                        ->load($_attribute['code'], 'attribute_code');

                    if ($model->getData('is_user_defined') && (strstr($model->getData('apply_to'), 'simple') || !$model->getData('apply_to'))) {
                        $str = trim($_attribute['code']);
                        $attributesCollection[] = '"' . $str . '"';
                    }
                } catch (Exception $e) {

                }
            }
        }
        $attributesCollection[] = '"seller_tax_type"';
        $attributesCollection[] = '"seller_tax_rate"';
        $attributesCollection[] = '"seller_tax_type_1"';
        $attributesCollection[] = '"seller_tax_rate_1"';
        $attributesCollection[] = '"category (*)"';
        $attributesCollection[] = '"qty (*)"';

        for ($i = 0; $i < Mage::getStoreConfig('supplierfrontendproductuploader_products/supplierfrontendproductuploader_catalog_config/images_count'); $i++) {
            $attributesCollection[] = '"image"';
        }

        $collection->joinField(
            'qty',
            'cataloginventory/stock_item',
            'qty',
            'product_id=entity_id',
            '{{table}}.stock_id=1',
            'left'
        )->joinTable('cataloginventory/stock_item', 'product_id=entity_id', array("stock_status" => "is_in_stock"));
        echo implode(',', $attributesCollection);
        echo "\r\n";
        $productValues = array();
        foreach ($collection as $product) {
            $productValues['product_id'] = $product->getId();
            foreach ($attributesCollection as $attributes) {
                if (str_replace('"', '', $this->_prepareHeader($attributes)) == 'product_id') {
                    $productValues[$product->getId()]['product_id'] = $product->getId();
                    continue;
                }
                if (str_replace('"', '', $this->_prepareHeader($attributes)) == 'image') {
                    continue;
                }
                if (str_replace('"', '', $this->_prepareHeader($attributes)) == 'category') {
                    $categories = $product->getCategoryCollection();
                    $catArray = array();
                    foreach ($categories as $category) {
                        $catArray[] = Mage::getModel('catalog/category')->load($category->getId())->getName();
                    }
                    $productValues[$product->getId()][str_replace('"', '', $this->_prepareHeader($attributes) )] =
                        implode(';', $catArray);
                    continue;
                }

                if ((isset($attributeTable[str_replace('"', '', $this->_prepareHeader($attributes) )])) && ($attributeTable[str_replace('"', '', $this->_prepareHeader($attributes) )]->getData('frontend_input')
                    == 'select')) {

                    $productRaw = Mage::getModel('catalog/product')->setStoreId(
                        Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID)
                    )->setData(str_replace('"', '', $this->_prepareHeader($attributes)), $product->getData(
                        str_replace('"', '', $this->_prepareHeader($attributes) )
                    ));
                    $value = $productRaw->getAttributeText(str_replace('"', '', $this->_prepareHeader($attributes) ));
                } else {
                    $value = $product->getData(
                        str_replace('"', '', $this->_prepareHeader($attributes) )
                    );
                }
                $value = str_replace(",", '', $value);
                $productValues[$product->getId()][str_replace('"', '', $this->_prepareHeader($attributes) )] =
                    $value?$value:'';
            }
            $mediaImages = $this->_getMediaImages($product->getId());
            for ($i = 0; $i < Mage::getStoreConfig('supplierfrontendproductuploader_products/supplierfrontendproductuploader_catalog_config/images_count'); $i++) {
                $productValues[$product->getId()]['image-'.$i] = isset($mediaImages[$i])?$mediaImages[$i]:'';
            }
            unset($mediaImages);
            echo implode(',', $productValues[$product->getId()]);
            echo "\r\n";
        }
    }

    private function _getMediaImages($productId = null) {
        if (is_null($productId)) {
            return false;
        }
        $_mediaGalleryAttributeId = Mage::getSingleton('eav/config')->getAttribute('catalog_product', 'media_gallery')->getAttributeId();
        $_read = Mage::getSingleton('core/resource')->getConnection('catalog_read');

        $_mediaGalleryData = $_read->fetchAll('
            SELECT `main`.`value` AS `file`
            FROM `catalog_product_entity_media_gallery` AS `main`
            WHERE (
                main.attribute_id = ' . $_read->quote($_mediaGalleryAttributeId) . ')
                AND (main.entity_id = '.$productId.' )
        ');

        $_mediaGalleryByProductId = array();

        foreach ($_mediaGalleryData as $_galleryImage) {
            $_mediaGalleryByProductId[] = basename($_galleryImage['file']);
        }
        return $_mediaGalleryByProductId;
    }
    private function _getOptionText($attributeCode = null, $optionValue = null) {
        if (is_null($attributeCode) || is_null($optionValue)) {
            return;
        }
        $productModel = Mage::getModel('catalog/product');
        $attr = $productModel->getResource()->getAttribute($attributeCode);
        if ($attr->usesSource()) {
          return $attr->getSource()->getOptionText($optionValue);
        }
    }
    private function _handleUpload()
    {
        if (isset($_FILES['file']['name']) && ($_FILES['file']['tmp_name'] != NULL)) {
            if (!$this->_validateSalt()) return false;

            $importResponse = array();
            $successCount = 0;
            $i = 0;
            $headers = array();
            if (($handle = fopen($_FILES['file']['tmp_name'], "r")) !== FALSE) {
                if (is_int(Mage::getStoreConfig('marketplace_configuration/csv_import/product_limit')) &&
                    Mage::getStoreConfig('marketplace_configuration/csv_import/product_limit') > 0 &&
                    count(file($_FILES['file']['tmp_name'])) > Mage::getStoreConfig('marketplace_configuration/csv_import/product_limit') + 1
                ) {
                    Mage::getSingleton('core/session')->addError(Mage::helper('marketplace')->__("Too many products added to import."));
                } else {
                    while (($data = fgetcsv($handle)) !== FALSE) {
                        if ($i != 0) {
                            $res = $this->_parseCsv($data, $headers);
                            if ($res['success']) {
                                $successCount++;
                            }
                            $res['line'] = $i;
                            $importResponse[] = $res;
                        } else {
                            $s = $this->validateHeaders($data);
                            if (count($s) > 0) {
                                Mage::getSingleton('core/session')->addError(Mage::helper('marketplace')->__("Attributes doesn't match all required attributes. Missing attribute : " . $s[0]));
                                break;
                            }
                            $headers = $data;
                        }
                        $i++;
                    }
                    fclose($handle);
                }
            }
            Mage::register('import_data', $importResponse);
            $customer = Mage::getModel('customer/customer')->load(Mage::helper('supplierfrontendproductuploader')->getSupplierId());

            $this->_getHelper('marketplace/email')->notifyAdminOnUploadingProducts($customer, $successCount);

            Mage::register('upload_done', true);
            $attributeSetId = $this->getRequest()->getParam('attributeSetId');
            Mage::register('attributeSetId', $attributeSetId);
        }
    }

    private function _parseCsv($line, $headers)
    {
        try {
            $this->_setMainPhoto = false;
            $productModel = $this->_findProduct($headers, $line);
            $isNew = false;
            if (!$productModel) {
                $isNew = true;
                $productModel = Mage::getModel("catalog/product");
                $productModel->setTypeId('simple');
                $productModel->setWebsiteIDs(array(Mage::app()->getStore()->getWebsiteId()));

                $attributeSetId = $this->getRequest()->getParam('attributeSetId');
                $productModel->setAttributeSetId($attributeSetId);
                $productModel->setStatus(Mage_Catalog_Model_Product_Status::STATUS_ENABLED);
                $productModel->setVisibility(Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE);
                $productModel->setTaxClassId(Mage::getStoreConfig('supplierfrontendproductuploader_products/supplierfrontendproductuploader_catalog_config/tax_class_id'));
                $productModel->setData('frontendproduct_product_status', Cminds_Supplierfrontendproductuploader_Model_Product::STATUS_PENDING);
                $productModel->setData('creator_id', Mage::helper('supplierfrontendproductuploader')->getSupplierId());

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
                        $productModel->setCategoryIds($categories);
                    }

                    $value = $this->_validateAttributeValue($attributeCode, $line[$i]);

                    if (strtolower($attributeCode) == 'qty') {
                        $productModel->setStockData(array(
                            'is_in_stock' => ($line[$i] > 0) ? 1 : 0,
                            'qty' => $line[$i]
                        ));
                    }

                    if (strtolower($attributeCode) == 'image') {

                        $key = $this->_findImageFileName($line[$i]);
                        $path = $this->_uploadImage($key);

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
                        } else {
                            $productModel->setData($attributeCode, $line[$i]);
                        }
                    }
                } else {
                    if ($this->_isRequired($attributeCode)) {
                        throw new Exception($this->__("Value for attribute : %s is not valid", $attributeCode));
                    }
                }
            }

            if (!$foundCategories) {
                throw new Exception($this->__('No categories found'));
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

            if (method_exists($e, 'getAttributeCode')) {
                return array('success' => false, 'message' => $e->getMessage(), 'attribute_code' => $e->getAttributeCode());
            } else {
                return array('success' => false, 'message' => $e->getMessage(), 'attribute_code' => 'unknown');
            }
        }
    }

    protected function _findProduct($headers, $line)
    {

        $foundIdValue = false;
        foreach ($headers AS $i => $header) {
            if (strtolower($header) == 'product_id') {
                $foundIdValue = $line[$i];
                break;
            }
        }

        if (!$foundIdValue || !is_numeric($foundIdValue)) return false;
        $product = Mage::getModel('catalog/product')->load($foundIdValue);

        if (!$product->getId()) throw new Exception($this->__("Product does not exists"));

        if ($product->getCreatorId() != Mage::helper('supplierfrontendproductuploader')->getSupplierId()) throw new Exception($this->__("Product does not exists"));

        return $product;
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

        if (!$isValid) {
            throw new Exception($this->__('No valid category'));
        }

        return $validCategoriesIds;
    }

    private function _prepareHeader($header)
    {
        return str_replace(' (*)', '', $header);
    }

    private function _isRequired($attribute_code)
    {
        $attributeModel = Mage::getSingleton("eav/config")->getAttribute('catalog_product', $attribute_code);
        return $attributeModel->getIsRequired();
    }

    private function _validateAttributeValue($attribute_code, $value)
    {
        $attributeModel = Mage::getSingleton("eav/config")->getAttribute('catalog_product', $attribute_code);

        if ($attributeModel->getIsRequired() && $value == '') {
            throw new Exception("Attribute " . $attribute_code . " is required");
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
                        throw new Exception("Value of attribute " . $attribute_code . " is not valid . Value : " . $value);
                    }
                }

                return $availableLabels[strtolower($value)];
            }
        }

        if ($attributeModel->getBackendType() == 'decimal') {
            if (!is_numeric($value)) {
                throw new Exception("Value of attribute " . $attribute_code . " is not valid. Should be numeric.");
            }
        }

        return false;
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

    private function downloadImage($url)
    {
        set_time_limit(0);
        $dir = $this->_getHelper('supplierfrontendproductuploader')->getImageCacheDir();
        $lfile = fopen($dir . '/' . basename($url), "w");

        $ch = curl_init($url);

        curl_setopt_array($ch, array(
            CURLOPT_URL => $url,
            CURLOPT_BINARYTRANSFER => 1,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_FILE => $lfile,
            CURLOPT_TIMEOUT => 50,
            CURLOPT_USERAGENT => 'Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)'
        ));

        $results = curl_exec($ch);
        if ($results) {
            return $dir . '/' . basename($url);
        }
        return false;
    }

    private function _uploadImage($key)
    {
        if (count($_FILES['files']['name']) == 0) return false;
        $file = array(
            'name' => $_FILES['files']['name'][$key],
            'type' => $_FILES['files']['type'][$key],
            'tmp_name' => $_FILES['files']['tmp_name'][$key],
            'error' => $_FILES['files']['error'][$key],
            'size' => $_FILES['files']['size'][$key]
        );

        $path = $this->_getHelper('supplierfrontendproductuploader')->getImageCacheDir(null);

        try {
            $uploader = new Varien_File_Uploader($file);
            $uploader->setAllowedExtensions(array('jpg', 'jpeg', 'gif', 'png'));
            $res = $uploader->save($path, strtolower($file['name']));
            $this->_usedImagesPaths[] = $path . DS . $res['file'];

            return $path . DS . $res['file'];
        } catch (Exception $e) {
            Mage::log($e, null, 'file-import-product.log', true);
            return false;
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

    private function _findImageFileName($name)
    {
        foreach ($_FILES['files']['name'] AS $key => $file) {
            if ($name == $file) {
                return $key;
            }
        }

        return false;
    }

    private function _validateSalt()
    {
        $salt = $this->getRequest()->getPost('salt');
        $sessionSalt = Mage::getSingleton('core/session')->getMarketplaceImportSalt();

        if ($salt != $sessionSalt) {
            Mage::getSingleton('core/session')->setMarketplaceImportSalt($salt);
            return true;
        }
        return false;
    }
}