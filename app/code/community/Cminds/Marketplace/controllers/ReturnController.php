<?php

class Cminds_Marketplace_ReturnController extends Cminds_Marketplace_Controller_Action {

    public function indexAction()
    {
        $this->_renderBlocks(false, true);
    }

    public function viewAction()
    {
        $this->_renderBlocks(false, true);
    }
    /**
     * Add RMA comment action
     */
    public function addCommentAction()
    {
        if ($this->_loadValidRma()) {
            try {
                $response   = false;
                $comment    = $this->getRequest()->getPost('comment');
                $comment    = trim(strip_tags($comment));

                if (!empty($comment)) {
                    $supplierId = Mage::getSingleton('customer/session')->getId();
                    $result = Mage::getModel('sz_rma/rma_status_history')
                        ->setRmaEntityId(Mage::registry('current_rma')->getEntityId())
                        ->setComment($comment)
                        ->setSupplier($supplierId)
                        ->setIsVisibleOnFront(true)
                        ->setStatus(Mage::registry('current_rma')->getStatus())
                        ->setCreatedAt(Mage::getSingleton('core/date')->gmtDate())
                        ->save();
                    $result->setStoreId(Mage::registry('current_rma')->getStoreId());
                    $result->sendCustomerCommentEmail();
                } else {
                    Mage::throwException(Mage::helper('sz_rma')->__('Enter valid message.'));
                }
            } catch (Mage_Core_Exception $e) {
                $response = array(
                    'error'     => true,
                    'message'   => $e->getMessage(),
                );
            } catch (Exception $e) {
                $response = array(
                    'error'     => true,
                    'message'   => Mage::helper('sz_rma')->__('Cannot add message.')
                );
            }
            if (is_array($response)) {
                Mage::getSingleton('core/session')->addError($response['message']);
            }
            $this->_redirect('*/*/view', array('entity_id' => (int)$this->getRequest()->getParam('entity_id')));
            return;
        }
        return;
    }

    /**
     * Try to load valid rma by entity_id and register it
     *
     * @param int $entityId
     * @return bool
     */
    protected function _loadValidRma($entityId = null)
    {
        if (null === $entityId) {
            $entityId = (int) $this->getRequest()->getParam('entity_id');
        }
        $rma = Mage::getModel('sz_rma/rma')->load($entityId);
        Mage::register('current_rma', $rma);
        return true;

    }
}
