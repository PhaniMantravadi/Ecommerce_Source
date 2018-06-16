<?php
/**
 * Magento Sz Edition
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magento Sz Edition License
 * that is bundled with this package in the file LICENSE_EE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.magentocommerce.com/license/sz-edition
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Sz
 * @package     Sz_Rma
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://www.magentocommerce.com/license/sz-edition
 */

/**
 * RMA model
 *
 * @category   Sz
 * @package    Sz_Rma
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Sz_Rma_Model_Rma_Status_History extends Mage_Core_Model_Abstract
{
    /**
     * Initialize resource model
     */
    protected function _construct()
    {
        $this->_init('sz_rma/rma_status_history');
    }

    /**
     * Get store object
     *
     * @return Mage_Core_Model_Store
     */
    public function getStore()
    {
        if ($this->getOrder()) {
            return $this->getOrder()->getStore();
        }
        return Mage::app()->getStore();
    }

    /**
     * Get RMA object
     *
     * @return Sz_Rma_Model_Rma;
     */
    public function getRma()
    {
        if (!$this->hasData('rma') && $this->getRmaEntityId()) {
            $rma = Mage::getModel('sz_rma/rma')->load($this->getRmaEntityId());
            $this->setData('rma', $rma);
        }
        return $this->getData('rma');
    }

    /**
     * Sending email with comment data
     *
     * @return Sz_Rma_Model_Rma_Status_History
     */
    public function sendCommentEmail()
    {
        /** @var $configRmaEmail Sz_Rma_Model_Config */
        $configRmaEmail = Mage::getSingleton('sz_rma/config');
        $order = $this->getRma()->getOrder();
        if ($order->getCustomerIsGuest()) {
            $customerName = $order->getBillingAddress()->getName();
        } else {
            $customerName = $order->getCustomerName();
        }
        $sendTo = array(
            array(
                'email' => $order->getCustomerEmail(),
                'name'  => $customerName
            )
        );

        return $this->_sendCommentEmail($configRmaEmail->getRootCommentEmail(), $sendTo, true);
    }

    /**
     * Sending email to admin with customer's comment data
     *
     * @return Sz_Rma_Model_Rma_Status_History
     */
    public function sendCustomerCommentEmail()
    {
        /** @var $configRmaEmail Sz_Rma_Model_Config */
        $configRmaEmail = Mage::getSingleton('sz_rma/config');
        $sendTo = array(
            array(
                'email' => $configRmaEmail->getCustomerEmailRecipient($this->getStoreId()),
                'name'  => null
            )
        );

        return $this->_sendCommentEmail($configRmaEmail->getRootCustomerCommentEmail(), $sendTo, false);
    }

    /**
     * Sending email to admin with customer's comment data
     *
     * @param string $rootConfig Current config root
     * @param array $sendTo mail recipient array
     * @param bool $isGuestAvailable
     * @return Sz_Rma_Model_Rma_Status_History
     */
    public function _sendCommentEmail($rootConfig, $sendTo, $isGuestAvailable = true)
    {
        /** @var $configRmaEmail Sz_Rma_Model_Config */
        $configRmaEmail = Mage::getSingleton('sz_rma/config');
        $configRmaEmail->init($rootConfig, $this->getStoreId());

        if (!$configRmaEmail->isEnabled()) {
            return $this;
        }

        $order = $this->getRma()->getOrder();
        $comment = $this->getComment();

        $translate = Mage::getSingleton('core/translate');
        /* @var $translate Mage_Core_Model_Translate */
        $translate->setTranslateInline(false);

        $mailTemplate = Mage::getModel('core/email_template');
        /* @var $mailTemplate Mage_Core_Model_Email_Template */
        $copyTo = $configRmaEmail->getCopyTo();
        $copyMethod = $configRmaEmail->getCopyMethod();
        if ($copyTo && $copyMethod == 'bcc') {
            foreach ($copyTo as $email) {
                $mailTemplate->addBcc($email);
            }
        }

        if ($isGuestAvailable && $order->getCustomerIsGuest()) {
            $template = $configRmaEmail->getGuestTemplate();
        } else {
            $template = $configRmaEmail->getTemplate();
        }

        if ($copyTo && $copyMethod == 'copy') {
            foreach ($copyTo as $email) {
                $sendTo[] = array(
                    'email' => $email,
                    'name'  => null
                );
            }
        }

        foreach ($sendTo as $recipient) {
            $mailTemplate->setDesignConfig(array('area'=>'frontend', 'store'=>$this->getStoreId()))
                ->sendTransactional(
                    $template,
                    $configRmaEmail->getIdentity(),
                    $recipient['email'],
                    $recipient['name'],
                    array(
                        'rma'       => $this->getRma(),
                        'order'     => $this->getRma()->getOrder(),
                        'comment'   => $comment
                    )
                );
        }
        $this->setEmailSent(true);
        $translate->setTranslateInline(true);

        return $this;
    }

    /**
     * Save system comment
     *
     * @return null
     */
    public function saveSystemComment()
    {
        $systemComments = array(
            Sz_Rma_Model_Rma_Source_Status::STATE_PENDING =>
                Mage::helper('sz_rma')->__('Your Return request has been placed.'),
            Sz_Rma_Model_Rma_Source_Status::STATE_AUTHORIZED =>
                Mage::helper('sz_rma')->__('Your Return request has been authorized.'),
            Sz_Rma_Model_Rma_Source_Status::STATE_PARTIAL_AUTHORIZED =>
                Mage::helper('sz_rma')->__('Your Return request has been partially authorized. '),
            Sz_Rma_Model_Rma_Source_Status::STATE_RECEIVED =>
                Mage::helper('sz_rma')->__('Your Return request has been received.'),
            Sz_Rma_Model_Rma_Source_Status::STATE_RECEIVED_ON_ITEM =>
                Mage::helper('sz_rma')->__('Your Return request has been partially received.'),
            Sz_Rma_Model_Rma_Source_Status::STATE_APPROVED_ON_ITEM =>
                Mage::helper('sz_rma')->__('Your Return request has been partially approved.'),
            Sz_Rma_Model_Rma_Source_Status::STATE_REJECTED_ON_ITEM =>
                Mage::helper('sz_rma')->__('Your Return request has been partially rejected.'),
            Sz_Rma_Model_Rma_Source_Status::STATE_CLOSED =>
                Mage::helper('sz_rma')->__('Your Return request has been closed.'),
            Sz_Rma_Model_Rma_Source_Status::STATE_PROCESSED_CLOSED =>
                Mage::helper('sz_rma')->__('Your Return request has been processed and closed.'),
        );

        $rma = $this->getRma();
        if (!($rma instanceof Sz_Rma_Model_Rma)) {
            return;
        }

        if (($rma->getStatus() !== $rma->getOrigData('status') && isset($systemComments[$rma->getStatus()]))) {
            $this->setRmaEntityId($rma->getEntityId())
                ->setComment($systemComments[$rma->getStatus()])
                ->setIsVisibleOnFront(true)
                ->setStatus($rma->getStatus())
                ->setCreatedAt(Mage::getSingleton('core/date')->gmtDate())
                ->setIsAdmin(1)
                ->save();
        }
    }
}
