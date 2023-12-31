<?php

class Smartwave_Megamenu_Helper_Data extends Mage_Core_Helper_Abstract
{
    private $_menuData = null;
	private $_block = null;
    
    public function getConfig($optionString)
    {
        return Mage::getStoreConfig('megamenu/' . $optionString);
    }
    public function getMegamenuBlock() {
        if(!$this->_block) {
            $blockClassName = Mage::getConfig()->getBlockClassName('megamenu/navigation');
            $this->_block = new $blockClassName();
        }
        return $this->_block;
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
            '_popupWidth'                   => Mage::getStoreConfig('megamenu/popup/width') . '0'
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
        $homeLinkUrl = str_replace("http://","//",$homeLinkUrl);
        $homeLinkUrl = str_replace("https://","//",$homeLinkUrl);
        $homeLinkText       = $this->__('Home');
        $homeLink           = '';
        $homeIconClass      = '';
        if ($this->getIsHomePage()) {
            $homeIconClass = 'act';
        }
        if ($_showHomeLink) {
            if ($_showHomeIcon && $mode == 'dt') {
                $homeLinkText = '<img src="'.$this->getHomeIcon().'" alt="'.$this->__('Home').'" title="'.$this->__('Home').'"/>';                
                $homeIconClass .= ' home-icon-img';
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
        return '';
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
    
    public function getMobileMenuContent($without_custom_block = false)
    {
        $menuData = $this->getMenuData();
        extract($menuData);
        $homeLink = "";
        $blogLink = "";
        if(!$without_custom_block){
            // --- Home Link ---
            $homeLink = $this->getHomeLink('mb');
            // --- Blog Link ---
            $blogLink = $this->getBlogLink();
        }
        // --- Menu Content ---
        $mobileMenuContent = '';
        $mobileMenuContentArray = array();
        foreach ($_categories as $_category) {
            $mobileMenuContentArray[] = $_block->drawMegaMenuItem($_category,'mb');
        }
        if (count($mobileMenuContentArray)) {
            $mobileMenuContent = implode("\n", $mobileMenuContentArray);
            $mobileMenuContent = str_replace("http://","//",$mobileMenuContent);
            $mobileMenuContent = str_replace("https://","//",$mobileMenuContent);
        }
        $customMobileLinks = "";
        if(!$without_custom_block){
            $customMobileLinks = $_block->drawCustomMobileLinks();
        }
        // --- Result ---
        $menu = <<<HTML
$homeLink
$mobileMenuContent
$blogLink
$customMobileLinks
HTML;
        return $menu;
    }
    
    public function getMenuContent($without_custom_block = false)
    {
        $menuData = $this->getMenuData();
        extract($menuData);
        $homeLink = "";
        $blogLink = "";
        if(!$without_custom_block){
            // --- Home Link ---        
            $homeLink = $this->getHomeLink();
            // --- Blog Link ---
            $blogLink = $this->getBlogLink();
        }
        // --- Menu Content ---
        $menuContent = '';
        $menuContentArray = array();
        foreach ($_categories as $_category) {
            $menuContentArray[] = $_block->drawMegaMenuItem($_category,'dt');
        }
        if (count($menuContentArray)) {
            $menuContent = implode("\n", $menuContentArray);
            $menuContent = str_replace("http://","//",$menuContent);
            $menuContent = str_replace("https://","//",$menuContent);
        }
        $customLinks = "";
        $customBlocks = "";
        
        if(!$without_custom_block){
            // --- Custom Links
            $customLinks = $_block->drawCustomLinks();              
            // --- Custom Blocks
            $customBlocks = $_block->drawCustomBlock();
        }
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
    public function getActiveChildren($parent, $level=0)
    {
        $activeChildren = array();
        // --- check level ---
        $maxLevel = (int)Mage::getStoreConfig('megamenu/general/max_level');
        if ($maxLevel > 0)
        {
            if ($level >= ($maxLevel - 1)) return $activeChildren;
        }
        // --- / check level ---
        
        $children = Mage::getModel('catalog/category')->getCategories($parent->getId());
        $childrenCount = count($children);
        
        $hasChildren = $children && $childrenCount;
        if ($hasChildren)
        {
            foreach ($children as $child)
            {
                if ($this->_isCategoryDisplayed($child))
                {
                    array_push($activeChildren, $child);
                }
            }
        }
        return $activeChildren;
    }
    private function _isCategoryDisplayed(&$child)
    {
        if (!$child->getIsActive()) return false;
        // === check products count ===
        // --- get collection info ---
        if (!Mage::getStoreConfig('megamenu/general/display_empty_categories'))
        {
            $data = $this->_getProductsCountData();
            // --- check by id ---
            $id = $child->getId();
            #Mage::log($id); Mage::log($data);
            if (!isset($data[$id]) || !$data[$id]['product_count']) return false;
        }
        // === / check products count ===
        return true;
    }
    private function _getProductsCountData()
    {
        if (is_null($this->_productsCount))
        {
            $collection = Mage::getModel('catalog/category')->getCollection();
            $storeId = Mage::app()->getStore()->getId();
            /* @var $collection Mage_Catalog_Model_Resource_Eav_Mysql4_Category_Collection */
            $collection->addAttributeToSelect('name')
                ->addAttributeToSelect('is_active')
                ->setStoreId($storeId);
            if(!Mage::helper('catalog/category_flat')->isEnabled()){
                $collection->setProductStoreId($storeId)
                    ->setLoadProductCount(true);
            }
            $productsCount = array();
            foreach($collection as $cat)
            {
                $productsCount[$cat->getId()] = array(
                    'name' => $cat->getName(),
                    'product_count' => $cat->getProductCount(),
                );
            }
            #Mage::log($productsCount);
            $this->_productsCount = $productsCount;
        }
        return $this->_productsCount;
    }
    public function getFirstLevelCategories() {
        $root_id = Mage::app()->getStore()->getRootCategoryId();
        $children = Mage::getModel('catalog/category')->getCategories($root_id);
        return $children;
    }
}
