<?php

class Cminds_Supplierfrontendproductuploader_ProductController extends Cminds_Supplierfrontendproductuploader_Controller_Action {
    public function preDispatch() {
        parent::preDispatch();
        $this->_getHelper()->validateModule();
        $hasAccess = $this->_getHelper()->hasAccess();

        if(!$hasAccess) {
            $this->getResponse()->setRedirect(Mage::helper('customer')->getLoginUrl());
        }
    }
    public function chooseTypeAction() {
        if($this->getRequest()->isPost()) {
            $postData = $this->getRequest()->getPost();
            
            if($postData['type'] == Mage_Catalog_Model_Product_Type::TYPE_SIMPLE) {
                $this->getResponse()->setRedirect(Mage::getUrl('supplierfrontendproductuploader/product/create', array('attribute_set_id' => $postData['attribute_set_id'], 'type' => $postData['type'])));
            } elseif($postData['type'] == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE) {
                $this->getResponse()->setRedirect(Mage::getUrl('marketplace/product/createConfigurable', array('attribute_set_id' => $postData['attribute_set_id'], 'type' => $postData['type'])));
            } elseif($postData['type'] == Mage_Catalog_Model_Product_Type::TYPE_VIRTUAL) {
                $this->getResponse()->setRedirect(Mage::getUrl('supplierfrontendproductuploader/product/create', array('attribute_set_id' => $postData['attribute_set_id'], 'type' => $postData['type'])));
            } elseif($postData['type'] == Mage_Downloadable_Model_Product_Type::TYPE_DOWNLOADABLE) {
                $this->getResponse()->setRedirect(Mage::getUrl('supplierfrontendproductuploader/product/create', array('attribute_set_id' => $postData['attribute_set_id'], 'type' => $postData['type'])));
            } else {
                Mage::getSingleton('core/session')->addError($this->__("Unsupported Product Type"));
                $this->getResponse()->setRedirect(Mage::getUrl('marketplace/product/chooseType'));
            }
            
        }
        $this->_renderBlocks(true);
       
    }
    public function createAction() {
        $params = $this->getRequest()->getParams();

        if(!isset($params['attribute_set_id'])) {
            $this->getResponse()->setRedirect(Mage::getUrl('marketplace/product/chooseType'));
            Mage::getSingleton('core/session')->addError($this->__("Missing Attribute Set ID"));
            return;
        }

        
        $this->_renderBlocks(true, true);
    }

    public function editAction() {
        $id = $this->_request->getParam('id', null);

        if($id == null) {
            throw new Exception('No product id');
        }

        $p = Mage::getModel('catalog/product')->load($id);

        if($p->getData('creator_id') != $this->_getHelper()->getSupplierId()) {
            throw new Exception('No product');
        }

        Mage::register('supplier_product_id', $id);


        $this->_renderBlocks(true);
    }

    public function listAction() {
        $this->_renderBlocks();
    }
    public function orderedAction() {
        $this->_renderBlocks(true);
    }
    
    public function viewAction() {
        $this->_renderBlocks();
    }
    public function cloneAction() {
        $id = $this->_request->getParam('id', null);

        if($id == null) {
            throw new Exception('No product id');
        }

        $p = Mage::getModel('catalog/product')->load($id);

        if($p->getData('creator_id') != $this->_getHelper()->getSupplierId()) {
            throw new Exception('No product');
        }

        Mage::register('supplier_product_id', $id);


        $this->_renderBlocks(true);
    
    }
    

