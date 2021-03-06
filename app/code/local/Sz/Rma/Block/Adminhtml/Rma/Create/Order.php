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
 * Admin RMA create order block
 *
 * @category    Sz
 * @package     Sz_Rma
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 */

class Sz_Rma_Block_Adminhtml_Rma_Create_Order extends Sz_Rma_Block_Adminhtml_Rma_Create_Abstract
{
    /**
     * Get Header Text for Order Selection
     *
     * @return string
     */
    public function getHeaderText()
    {
        return Mage::helper('sz_rma')->__('Please Select Order');
    }
}
