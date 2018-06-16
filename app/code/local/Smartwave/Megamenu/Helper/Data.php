<?php

class Smartwave_Megamenu_Helper_Data extends Mage_Core_Helper_Abstract
{
    private $_menuData = null;
	private $_block = null;
    
    public function getConfig($optionString)
    {
        return Mage::getStoreConfig('megamenu/' . $optionString);
    }
       
    public function getCustomLink()
    {
		if(!$this->_block) {
			$blockClassName = Mage::getConfig()->getBlockClassName('megamenu/navigation');
			$this->_block = new $blockClassName();
		}
        $customLinks = $this->_block->drawCustomLinks();
        return $customLinks;
    }
    public function getHomeIcon()
    {
        if ($this->getConfig('general/show_home_link') && $this->getConfig('general/show_home_icon')) {
            $icon = $this->getConfig('general/home_icon');
            if ($icon)
                return Mage::getBaseUrl('media') . 'smartwave/megamenu/html/' . $icon;
            return Mage::getBaseUrl('media') . 'smartwave/megamenu/html/icon_home.png';
        }
        return false;
        
    }
    
    public function getCustomStyle()
    {
        $customStyle = $this->getConfig('custom/custom_style');
        if (!$customStyle) return;
        return $customStyle;
    }
    
    public function getMenuData()
    {
        if (!is_null($this->_menuData)) return $this->_menuData;

        if(!$this->_block) {
			$blockClassName = Mage::getConfig()->getBlockClassName('megamenu/navigation');
			$this->_block = new $blockClassName();
		}
        $categories = $this->_block->getStoreCategories();        
        if (is_object($categories)) $categories = $categories->getNodes();

        $this->_menuData = array(
            '_block'                        => $this->_block,
            '_categories'                   => $categories,
            '_isWide'                       => Mage::getStoreConfig('megamenu/general/wide_style'),
            '_showHomeLink'                 => Mage::getStoreConfig('megamenu/general/show_home_link'),
            '_showHomeIcon'                 => Mage::getStoreConfig('megamenu/general/show_home_icon'),
            '_popupWidth'                   => Mage::getStoreConfig('megamenu/popup/width') + 0            
        );        
        return $this->_menuData;
    }
    
    public function getHomeLink($mode = 'dt')
    {
        $store = Mage::app()->getStore();
        $store_id  = $store->getId();

        $menuData = $this->getMenuData();
        extract($menuData);
        $homeLinkUrl        = Mage::app()->getStore($store_id)->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK);
        $homeLinkText       = $this->__('Home');
        $homeLink           = '';
        $homeIconClass      = '';
        if ($this->getIsHomePage()) {
            $homeIconClass = 'act';
        }

            $homeLink = <<<HTML
<li class="$homeIconClass">
    <a href="$homeLinkUrl">
       <span>$homeLinkText</span>
    </a>
</li>
HTML;
            return $homeLink;
       
    }
    
    public function getBlogLink()
    {
		//---updated from version 1.0.2---
     if (Mage::getStoreConfig('blog/menu/top_menu') && Mage::getStoreConfig('blog/blog/enabled')) {
        $menuData = $this->getMenuData();
        extract($menuData);
        $blogLinkUrl        = Mage::helper('blog')->getRouteUrl();
        $blogLinkText       = $this->__('Blog');
        $blogLink           = <<<HTML
<li>
    <a href="$blogLinkUrl" class="blog-nav">
       <span>$blogLinkText</span>
    </a>
</li>
HTML;
       return $blogLink;
        }else{
            return '';
        }
        
    }
    
    public function getMobileMenuContent()
    {
        $menuData = $this->getMenuData();
        extract($menuData);
        // --- Home Link ---
        $homeLink = $this->getHomeLink('mb');
        // --- Blog Link ---
        $blogLink = $this->getBlogLink();
        // --- Menu Content ---
        $mobileMenuContent = '';
        $mobileMenuContentArray = array();
        foreach ($_categories as $_category) {
            $mobileMenuContentArray[] = $_block->drawMegaMenuItem($_category,'mb');
        }
        if (count($mobileMenuContentArray)) {
            $mobileMenuContent = implode("\n", $mobileMenuContentArray);
        }
        
        $customMobileLinks = $_block->drawCustomMobileLinks();
        // --- Result ---
        $menu = <<<HTML
$homeLink
$mobileMenuContent
$blogLink
$customMobileLinks
HTML;
        return $menu;
    }
    
    public function getMenuContent()
    {
        $menuData = $this->getMenuData();
        extract($menuData);
        // --- Home Link ---        
        $homeLink = $this->getHomeLink();
        // --- Blog Link ---
        $blogLink = $this->getBlogLink();
        // --- Menu Content ---
        $menuContent = '';
        $menuContentArray = array();
        foreach ($_categories as $_category) {
            $menuContentArray[] = $_block->drawMegaMenuItem($_category,'dt');
        }
        if (count($menuContentArray)) {
            $menuContent = implode("\n", $menuContentArray);
        }
        // --- Custom Links
        $customLinks = $_block->drawCustomLinks();              
        // --- Custom Blocks
        $customBlocks = $_block->drawCustomBlock();              
        // --- Result ---
        $menu = <<<HTML
$homeLink
$menuContent
$blogLink
$customLinks
$customBlocks
HTML;
        return $menu;
    }
    public function getLogoAlt() 
    {
        $menuData = $this->getMenuData();
        extract($menuData);
        return $_block->getLogoAlt();
    }
    public function getLogoSrc()
    {
        $menuData = $this->getMenuData();
        extract($menuData);
        return $_block->getLogoSrc();
    }
    public function getIsHomePage()
    {
        if(Mage::app()->getFrontController()->getRequest()->getActionName()=='index' && Mage::app()->getFrontController()->getRequest()->getRouteName()=='cms' && Mage::app()->getFrontController()->getRequest()->getControllerName()=='index')
            return true;
        return false;
    }
}