    public function saveAction() {
        if($this->_request->isPost()) {
            $postData = $this->_request->getPost();
            $editMode = false;
            $currentStoreId = Mage::app()->getStore()->getId();
            try {
                if(isset($postData['product_id']) && $postData['product_id'] != NULL) {
                    $product = Mage::getModel('catalog/product')->load($postData['product_id']);
                    
                    if(!$product->getId()) {
                        throw new Exception('Product does not exists');
                    }
                    
                    if($product->getData('creator_id') != $this->_getHelper()->getSupplierId()) {
                        throw new Exception('Product does not belongs to this supplier');
                    }
                    $editMode = true;
                } else {
                    $product = Mage::getModel('catalog/product');
                }

                $productValidator = Mage::getModel('supplierfrontendproductuploader/product');
                $productValidator->setData($postData);
                $productValidator->validate();

                $product->setName($postData['name']);
                $product->setDescription($postData['description']);
                $product->setShortDescription($postData['short_description']);
                
                if($postData['special_price'] != '' && number_format($postData['special_price']) != 0) {
                    $product->setSpecialPrice($postData['special_price']);
                    
                    if($postData['special_price_from_date'] != NULL && $postData['special_price_from_date'] != '') {
                        $product->setSpecialFromDate($postData['special_price_from_date']);
                        $product->setSpecialFromDateIsFormated(true);
                    }
                    if($postData['special_price_to_date'] != NULL && $postData['special_price_to_date'] != '') { 
                        $product->setSpecialToDate($postData['special_price_to_date']);
                        $product->setSpecialToDateIsFormated(true);
                    }
                }
                
                if(!$editMode) {
                    if(!isset($postData['sku']) || $postData['sku'] == NULL) {
                        $product->setSku($this->_getSupplierHelper()->generateSku());
                    } else {
                        $cProduct = Mage::getModel('catalog/product')->loadByAttribute('sku', $postData['sku']);

                        if($cProduct) {
                            throw new Exception('Product with this SKU already exists in catalog');
                        }

                        $product->setSku($postData['sku']);
                    }
                    if(!isset($postData['attribute_set_id']) || empty($postData['attribute_set_id'])) {
                        throw new Exception('Missing Attribute Set ID');
                    }

                    $typeId = $this->getRequest()->getParams();
                   
                    if ($typeId['type'] == 'simple'){
                        $product->setTypeId(Mage_Catalog_Model_Product_Type::TYPE_SIMPLE);
                    } elseif ($typeId['type'] == 'configurable') {
                        $product->setTypeId(Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE);
                    } elseif ($typeId['type'] == 'virtual') {
                        $product->setTypeId(Mage_Catalog_Model_Product_Type::TYPE_VIRTUAL);
                    } elseif ($typeId['type'] == 'downloadable') {
                        $product->setTypeId(Mage_Downloadable_Model_Product_Type::TYPE_DOWNLOADABLE);
                    } 
                    $product->setAttributeSetId($postData['attribute_set_id']);
                    $product->setStatus(Mage_Catalog_Model_Product_Status::STATUS_ENABLED);
                    $product->setVisibility(Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE);
                    $product->setTaxClassId(Mage::getStoreConfig('supplierfrontendproductuploader_products/supplierfrontendproductuploader_catalog_config/tax_class_id'));
                    $product->setData('admin_product_note', null);
                }

                if(isset($postData['weight'])) {
                    $product->setWeight($postData['weight']);
                }
                $product->setPrice($postData['price']);

                if(isset($postData['qty'])) {
                    $product->setStockData(array(
                        'is_in_stock' => ($postData['qty'] > 0) ? 1 : 0,
                        'qty' => $postData['qty']
                    ));
                }

                $product->setCategoryIds($postData['category']);
                $product->setWebsiteIDs(array(Mage::app()->getStore()->getWebsiteId()));
                $product->setCreatedAt(strtotime('now'));

                if(isset($postData['attributes'])) {
                    foreach($postData['attributes'] as $attrCode){

                        $super_attribute= Mage::getModel('eav/entity_attribute')->loadByCode('catalog_product',$attrCode);
                        $configurableAtt = Mage::getModel('catalog/product_type_configurable_attribute')->setProductAttribute($super_attribute);

                        $newAttributes[] = array(
                            'id'             => $configurableAtt->getId(),
                            'label'          => $configurableAtt->getLabel(),
                            'position'       => $super_attribute->getPosition(),
                            'values'         => $configurableAtt->getPrices() ? $product->getPrices() : array(),
                            'attribute_id'   => $super_attribute->getId(),
                            'attribute_code' => $super_attribute->getAttributeCode(),
                            'frontend_label' => $super_attribute->getFrontend()->getLabel(),
                        );
                    }
                }

                Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
                

                $ret = $this->uploadFile();
                if($ret != NULL) {
                    $product->setLinksTitle('Test Product');
                    $product->setLinksPurchasedSeparately('0');
                    $downloadData = array();


                    $downloadData['link'][0] = array(
                        'link_id' => '',
                        'title' => $ret,
                        'price' => $product->getPrice(),
                        'number_of_downloads' => $product->getNumberOfDownloads(),
                        'is_shareable' => $product->getLinkId(),
                        'file' => $product->getLinkFile(),
                        'type' => 'file',
                        'link_url' => Mage::getBaseUrl('media') . $ret,
                        'sort_order' => $product->getSortOrder(),
                        'is_delete' => 0
                    );

                    $product->setDownloadableData($downloadData);
                } elseif($postData['file_url']) {
                    if(!$editMode) {
                        $downloadData = array();

                        $downloadData['link'][0] = array(
                            'link_id' => '',
                            'title' => $postData['file_url'],
                            'price' => $product->getPrice(),
                            'number_of_downloads' => $product->getNumberOfDownloads(),
                            'is_shareable' => $product->getLinkId(),
                            'file' => $product->getLinkFile(),
                            'type' => 'url',
                            'link_url' => $postData['file_url'],
                            'sort_order' => $product->getSortOrder(),
                            'is_delete' => 0
                        );

                        $product->setDownloadableData($downloadData);
                    } else {
                        $linkPurchasedItems = Mage::getModel('downloadable/link')->getCollection()
                            ->addFieldToFilter('product_id', $product->getId())->load();
                        $currentPurchasedItemsT = $linkPurchasedItems->getItems();

                        foreach( $currentPurchasedItemsT as $c){
                            $c->setLinkUrl($postData['file_url']);
                            $c->save();
                        }
                    }
                }

                $product->save();

                unset($postData['name'], $postData['description'], $postData['short_description'], $postData['sku'], $postData['weight'], $postData['price'], $postData['qty'], $postData['category']);

                $product = Mage::getModel('catalog/product')->load($product->getId());

                if(!isset($postData['image'])) {
                    $postData['image'] = array();
                }
                
                $existingImages = array();
                
                if($product->getId() && $editMode) {
                    $mediaApi = Mage::getModel("catalog/product_attribute_media_api");
                    $mediaGalleryAttribute = Mage::getModel('catalog/resource_eav_attribute')->loadByCode($product->getEntityTypeId(), 'media_gallery');
                    $gallery = $product->getMediaGalleryImages();
                    
                    foreach ($gallery as $image) {
                        if(!in_array($image->getFile(), $postData['image'])) {
                            $mediaApi->remove($product->getId(), $image->getFile());
                            $mediaGalleryAttribute->getBackend()->removeImage($product, $image->getFile());

                        } else {
                            $existingImages[] = $image->getFile();
                            
                            if($postData['main_photo'] == $image->getFile()) {
                                Mage::getSingleton('catalog/product_action')->updateAttributes(array($product->getId()), array('image'=>$image->getFile()), 0);
                            }
                        }
                    }
                }

                $onlyOneImage = false;

                if(count($postData['image']) == 1) {
                    $onlyOneImage = true;
                }

                foreach($postData['image'] AS $image) {
                    if($image != '' && $image && $image != NULL && !in_array($image, $existingImages)) {
                        $attrs = null;

                        if($image == $postData['main_photo'] || $onlyOneImage) {
                            $attrs = array('image','small_image','thumbnail');
                        }

                        if(isset($postData['is_cloned'])) {
                            $product->addImageToMediaGallery($this->_getSupplierHelper()->getImageDir($postData) . $image, $attrs, true, false);
                        } else {
                            $product->addImageToMediaGallery($this->_getSupplierHelper()->getImageCacheDir($postData) . $image, $attrs, true, false);
                        }
                    }

                }

                $ommitIndex = array('submit', 'main_photo', 'image', 'product_id', 'special_price', 'special_price_to_date', 'special_price_from_date', 'notify_admin_about_change');

                foreach($postData AS $index => $value) {
                    if(!in_array($index, $ommitIndex) && $value != '') {
                        $product->setData($index, $value);
                    }
                }

                if($editMode) {
                    $product->setSmallImage($postData['main_photo']);
                    $product->setImage($postData['main_photo']);
                    $product->setThumbnail($postData['main_photo']);
                } else {
                    $product->setData('frontendproduct_product_status', Cminds_Supplierfrontendproductuploader_Model_Product::STATUS_PENDING);
                    $product->setData('creator_id', $this->_getHelper()->getSupplierId());
                }

                $product->save();

                if(!$editMode) {
                    Mage::log($this->_getHelper()->__('Supplier '. $this->_getHelper()->getSupplierId() .' created product : ' . $product->getId()));
                    $this->_getHelper('supplierfrontendproductuploader/email')->notifyOnSupplierAddNew($product);
                } else {
                    if(isset($postData['notify_admin_about_change']) && $postData['notify_admin_about_change'] == 1) {
                        $this->_getHelper('supplierfrontendproductuploader/email')->notifyAdminOnProductChange($product);
                    }
                }

                Mage::app()->getFrontController()->getResponse()->setRedirect(Mage::getUrl('supplierfrontendproductuploader/product/list'));
            } catch (Exception $ex) {
                Mage::getSingleton('core/session')->addError($ex->getMessage());
                Mage::log($ex->getMessage());
                Mage::getSingleton("supplierfrontendproductuploader/session")->setProductData($postData);

                if($editMode) {
                    Mage::app()->getFrontController()->getResponse()->setRedirect(Mage::getUrl('supplierfrontendproductuploader/product/edit/', array('id' =>  $postData['product_id'], 'type' => $postData['type'])));
                } else {
                    Mage::app()->getFrontController()->getResponse()->setRedirect(Mage::getUrl('supplierfrontendproductuploader/product/create/', array('attribute_set_id' =>  $postData['attribute_set_id'], 'type' => $postData['type'])));
                }
            }
        }
    }
    
