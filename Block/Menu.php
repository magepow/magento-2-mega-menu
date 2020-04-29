<?php
/**
 * Magiccart 
 * @category  Magiccart 
 * @copyright   Copyright (c) 2014 Magiccart (http://www.magiccart.net/) 
 * @license   http://www.magiccart.net/license-agreement.html
 * @Author: Magiccart<team.magiccart@gmail.com>
 * @@Create Date: 2016-02-28 10:10:00
 * @@Modify Date: 2018-10-13 09:09:06
 * @@Function:
 */
namespace Magiccart\Magicmenu\Block;

class Menu extends \Magento\Catalog\Block\Navigation
{

    /**
     * @var Category
     */
    protected $_categoryInstance;

    /**
     * Current category key
     *
     * @var string
     */
    protected $_currentCategoryKey;

    /**
     * Array of level position counters
     *
     * @var array
     */
    protected $_itemLevelPositions = [];

    /**
     * Catalog category
     *
     * @var \Magento\Catalog\Helper\Category
     */
    protected $_catalogCategory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_registry;

    /**
     * Customer session
     *
     * @var \Magento\Framework\App\Http\Context
     */
    protected $httpContext;

    /**
     * Catalog layer
     *
     * @var \Magento\Catalog\Model\Layer
     */
    protected $_catalogLayer;

    /**
     * Product collection factory
     *
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $_productCollectionFactory;

    /**
     * @var \Magento\Catalog\Model\Indexer\Category\Flat\State
     */
    protected $flatState;


    // +++++++++add new +++++++++

    public $_sysCfg;

    protected $_urlMedia;

    protected $_dirMedia;

    protected $_recursionLevel;

    protected $extData = array();

    /**
     * magicmenu collection factory.
     *
     * @var \Magiccart\Magicmenu\Model\ResourceModel\Magicmenu\CollectionFactory
     */
    protected $_magicmenuCollectionFactory;


    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Catalog\Model\Layer\Resolver $layerResolver,
        \Magento\Framework\App\Http\Context $httpContext,
        \Magento\Catalog\Helper\Category $catalogCategory,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Model\Indexer\Category\Flat\State $flatState,

        // +++++++++add new +++++++++
        // \Magiccart\Magicmenu\Model\CategoryFactory $categoryFactory,
        \Magiccart\Magicmenu\Model\ResourceModel\Magicmenu\CollectionFactory $magicmenuCollectionFactory,

