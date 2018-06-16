<?php 
class Velanapps_Ecfplus_Adminhtml_MultiformController extends Mage_Adminhtml_Controller_Action
{
	protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('ecfplus/items')
            ->_addBreadcrumb(Mage::helper('adminhtml')->__('Multiform Information'), Mage::helper('adminhtml')->__('Multiform'));
        return $this;
    }   
   
    public function indexAction() 
	{		
	  
	   $this->_initAction();
	   $this->_addContent($this->getLayout()->createBlock('ecfplus/adminhtml_multiform'));
       $this->renderLayout();
    }
	
	public function editAction()
    {
        $id = $this->getRequest()->getParam('id');
		$model = Mage::getModel('ecfplus/multiform')->load($id);
		
 
        if ($model->getId() || $id == 0)		
		{	
			
			Mage::register('multiform_data', $model);			
			/* $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
			if ($data)
			{
				$model->setData($data)->setId($id);
			} */
			$this->loadLayout()->_setActiveMenu('ecfplus/items');
			$this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
			$this->_addContent($this->getLayout()->createBlock('ecfplus/adminhtml_multiform_edit'))->_addLeft($this->getLayout()->createBlock('ecfplus/adminhtml_multiform_edit_tabs'));
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
		if ($data = $this->getRequest()->getPost())
        {			
			$model = Mage::getModel('ecfplus/multiform');
			$request = $this->getRequest();    			
			$id = $this->getRequest()->getParam('id');
			$urlSet = $id;
			$model->load($id);
			$model->setData($data);
            Mage::getSingleton('adminhtml/session')->setFormData($data);
            try 
			{
                if($id) 
				{
                    $model->setId($id);
                }
                $model->save(); 
				/*-*/
				/* $modelEmail = Mage::getModel('ecfplus/multiform')->getCollection()->addFieldToSelect('enable_email')->addFieldToFilter('id', array('eq' => $model->getId()))->addFieldToFilter('enable_email', array('eq' => 1))->getData();		
		
				if( !empty($modelEmail) )
				{
					$emailData = Mage::getModel('ecfplus/items')->getCollection()->addFieldToSelect('title')->addFieldToFilter('form_id', array('eq' => $id))->addFieldToFilter('is_mail', array('eq' => 1))->getData();
					
					if( empty($emailData) )
					{
						
						try
						{	
							$ecfplusItems = Mage::getModel('ecfplus/items')
													->setTitle('Email')
													->setGroup('text')
													->setType('field')
													->setIsRequire(1)
													->setFormId($id)
													->setIsMail(1);
							$itemId = $ecfplusItems->save()->getId();
						}
						catch (Exception $e)
						{
							Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
						}			
						
						try
						{
							$ecfplusItemoptions = Mage::getModel('ecfplus/itemoptions')->setItemId($itemId)->setFormId($id)->setValidation('validate-email');
							$ecfplusItemoptions->save();
						}
						catch (Exception $e)
						{
							Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
						}				
					}
				}
				
				if( empty($modelEmail) )
				{
					$emailData = Mage::getModel('ecfplus/items')->getCollection()->addFieldToSelect('item_id')->addFieldToFilter('form_id', array('eq' => $id))->addFieldToFilter('is_mail', array('eq' => 1))->getData();
					
					
					if( !empty($emailData) )
					{		
						
						try
						{
							foreach ($emailData as $ids)
							{
								$em = Mage::getModel('ecfplus/items')->load($ids);
								$em->delete();
							}
						}
						catch (Exception $e)
						{
							Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
						}	
					}
					
					
				} */
		/*-*/		
				
                if (!$model->getId()) 
				{
                    Mage::throwException(Mage::helper('adminhtml')->__('Error saving forms'));
                } 
              
                 
                if ($this->getRequest()->getParam('back'))
				{
                    $this->_redirect('*/*/edit', array('id' => $model->getId()));
                } 
				else 
				{
                    $this->_redirect('*/*/');
                }
				
				
				if($urlSet!='')
				{
					  Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Form successfully saved.'));
					$this->_redirect('*/adminhtml_multiform/');
				}
				else
				{
					Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Form successfully saved. please add the contact fields'));
					$this->_redirect('ecfplus/adminhtml_multiform/add/id/'.$model->getId().'');
				}
				
				
 
            } 
			catch (Exception $e)
			{
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                if ($model && $model->getId()) 
				{
                    $this->_redirect('*/*/edit', array('id' => $model->getId()));
                } 
				else
				{
                    $this->_redirect('*/*/');
                }
				$this->_redirect('*/*/');
            }			
        }    
       		
	}
	
	public function deleteAction()
    {
        if( $this->getRequest()->getParam('id') > 0 ) 
		{
            try 
			{
				$formid = $this->getRequest()->getParam('id');
                $multiformModel = Mage::getModel('ecfplus/multiform'); 
				Velanapps_Ecfplus_Model_Truncateitem::deleteItems($formid);	
                $multiformModel->setId($formid)->delete();
                   
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
   
    public function newAction()
    {
        $this->_forward('edit');
    }
	
	public function gridAction()
    {
        $this->loadLayout();
        $this->getResponse()->setBody($this->getLayout()->createBlock('ecfplus/adminhtml_multiform_grid')->toHtml());
    }
	
	public function addAction() 
	{
		$id = $this->getRequest()->getParam('id');		
		$model = Mage::getModel('ecfplus/multiform')->getCollection()->addFieldToSelect('enable_email')->addFieldToFilter('id', array('eq' => $id))->addFieldToFilter('enable_email', array('eq' => 1))->getData();		
		
		if( !empty($model) )
		{
			$emailData = Mage::getModel('ecfplus/items')->getCollection()->addFieldToSelect('title')->addFieldToFilter('form_id', array('eq' => $id))->addFieldToFilter('is_mail', array('eq' => 1))->getData();
			
			if( empty($emailData) )
			{
				
				try
				{	
					$ecfplusItems = Mage::getModel('ecfplus/items')
											->setTitle('Email')
											->setGroup('text')
											->setType('field')
											->setIsRequire(1)
											->setFormId($id)
											->setIsMail(1);
					$itemId = $ecfplusItems->save()->getId();
				}
				catch (Exception $e)
				{
					Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
				}			
				
				try
				{
					$ecfplusItemoptions = Mage::getModel('ecfplus/itemoptions')->setItemId($itemId)->setFormId($id)->setValidation('validate-email input-text');
					$ecfplusItemoptions->save();
				}
				catch (Exception $e)
				{
					Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
				}				
			}
		}
		
		if( empty($model) )
		{
			$emailData = Mage::getModel('ecfplus/items')->getCollection()->addFieldToSelect('item_id')->addFieldToFilter('form_id', array('eq' => $id))->addFieldToFilter('is_mail', array('eq' => 1))->getData();
			
			
			if( !empty($emailData) )
			{		
				
				try
				{
					foreach ($emailData as $ids)
					{
						$em = Mage::getModel('ecfplus/items')->load($ids);
						$em->delete();
					}
				}
				catch (Exception $e)
				{
					Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
				}	
			}
			
			
		}
        $this->_initAction();  
		$this->_addContent($this->getLayout()->createBlock('ecfplus/adminhtml_multiform_options'));
        $this->renderLayout();
    }
	
	public function postDataAction()
    {
		$postedData = $this->getRequest()->getPost();
		$formid = $postedData['formid'];		
		$ecfplus = $postedData['ecfplus'];
		$ecfplusValues = $postedData['easycontectvalues'];
		
		//Truncate the table
		Velanapps_Ecfplus_Model_Truncateitem::deleteItems($formid);
		$i = 1; 	
		$j = 0;	
		end($ecfplus);
		$i = key($ecfplus);
 
			 

		foreach($ecfplus as $key => $ezField)
		{

			if($ecfplus[$i]['is_delete'] != 1)
			{	
				
				$group 		= $ecfplus[$i]['previous_group'];
				$postId 	= $ecfplus[$i]['id'];
				$title 		= $ecfplus[$i]['title'];
				$type 		= $ecfplus[$i]['type'];				
				$isRequire 	= $ecfplus[$i]['is_require'];								
				$isMail 	= $ecfplus[$i]['is_mail'];				 
				$sortOrder 	= $ecfplus[$i]['sort_order'];
				
				$itemData = array(
							'group'   	 => $group,
							'post_id' 	 => $postId,
							'title'   	 => $title,
							'type'	  	 => $type,
							'is_require' => $isRequire,
							'sort_order' => $sortOrder
						);	
				
				$modelItem = Mage::getModel('ecfplus/items')->setData($itemData);
				$modelItem->setFormId($formid);
				$modelItem->setIsMail($isMail);
				$itemId = $modelItem->save()->getId(); 
				
				$optValidation 		= ($ecfplusValues[$i]['validation'] != "") ? $ecfplusValues[$i]['validation'] : '';
				$optMaxCharacters 	= ($ecfplusValues[$i]['max_characters'] != "") ? $ecfplusValues[$i]['max_characters'] : '' ;

				if(is_array($ecfplusValues[$i]['values']))
				{
					foreach($ecfplusValues[$i]['values'] as $ezOption)
					{
						if($ecfplusValues[$i]['values'][$j]['is_delete'] != 1) 
						{
							$optTitle 	  = $ezOption['title'];
							$optSortOrder = $ezOption['sort_order'];
							$itemOption = array(
											'item_id'    	 => $itemId,
											'title'   	  	 => $optTitle,
											'sort_order' 	 => $optSortOrder);
							try 
							{
								$modelOption = Mage::getModel('ecfplus/itemoptions')->setData($itemOption);
								$modelOption->setFormId($formid);
								$optionId = $modelOption->save()->getId(); 
							}
							catch (Exception $e){
								Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
							}
						}
						$j++;
					}
				}
				else
				{
					$itemOption = array(
									'item_id'    => $itemId,									
									'validation'	 => $optValidation,
									'max_characters' => $optMaxCharacters);
					try 
					{						
						$modelOption = Mage::getModel('ecfplus/itemoptions')->setData($itemOption);
						$modelOption->setFormId($formid);
						$optionId = $modelOption->save()->getId();
					}
					catch (Exception $e)
					{
						Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
					}
				}
			}
			$i++;		
		}
		Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Contact Form successfully Saved'));
		$this->_redirect('*/adminhtml_multiform/');
	
    }
	
}	