    public function uploadAction() {
        if(isset($_FILES['file_upload']['name']) && ($_FILES['file_upload']['tmp_name'] != NULL))
        {
            $uploader = new Varien_File_Uploader('file_upload');
            $uploader->setAllowedExtensions(array('jpg','jpeg','gif','png'));
            $uploader->setAllowRenameFiles(true);
            $uploader->setFilesDispersion(true);

            $path = $this->_getHelper()->getImageCacheDir(null);

            try {
                $uploader->save($path, $_FILES['file_upload']['name']);

                $image = new Varien_Image($path . $uploader->getUploadedFileName());
                $image->resize(171);
                $image->save($path . DS . 'resized/' . $uploader->getUploadedFileName() );

                $imageUrl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . 'upload/resized' . $uploader->getUploadedFileName();

                $ret = array('success' => true, 'url' => $imageUrl, 'name' => $uploader->getUploadedFileName());
            } catch(Exception $e) {
                
                $ret = array('success' => false, 'message' => $e->getMessage());
                print json_encode($ret);
                exit;
            }
        }

        print json_encode($ret);
        exit;
    }
    public function uploadFile() {
        if(isset($_FILES['downloadable_upload']['name']) && ($_FILES['downloadable_upload']['tmp_name'] != NULL))
        {
            
            try {
                $uploader = new Varien_File_Uploader('downloadable_upload');
                $uploader->setAllowedExtensions($this->_getHelper()->getAvailableExtensions());
                $uploader->setAllowRenameFiles(true);
                $uploader->setFilesDispersion(false);

                $path = Mage::getBaseDir('media'); 
                $uploader->save($path , $_FILES['downloadable_upload']['name']);
                $file = $_FILES['downloadable_upload']['name'];
                $fileUrl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . $uploader->getUploadedFileName() ;
                $ret = array('success' => true, 'url' => $fileUrl, 'name' => $uploader->getUploadedFileName());
                return $ret['name'];
            } catch(Exception $e) {
                Mage::throwException($e->getMessage());
            }
        }
    }