        array $data = []
    ) {

        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_catalogLayer = $layerResolver->get();
        $this->httpContext = $httpContext;
        $this->_catalogCategory = $catalogCategory;
        $this->_registry = $registry;
        $this->flatState = $flatState;
        $this->_categoryInstance = $categoryFactory->create();

        // +++++++++add new +++++++++
        $this->_magicmenuCollectionFactory = $magicmenuCollectionFactory;
        $this->_sysCfg= (object) $context->getScopeConfig()->getValue(
            'magicmenu',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        parent::__construct($context, $categoryFactory, $productCollectionFactory, $layerResolver, $httpContext, $catalogCategory, $registry, $flatState, $data);

        $this->_urlMedia = $this->_storeManager->getStore()->getBaseUrl(
                \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
            );

        $this->_dirMedia = $this->getMediaDirectory()->getAbsolutePath();

        $this->_recursionLevel = max(
            0,
            (int)$context->getScopeConfig()->getValue(
                'catalog/navigation/max_depth',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            )
        );



    }

    public function getIsHomePage()
    {
        return $this->getUrl('') == $this->getUrl('*/*/*', array('_current'=>true, '_use_rewrite'=>true));
    }

    public function isCategoryActive($catId)
    {
        return $this->getCurrentCategory() ? in_array($catId, $this->getCurrentCategory()->getPathIds()) : false;
    }

    public function isHasActive($catId)
    {
        return ($catId != $this->getCurrentCategory()->getId()) ? true : false;
    }

    protected function _getActiveClasses($catId)
    {
        $classes = '';
        if ($this->isCategoryActive($catId)) {
            if($this->isHasActive($catId)){
                $classes = 'has-active active';
            } else {
                $classes = 'active';
            }
            
        }
        return $classes;
    }

    public function getLogo()
    {
        return $this->getLayout()->createBlock('Magento\Theme\Block\Html\Header\Logo')->toHtml();
    }

    public function getRootName()
    {
        $rootCatId = $this->_storeManager->getStore()->getRootCategoryId();
        return $this->_categoryInstance->load($rootCatId)->getName();
    }

    public function drawHomeMenu()
    {
        if($this->hasData('homeMenu')) return $this->getData('homeMenu');
        $drawHomeMenu = '';
        $active = ($this->getIsHomePage()) ? ' active' : '';
        $drawHomeMenu .= '<li class="level0 category-item level-top dropdown home' . $active . '">';
        $drawHomeMenu .= '<a class="level-top" href="'.$this->getBaseUrl().'"><span class="icon fa fa-home"></span><span class="icon-text">' .__('Home') .'</span>';
        $drawHomeMenu .= '</a>';
        if($this->_sysCfg->topmenu['demo']){
            $demo = '';
            $currentStore = $this->_storeManager->getStore();
            $switcher = $this->getLayout()->createBlock('Magento\Store\Block\Switcher');
            foreach ($this->_storeManager->getWebsites() as $website) {
                $groups = $website->getGroups();
                if(count($groups) > 1){
                    foreach ($groups as $group) {
                        $store = $group->getDefaultStore();
                        if ($store && !$store->getIsActive()) {
                            $stores = $group->getStores();
                            foreach ($stores as $store) {
                                if ($store->getIsActive()) break;
                                else $store = '';
                            }
                        }                     
                        if($store){
                            if( $store->getCode() == $currentStore->getCode() )  $demo .= '<li class="level1"><a href="' .$store->getBaseUrl(). '"><span class="demo-home">'. $group->getName(). '</span></a></li>';
                            else $demo .= '<li class="switcher-option level1"><a href="#" data-post='. $switcher->getTargetStorePostData($store) . '><span class="demo-home">'. $group->getName(). '</span></a></li>';


                        }
                    }
                }
            }
            if($demo) $drawHomeMenu .= '<ul class="level0 category-item submenu">' .$demo .'</ul>';           
        }

        $drawHomeMenu .= '</li>';
        $this->setData('homeMenu', $drawHomeMenu);
        return $drawHomeMenu;
    }

    public function drawMainMenu()
    {
        if($this->hasData('mainMenu')) return $this->getData('mainMenu');
        $desktopHtml = array();
        $mobileHtml  = array();
        $rootCatId = $this->_storeManager->getStore()->getRootCategoryId();
        $catListTop = $this->getChildExt($rootCatId);
        $contentCatTop  = $this->getContentCatTop();

        foreach ($contentCatTop as $ext) {
            $this->extData[$ext->getCatId()] = $ext->getData();
        }
        $last = count($catListTop);
        $dropdownIds = explode(',', $this->_sysCfg->general['dropdown']);
        $counter = 1;
        
        foreach ($catListTop as $catTop){
            $parentPositionClass = '';
            $itemPositionClassPrefix = $parentPositionClass ? $parentPositionClass . '-' : 'nav-';
            $idTop    = $catTop->getEntityId();
            $urlTop      =  '<a class="level-top" href="' .$catTop->getUrl(). '">' .$this->getThumbnail($catTop). '<span>' .__($catTop->getName()) . $this->getCatLabel($catTop). '</span><span class="boder-menu"></span></a>';

            $itemPositionClassPrefixTop = $itemPositionClassPrefix . $counter;
            $classTop   = $itemPositionClassPrefixTop . ' ' . $this->_getActiveClasses($idTop);
            $isDropdown = in_array($idTop, $dropdownIds) ? ' dropdown' : '';
            // drawMainMenu
            $options  = '';
            if($this->_recursionLevel == 1){
                $menu = array('desktop' => '', 'mobile' => '');               
            }else {
                if($isDropdown){
                    $classTop .= $isDropdown;
                    $childHtml = $this->getTreeCategoriesExt($idTop, $itemPositionClassPrefixTop); // include magic_label
                    // $childHtml = $this->getTreeCategoriesExtra($idTop, $itemPositionClassPrefixTop); // include magic_label and Maximal Depth
                    $menu = array('desktop' => $childHtml, 'mobile' => $childHtml);
                } else { // Draw Mega Menu
                    $idTop    = $catTop->getEntityId();
                    $data     = isset($this->extData[$idTop]) ? $this->extData[$idTop] : '';
                    $blocks   = array('top'=>'', 'left'=>'', 'right'=>'', 'bottom'=>'');
                    if($data){
                        foreach ($blocks as $key => $value) {
                            $proportion = $key .'_proportion';
                            $html = $this->getStaticBlock($data[$key]);
                            if($html) $blocks[$key] = "<div class='mage-column mega-block-$key'>".$html.'</div>';
                        }
                        $remove = array('top'=>'', 'left'=>'', 'right'=>'', 'bottom'=>'', 'cat_id'=>'');
                        foreach ($remove as $key => $value) {
                            unset($data[$key]);
                        }
                        $opt     = json_encode($data);
                        $options = $opt ? " data-options='$opt'" : '';
                    }
                    $menu = $this->getMegamenu($catTop, $blocks, $itemPositionClassPrefixTop);
                }               
            }

            if($menu['desktop']) $classTop .= ' hasChild parent';

            $desktopHtml[$idTop] = '<li class="level0 category-item level-top cat ' . $classTop . '"' . $options .'>' . $urlTop . $menu['desktop'] . '</li>';
            $mobileHtml[$idTop]  = '<li class="level0 category-item level-top cat ' . $classTop . '">' . $urlTop . $menu['mobile'] . '</li>';
            $counter++;     
        }
        $menu['desktop'] = $desktopHtml;
        $menu['mobile'] = implode("\n", $mobileHtml);
        $this->setData('mainMenu', $menu);
        return $menu;
    }

    public function getMegamenu($catTop, $blocks, $itemPositionClassPrefix)
    {
        // Draw Mega Menu 
        $idTop    = $catTop->getEntityId();
        $hasChild = $catTop->hasChildren();
        $desktopTmp = $mobileTmp  = '';
        if($hasChild || $blocks['top'] || $blocks['left'] || $blocks['right'] || $blocks['bottom']) :
            $desktopTmp .= '<div class="level-top-mega">';  /* Wrap Mega */
                $desktopTmp .='<div class="content-mega">';  /*  Content Mega */
                    $desktopTmp .= $blocks['top'];
                    $desktopTmp .= '<div class="content-mega-horizontal">';
                        $desktopTmp .= $blocks['left'];
                        if($hasChild) :
                            $desktopTmp .= '<ul class="level0 category-item mage-column cat-mega">';
                            $mobileTmp .= '<ul class="submenu">';
                            $childTop  =  $this->getChildExt($idTop);
                            $counter = 1;
                            foreach ($childTop as $child) {
                                $itemPositionClassPrefixChild = $itemPositionClassPrefix . '-' . $counter;
                                $class = 'level1 category-item ' . $itemPositionClassPrefixChild . ' ' . $this->_getActiveClasses($child->getId());
                                $url =  '<a href="'. $child->getUrl().'"><span>'.__($child->getName()) . $this->getCatLabel($child) . '</span></a>';
                                $childHtml = ($this->_recursionLevel != 2 ) ? $this->getTreeCategoriesExt($child->getId(), $itemPositionClassPrefixChild) : ''; // include magic_label
                                // $childHtml = ($this->_recursionLevel != 2 ) ? $this->getTreeCategoriesExtra($child->getId()) : ''; // include magic_label and Maximal Depth
                                $desktopTmp .= '<li class="children ' . $class . '">' . $this->getImage($child) . $url . $childHtml . '</li>';
                                $mobileTmp  .= '<li class="' . $class . '">' . $url . $childHtml . '</li>';
                                $counter++;
                            }
                            //$desktopTmp .= '<li>'  .$blocks['bottom']. '</li>';
                            $desktopTmp .= '</ul>'; // end cat-mega
                            $mobileTmp .= '</ul>';
                        endif;
                        $desktopTmp .= $blocks['right'];
                    $desktopTmp .= '</div>';
                    $desktopTmp .= $blocks['bottom'];
                $desktopTmp .= '</div>';  /* End Content mega */
            $desktopTmp .= '</div>';  /* Warp Mega */
        endif;
        return array('desktop' => $desktopTmp, 'mobile' => $mobileTmp);
    }

    public function drawExtraMenu()
    {
        if($this->hasData('extraMenu')) return $this->getData('extraMenu');
        $extMenu    = $this->getExtraMenu();
        $count = count($extMenu);
        $drawExtraMenu = '';
        if($count){
            $i = 1; $class = 'first';
            $currentUrl = $this->getUrl('*/*/*', ['_current' => true, '_use_rewrite' => true]); //$this->getCurrentUrl();
            foreach ($extMenu as $ext) { 
                $link = $ext->getLink();
                $url = (filter_var($link, FILTER_VALIDATE_URL)) ? $link : $this->getUrl('', array('_direct'=>$link));
                $active = ( $link && $url == $currentUrl) ? ' active' : '';
                $html = $this->getStaticBlock($ext->getExtContent());
                $class .= $ext->getCatCol() ? ' ' . $ext->getCatCol() : ' dropdown';
                if($html) $active .=' hasChild parent';
                $drawExtraMenu .= "<li class='level0 category-item level-top ext $active $class'>";
                    if($link) $drawExtraMenu .= '<a class="level-top" href="' .$url. '"><span>' .__($ext->getName()) . $this->getCatLabel($ext). '</span></a>';
                    else $drawExtraMenu .= '<span class="level-top"><span>' .__($ext->getName()) . $this->getCatLabel($ext). '</span></span>';
                    if($html) $drawExtraMenu .= $html; //$drawExtraMenu .= '<div class="level-top-mega">'.$html.'</div>';
                $drawExtraMenu .= '</li>';
                $i++;
                $class = ($i == $count) ? 'last' : '';  
            }
        }
        $this->setData('extraMenu', $drawExtraMenu);
        return $drawExtraMenu;
    }

    public function getChildExt($parentId)
    {
        $collection = $this->_categoryInstance->getCollection()
                        ->addAttributeToSelect(array('entity_id','name','magic_label','url_path'))
                        ->addAttributeToFilter('parent_id', $parentId)
                        ->addAttributeToFilter('include_in_menu', 1)
                        ->addIsActiveFilter()
                        ->addAttributeToSort('position', 'asc'); //->addOrderField('name');
        return $collection;
    }

    public function getExtraMenu()
    {
        $store = $this->_storeManager->getStore()->getStoreId();
        $collection = $this->_magicmenuCollectionFactory->create()
                        ->addFieldToSelect(array('link','name', 'cat_col', 'magic_label','ext_content','order'))
                        ->addFieldToFilter('extra', 1)
                        ->addFieldToFilter('status', 1);
        $collection->getSelect()->where('find_in_set(0, stores) OR find_in_set(?, stores)', $store)->order('order');
        return $collection;        
    }

    public function getStaticBlock($id)
    {
        return $this->getLayout()->createBlock('Magento\Cms\Block\Block')->setBlockId($id)->toHtml();
    }

    public function getContentCatTop()
    {
        $store = $this->_storeManager->getStore()->getStoreId();
        $collection = $this->_magicmenuCollectionFactory->create()
                        ->addFieldToSelect(array(
                                'cat_id','cat_col','cat_proportion','top',
                                'right','right_proportion','bottom','left','left_proportion'
                            ))
                        ->addFieldToFilter('stores',array( array('finset' => 0), array('finset' => $store)))
                        ->addFieldToFilter('status', 1);
        return $collection;
    }

    public function  getTreeCategoriesExt($parentId, $itemPositionClassPrefix) // include Magic_Label
    { 
        $categories = $this->_categoryInstance->getCollection()
                        ->addAttributeToSelect(array('name','magic_label','url_path'))
                        ->addAttributeToFilter('include_in_menu', 1)
                        ->addAttributeToFilter('parent_id', $parentId)
                        ->addIsActiveFilter()
                        ->addAttributeToSort('position', 'asc'); 
        $html = '';
        $counter = 1;
        foreach($categories as $category)
        {
            $level = $category->getLevel();
            $childHtml = ( $this->_recursionLevel == 0 || ($level -1 < $this->_recursionLevel) ) ? $this->getTreeCategoriesExt($category->getId(), $itemPositionClassPrefix) : '';
            $childClass = $childHtml ? ' hasChild parent category-item ' : ' category-item ';
            $childClass .= $itemPositionClassPrefix . '-' .$counter;
            $childClass .= $this->_getActiveClasses($category->getId());
            $html .= '<li class="level' . ($level -2) . $childClass . '"><a href="' . $category->getUrl() . '"><span>' . $category->getName() . $this->getCatLabel($category) . "</span></a>\n" . $childHtml . '</li>';
            $counter++;
        }
        if($html) $html = '<ul class="level'.($level -3).' submenu">' .$html. '</ul>';
        return $html;
    }

    public function  getTreeCategoriesExtra($parentId, $itemPositionClassPrefix) // include Magic_Label and Maximal Depth
    {
        $html = '';
        $categories = $this->_categoryInstance->getCategories($parentId);
        $counter = 1;
        foreach($categories as $category) {
            $cat = $this->_categoryInstance->load($category->getId());
            $count = $cat->getProductCount();
            $level = $cat->getLevel();
            $childHtml = ( $this->_recursionLevel == 0 || ($level -1 < $this->_recursionLevel) ) ? $this->getTreeCategoriesExtra($category->getId(), $itemPositionClassPrefix) : '';
            $childClass  = $childHtml ? ' hasChild parent' : '';
            $childClass .= $itemPositionClassPrefix . '-' .$counter;
            $childClass .= ' category-item ' . $this->_getActiveClasses($category->getId());
            $html .= '<li class="level' . ($level -2) . $childClass . '"><a href="' . $this->getCategoryUrl($category) . '"><span>' . $cat->getName() . "(".$count.")" . $this->getCatLabel($cat) . "</span></a>\n";
            $html .= $childHtml;
            $html .= '</li>';
            $counter++;
        }
        if($html) $html = '<ul class="level' .($level -3). ' submenu">' . $html . '</ul>';
        return  $html;
    }

    public function getCatLabel($cat)
    {
        $html = '';
        $label = explode(',', $cat->getMagicLabel());
        foreach ($label as $lab) {
          if($lab) $html .= '<span class="cat_label '.$lab.'">'.__(trim($lab)) .'</span>';
        }
        return $html;
    }

    public function getImage($object)
    {
        $url = '';
        $image = $this->_dirMedia . 'magiccart/magicmenu/images/' . $object->getId() .'.png';
        if(file_exists($image)) $url = $this->_urlMedia . 'magiccart/magicmenu/images/' . $object->getId() .'.png';
        if($url) return '<a class="a-image" href="' .$object->getUrl(). '"><img class="img-responsive" alt="' .$object->getName(). '" src="'.$url.'"></a>';
    }

    public function getThumbnail($object)
    {
        $url = '';
        $image = $this->_dirMedia . 'magiccart/magicmenu/thumbnail/' . $object->getId() .'.png';
        if(file_exists($image)) $url = $this->_urlMedia . 'magiccart/magicmenu/thumbnail/' . $object->getId() .'.png';
        if($url) return '<img class="img-responsive" alt="' .$object->getName(). '" src="'.$url.'">';
    }

}
