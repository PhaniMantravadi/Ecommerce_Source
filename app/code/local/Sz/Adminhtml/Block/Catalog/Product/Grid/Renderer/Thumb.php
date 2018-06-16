<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2010-2011 Amasty (http://www.amasty.com)
* @package Amasty_Pgrid
*/
class Sz_Adminhtml_Block_Catalog_Product_Grid_Renderer_Thumb extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        try
        {
            $size    = 70;

            if (!$row->getThumbnail())
            {
                $product = Mage::getModel('catalog/product')->load($row->getEntityId());
                if ($product)
                {
                    if ($product->getThumbnail())
                    {
                        $row->setThumbnail($product->getThumbnail());
                    }
                }
            }

            $url     = Mage::helper('catalog/image')->init($row, 'thumbnail')->resize($size)->__toString();

            if ($url)
            {
                $html = '<img src="' . $url . '" alt="" width="' . $size . '" height="' . $size . '" />';
                return $html;
            }
        } catch (Exception $e) { /* no file uploaded */ }
        return '';
    }
}