    public function activeAction() {
        $id = $this->_request->getParam('id', null);

        if($id == null) {
            throw new Exception('No product id');
        }

        $p = Mage::getModel('catalog/product')->load($id);

        if($p->getData('creator_id') != $this->_getHelper()->getSupplierId()) {
            throw new Exception('No product');
        }
        
        if(!in_array($p->getData('frontendproduct_product_status'), array(Cminds_Supplierfrontendproductuploader_Model_Product::STATUS_PENDING, Cminds_Supplierfrontendproductuploader_Model_Product::STATUS_DISAPPROVED))) {
            $p->setSupplierActivedProduct(1);   
            $p->getResource()->saveAttribute($p, 'supplier_actived_product'); 
            $p->setVisibility(Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH);
            $p->setFrontendproductProductStatus(Cminds_Supplierfrontendproductuploader_Model_Product::STATUS_APPROVED);
            $p->getResource()->saveAttribute($p, 'frontendproduct_product_status');

            $p->setStockData(array( 
                'is_in_stock' => 1
            ));
            
            $p->save();
        }
        
        Mage::dispatchEvent('supplierfrontendproductuploader_catalog_product_supplier_active', array('product' => $p));
        $this->_redirect(Mage::getUrl("supplierfrontendproductuploader/product/list/"));
    } 

    public function deactiveAction() {
        $id = $this->_request->getParam('id', null);

        if($id == null) {
            throw new Exception('No product id');
        }

        $p = Mage::getModel('catalog/product')->load($id);

        if($p->getData('creator_id') != $this->_getHelper()->getSupplierId()) {
            throw new Exception('No product');
        }

        if(!in_array($p->getData('frontendproduct_product_status'), array(Cminds_Supplierfrontendproductuploader_Model_Product::STATUS_PENDING, Cminds_Supplierfrontendproductuploader_Model_Product::STATUS_DISAPPROVED))) {
            $p->setSupplierActivedProduct(0); 
            $p->setVisibility(Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE);
            $p->getResource()->saveAttribute($p, 'supplier_actived_product'); 

            $p->setFrontendproductProductStatus(Cminds_Supplierfrontendproductuploader_Model_Product::STATUS_NONACTIVE);
            $p->getResource()->saveAttribute($p, 'frontendproduct_product_status');            

            $p->setStockData(array( 
                'is_in_stock' => 0
            )); 

            $p->save();
        }
        
        Mage::dispatchEvent('supplierfrontendproductuploader_catalog_product_supplier_deactive', array('product' => $p));
        Mage::app()->getFrontController()->getResponse()->setRedirect(Mage::getBaseUrl().'supplierfrontendproductuploader/product/list/');
    }
}
