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
 * Rma Item Form Renderer Block for select
 *
 * @category    Sz
 * @package     Sz_Rma
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Sz_Rma_Block_Form_Renderer_Select extends Sz_Eav_Block_Form_Renderer_Select
{
    /**
     * Prepare rma item attribute
     *
     * @return boolean | Sz_Rma_Model_Item_Attribute
     */
    public function getAttribute($code)
    {
        /* @var $itemModel  */
        $itemModel = Mage::getModel('sz_rma/item');

        /* @var $itemForm Sz_Rma_Model_Item_Form */
        $itemForm   = Mage::getModel('sz_rma/item_form');
        $itemForm->setFormCode('default')
            ->setStore($this->getStore())
            ->setEntity($itemModel);

        $attribute = $itemForm->getAttribute($code);
        if ($attribute->getIsVisible()) {
            return $attribute;
        }
        return false;
    }
}

