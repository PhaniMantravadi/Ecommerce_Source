<?php

class Velanapps_Ecfplus_Adminhtml_StorelocatorController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $this->loadLayout();
		$this->_addContent($this->getLayout()->createBlock('ecfplus/adminhtml_storelocator'));		
		$this->renderLayout();
    }
	public function newAction()
    {
        $this->loadLayout()->_setActiveMenu('ecfplus/items');
		$this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
		$this->_addContent($this->getLayout()->createBlock('ecfplus/adminhtml_storelocator_edit'))->_addLeft($this->getLayout()->createBlock('ecfplus/adminhtml_storelocator_edit_tabs'));
		$this->renderLayout();
    }
    
    public function postAction()
    {
        $post = $this->getRequest()->getPost();
		$location = serialize($post['option']);
		
		$storelocator = Mage::getModel('ecfplus/storelocator')
							->setMapName($post['mapname'])
							->setStatus($post['status'])
							->setLocation($location)
							->save();
		
        try {
            if (empty($post)) {
                Mage::throwException($this->__('Invalid form data.'));
            }
            
            /* here's your form processing */
            
            $message = $this->__('Your Store Location has been saved successfully.');
            Mage::getSingleton('adminhtml/session')->addSuccess($message);
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }
        $this->_redirect('*/*');
    }
	
	public function editAction()
    {
        $id = $this->getRequest()->getParam('id');
		$model = Mage::getModel('ecfplus/storelocator')->load($id);
 
        if ($model->getId() || $id == 0)		
		{	
			
			Mage::register('storelocator_data', $model);			
			/* $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
			if ($data)
			{
				$model->setData($data)->setId($id);
			} */
			$this->loadLayout()->_setActiveMenu('ecfplus/items');
			$this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
			$this->_addContent($this->getLayout()->createBlock('ecfplus/adminhtml_storelocator_edit'))->_addLeft($this->getLayout()->createBlock('ecfplus/adminhtml_storelocator_edit_tabs'));
			$this->renderLayout();			
		}
		else 
		{
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Does not exist'));
			$this->_redirect('*/*/');
		}
	}
	
	
	public function saveAction()
    {
        if ( $this->getRequest()->getPost() ) 
        {
            try {
                $postData = $this->getRequest()->getPost();
				/* print_r($postData);
				exit; */ 
				// Save the data
                $locations = $postData['location'];
                unset($postData['locations']);
		
                if($postData['parent_id'])
				{
					$model = Mage::getModel('ecfplus/storelocator')->load($postData['parent_id']);
					$backgroundImage = $this->getRequest()->getParam('background_image');
					if(isset($backgroundImage["delete"])){
						$postData['background_image'] =  "";
					}else{
						if(isset($_FILES['background_image']['name'])){
							$backgroundImageUrl = $this->uploadMarkerImage('background_image');
							if($backgroundImageUrl != ""){
								$postData['background_image'] = $backgroundImageUrl;
							}	
							else{
								$postData['background_image'] = $model->getBackgroundImage();
							}
						}
					}
					
					$model->addData(array(
						'map_name' => $postData['map_name'],
                        'status' => $postData['status'],
                        'background_image' => $postData['background_image'],
                        ));
				}
				else
				{
					$model = Mage::getModel('ecfplus/storelocator');
					$backgroundImage = $this->getRequest()->getParam('background_image');
					if(isset($backgroundImage["delete"])){
						$postData['background_image'] =  "";
					}else{
						if(isset($_FILES['background_image']['name'])){
							$backgroundImageUrl = $this->uploadMarkerImage('background_image');
							if($backgroundImageUrl != ""){
								$postData['background_image'] = $backgroundImageUrl;
							}	
							else{
								$postData['background_image'] = $model->getBackgroundImage();
							}
						}
					}
					$model->setData(array(
									'map_name' => $postData['map_name'],
                                    'status' => $postData['status'],
									'background_image' => $postData['background_image'],
                                  ));
				}
				$model->save();


                $parentId = $model->getId();
			
                // save locations
                if (!empty($locations))
                {
                    foreach ($locations['delete'] as $_key => $_row)
                    {
                        $delete = (int)$_row;
						
                        $locationsData = $locations['value'][$_key];
					    if($locationsData['id'])
						{
							$_locations = Mage::getModel('ecfplus/storelocator_locations')->load((int)$locationsData['id']); // this is the model that stores the URLs data in a second table
							$_locations->addData(array(
									'parent_id' => $parentId,
                                    'latitude' => $locationsData['latitude'],
                                    'longitude'   => $locationsData['longitude'],
                                    'address'   => $locationsData['address'],
                                    'sortorder' => $locationsData['sortorder'],
                                ))->save();
							if ($delete && 0 < (int)$_locations->getId()) // exists & required to delete
							{
								$_locations->delete();
								continue;
							}
						}
						else
						{
							if($delete)
							{
							}
							else
							{
								Mage::getModel('ecfplus/storelocator_locations')->setData(array(
										'parent_id' => $parentId,
										'latitude' => $locationsData['latitude'],
										'longitude'   => $locationsData['longitude'],
										'address'   => $locationsData['address'],
										'sortorder' => $locationsData['sortorder'],
									))->save();
							}
						}
                    }
                }

                // And wrap up the transaction
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Item was successfully saved'));

                $this->_redirect('*/*/');
                return;
            } catch (Exception $e) {

            }
        }
        $this->_redirect('*/*/');
    }
	
	
	
	
	
	public function deleteAction()
    {
        if( $this->getRequest()->getParam('id') > 0 ) 
		{
            try 
			{
				$mapid = $this->getRequest()->getParam('id');
                $storelocatorModel = Mage::getModel('ecfplus/storelocator'); 
                $storelocatorModel->setId($mapid)->delete();
                   
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Item was successfully deleted'));
                $this->_redirect('*/*/');
            } 
			catch (Exception $e)
			{
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            }
        }
        $this->_redirect('*/*/');
    }
	
	public function uploadMarkerImage($file){
		$imageUrl = "";
		
		try{
			if(isset($_FILES[$file]['name']) and (file_exists($_FILES[$file]['tmp_name']))) {
			
				$fileName = $_FILES[$file]['name'];
				$fileName = str_replace(" ", "-", $fileName);
				
				$path = Mage::getBaseDir('media') . DS . "velanapps";
				
				$uploader = new Varien_File_Uploader($file);
				
				$uploader->setAllowRenameFiles(false);
				$uploader->setFilesDispersion(false);
				$uploader->save($path, $fileName);
				
				$imageUrl = Mage::helper('ecfplus')->getImageUrl($fileName);
				
			}
			
		}catch(Exception $error){
			$this->helper->error($error->getMessage());
		}
		
		return $imageUrl;
		
	}
	
}