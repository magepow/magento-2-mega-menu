<?php
/**
 * Magiccart 
 * @category  Magiccart 
 * @copyright   Copyright (c) 2014 Magiccart (http://www.magiccart.net/) 
 * @license   http://www.magiccart.net/license-agreement.html
 * @Author: Magiccart<team.magiccart@gmail.com>
 * @@Create Date: 2016-02-28 10:10:00
 * @@Modify Date: 2021-04-28 09:09:06
 * @@Function:
 */
namespace Magiccart\Magicmenu\Block;

use Magento\Framework\Data\Tree\Node;
use Magento\Framework\Data\Tree\Node\Collection;
use Magento\Framework\Data\Tree\NodeFactory;
use Magento\Framework\Data\TreeFactory;

class Menu extends \Magento\Catalog\Block\Navigation
{

    const DEFAULT_CACHE_TAG = 'MAGICCART_MAGICMENU';

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    private $serializer;

    /**
     * Top menu data tree
     *
     * @var Node
     */
    protected $_menu;

    /**
     * @var NodeFactory
     */
    private $nodeFactory;

    /**
     * @var TreeFactory
     */
    private $treeFactory;

    /**
     * magicmenu collection factory.
     *
     * @var \Magiccart\Magicmenu\Model\ResourceModel\Magicmenu\CollectionFactory
     */
    protected $_magicmenuCollectionFactory;

    protected $_urlMedia;

    protected $_dirMedia;

    protected $extData = [];

    public $_sysCfg;
    
    /**
     * @var \Magiccart\Magicmenu\Helper\Data
     */
    public $_helper;

    public $rootCategory;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Catalog\Model\Layer\Resolver $layerResolver,
        \Magento\Framework\App\Http\Context $httpContext,
        \Magento\Catalog\Helper\Category $catalogCategory,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Model\Indexer\Category\Flat\State $flatState,
        \Magento\Framework\Serialize\Serializer\Json $serializer = null,

        // +++++++++add new +++++++++
        \Magiccart\Magicmenu\Helper\Data $helper,
        // \Magiccart\Magicmenu\Model\CategoryFactory $categoryFactory,
        \Magiccart\Magicmenu\Model\ResourceModel\Magicmenu\CollectionFactory $magicmenuCollectionFactory,
        NodeFactory $nodeFactory,
        TreeFactory $treeFactory,
        array $data = []
    ) {

        $this->_helper = $helper;
        $this->_magicmenuCollectionFactory = $magicmenuCollectionFactory;
        $this->_sysCfg= (object) $this->_helper->getConfigModule();

        $this->serializer = $serializer ?: \Magento\Framework\App\ObjectManager::getInstance()
        ->get(\Magento\Framework\Serialize\Serializer\Json::class);

        $this->nodeFactory = $nodeFactory;
        $this->treeFactory = $treeFactory;

        parent::__construct($context, $categoryFactory, $productCollectionFactory, $layerResolver, $httpContext, $catalogCategory, $registry, $flatState, $data);

        $this->_urlMedia = $this->_storeManager->getStore()->getBaseUrl(
                \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
            );

        $this->_dirMedia = $this->getMediaDirectory()->getAbsolutePath();

    }

    protected function getCacheLifetime()
    {
        return parent::getCacheLifetime() ?: 86400;
    }

    public function getCacheKeyInfo()
    {
        $keyInfo     =  parent::getCacheKeyInfo();
        $keyInfo[]   =  $this->getCurrentCategory()->getId();
        return $keyInfo;
    }

    /**
     * @return array
     */
    public function getIdentities()
    {
        return [self::DEFAULT_CACHE_TAG, self::DEFAULT_CACHE_TAG . '_' . $this->getCurrentCategory()->getId()];
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

    public function getRootCategory()
    {
        if(!$this->rootCategory){
            $rootCatId = $this->_storeManager->getStore()->getRootCategoryId();
            $this->rootCategory = $this->_categoryInstance->load($rootCatId);            
        }
        return $this->rootCategory;
    }

    public function getRootName()
    {
        return $this->getRootCategory()->getName();
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
                            if( $store->getCode() == $currentStore->getCode() ){
                                $demo .= '<li class="level1"><a href="' .$store->getBaseUrl(). '"><span class="demo-home">'. $group->getName(). '</span></a></li>';
                            } else {
                                $dataPost = $switcher->getTargetStorePostData($store);
                                $dataPost = $this->serializer->unserialize($dataPost);
                                if(isset($dataPost['action']) && isset($dataPost['data'])){
                                    $href = $dataPost['action'] . '?' . http_build_query($dataPost['data']);
                                    $demo .= '<li class="switcher-option level1"><a href="' . $href . '"><span class="demo-home">'. $group->getName(). '</span></a></li>';
                                }
                            }
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

        $desktopHtml   = [];
        $mobileHtml    = [];
        $rootCategory  = $this->getRootCategory();
        $storeId       = $this->_storeManager->getStore()->getId();
        $rootCatId     = $rootCategory->getId();
        $categories    = $this->getTreeMenu($storeId, $rootCatId);
        $contentCatTop = $this->getContentCatTop();

        foreach ($contentCatTop as $ext) {
            $this->extData[$ext->getCatId()] = $ext->getData();
        }
        $last = count($categories);
        $dropdownIds = explode(',', $this->_sysCfg->general['dropdown']);
        $counter = 1;
        $this->removeChildrenWithoutActiveParent($categories, 0);        
        foreach ($categories as $catTop){
            if(!$catTop->getData('is_parent_active')) continue;
            $parentPositionClass = '';
            $itemPositionClassPrefix = $parentPositionClass ? $parentPositionClass . '-' : 'nav-';
            $idTop    = $catTop->getEntityId();
            $urlTop      =  '<a class="level-top" href="' . $catTop->getUrl() . '">' .$this->getThumbnail($catTop). '<span>' . $catTop->getName() . $this->getCatLabel($catTop). '</span><span class="boder-menu"></span></a>';

            $itemPositionClassPrefixTop = $itemPositionClassPrefix . $counter;
            $classTop   = $itemPositionClassPrefixTop . ' ' . $this->_getActiveClasses($idTop);
            $isDropdown = in_array($idTop, $dropdownIds) ? ' dropdown' : '';
            // drawMainMenu
            $options  = '';
            if($isDropdown){
                $classTop .= $isDropdown;
                $catChild  = $catTop->getChildren();
                $childHtml = $this->getTreeCategories($catChild, $itemPositionClassPrefixTop); // include magic_label and Maximal Depth
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
                    $opt     = $this->serializer->serialize($data);
                    $options = $opt ? " data-options='$opt'" : '';
                }
                $menu = $this->getMegamenu($catTop, $blocks, $itemPositionClassPrefixTop);
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
                            $childTop  =  $catTop->getChildren();
                            $childLevel = $this->getChildLevel($catTop->getLevel());
                            $this->removeChildrenWithoutActiveParent($childTop, $childLevel);
                            $counter = 1;
                            foreach ($childTop as $cat) {
                                if(!$cat->getData('is_parent_active')) continue;
                                $itemPositionClassPrefixChild = $itemPositionClassPrefix . '-' . $counter;
                                $class = 'level1 category-item ' . $itemPositionClassPrefixChild . ' ' . $this->_getActiveClasses($cat->getEntityId());
                                $url =  '<a href="'. $cat->getUrl() .'"><span>' . $cat->getName() . $this->getCatLabel($cat) . '</span></a>';
                                $catChild  = $cat->getChildren();
                                $childHtml = $this->getTreeCategories($catChild, $itemPositionClassPrefixChild); // include magic_label and Maximal Depth
                                $desktopTmp .= '<li class="children ' . $class . '">' . $this->getImage($cat) . $url . $childHtml . '</li>';
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
                    if($link) $drawExtraMenu .= '<a class="level-top" href="' .$url. '"><span>' . $ext->getName() . $this->getCatLabel($ext). '</span></a>';
                    else $drawExtraMenu .= '<span class="level-top"><span>' . $ext->getName() . $this->getCatLabel($ext). '</span></span>';
                    if($html) $drawExtraMenu .= $html; //$drawExtraMenu .= '<div class="level-top-mega">'.$html.'</div>';
                $drawExtraMenu .= '</li>';
                $i++;
                $class = ($i == $count) ? 'last' : '';  
            }
        }
        $this->setData('extraMenu', $drawExtraMenu);
        return $drawExtraMenu;
    }

    public function getTreeMenu($storeId, $rootId)
    {
        $collection = $this->getCategoryTree($storeId, $rootId);
        $currentCategory = $this->getCurrentCategory();
        $mapping = [$rootId => $this->getMenu()];  // use nodes stack to avoid recursion
        foreach ($collection as $category) {
            $categoryParentId = $category->getParentId();
            if (!isset($mapping[$categoryParentId])) {
                $parentIds = $category->getParentIds();
                foreach ($parentIds as $parentId) {
                    if (isset($mapping[$parentId])) {
                        $categoryParentId = $parentId;
                    }
                }
            }

            /** @var Node $parentCategoryNode */
            $parentCategoryNode = $mapping[$categoryParentId];

            $categoryNode = new Node(
                $this->getCategoryAsArray(
                    $category,
                    $currentCategory,
                    $category->getParentId() == $categoryParentId
                ),
                'id',
                $parentCategoryNode->getTree(),
                $parentCategoryNode
            );
            $parentCategoryNode->addChild($categoryNode);

            $mapping[$category->getId()] = $categoryNode; //add node in stack
        }
        $menu = isset($mapping[$rootId]) ? $mapping[$rootId]->getChildren() : [];

        return $menu;
    }

    /**
     * Get menu object.
     *
     * Creates Tree root node object.
     * The creation logic was moved from class constructor into separate method.
     *
     * @return Node
     * @since 100.1.0
     */
    public function getMenu()
    {
        if (!$this->_menu) {
            $this->_menu = $this->nodeFactory->create(
                [
                    'data' => [],
                    'idField' => 'root',
                    'tree' => $this->treeFactory->create()
                ]
            );
        }
        return $this->_menu;
    }

    protected function getCategoryTree($storeId, $rootId)
    {
        /** @var \Magento\Catalog\Model\ResourceModel\Category\Collection $collection */
        $collection = $this->_categoryInstance->getCollection();
        $collection->setStoreId($storeId);
        $collection->addAttributeToSelect(['name', 'magic_label']);
        $collection->addFieldToFilter('path', ['like' => '1/' . $rootId . '/%']); //load only from store root
        $collection->addAttributeToFilter('include_in_menu', 1);
        $collection->addIsActiveFilter();
        $collection->addNavigationMaxDepthFilter();
        $collection->addUrlRewriteToResult();
        $collection->addOrder('level', 'ASC');
        $collection->addOrder('position', 'ASC');
        $collection->addOrder('parent_id', 'ASC');
        $collection->addOrder('entity_id', 'ASC');
        return $collection;
    }

    /**
     * Convert category to array
     *
     * @param \Magento\Catalog\Model\Category $category
     * @param \Magento\Catalog\Model\Category $currentCategory
     * @param bool $isParentActive
     * @return array
     */
    private function getCategoryAsArray($category, $currentCategory, $isParentActive)
    {
        $categoryId = $category->getId();
        return [
            'name' => $category->getName(),
            'id' => 'category-node-' . $categoryId,
            'url' => $this->_catalogCategory->getCategoryUrl($category),
            'has_active' => in_array((string)$categoryId, explode('/', (string)$currentCategory->getPath()), true),
            'is_active' => $categoryId == $currentCategory->getId(),
            'is_category' => true,
            'is_parent_active' => $isParentActive,
            'entity_id' => $categoryId,
            'level' => $category->getLevel(),
            'magic_label' => $category->getMagicLabel(),
        ];
    }

    /**
     * Remove children from collection when the parent is not active
     *
     * @param Collection $children
     * @param int $childLevel
     * @return void
     */
    private function removeChildrenWithoutActiveParent(Collection $children, int $childLevel): void
    {
        /** @var Node $child */
        foreach ($children as $child) {
            if ($childLevel === 0 && $child->getData('is_parent_active') === false) {
                $children->delete($child);
            }
        }
    }

    /**
     * Retrieve child level based on parent level
     *
     * @param int $parentLevel
     *
     * @return int
     */
    private function getChildLevel($parentLevel): int
    {
        return $parentLevel === null ? 0 : $parentLevel + 1;
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

    public function  getTreeCategories($categories, $itemPositionClassPrefix, $count='') // include Magic_Label and Maximal Depth
    {
        $html = '';
        $counter = 1;
        foreach($categories as $category) {
            if(!$category->getData('is_parent_active')) continue;
            if($count) {
                $cat = $this->_categoryInstance->load($category->getEntityId());
                $count = $count ? '(' . $cat->getProductCount() . ')' : '';                
            }
            $level = $category->getLevel();
            $catChild  = $category->getChildren();
            $childLevel = $this->getChildLevel($level);
            $this->removeChildrenWithoutActiveParent($catChild, $childLevel);
            $childHtml   = $this->getTreeCategories($catChild, $itemPositionClassPrefix);
            $childClass  = $childHtml ? ' hasChild parent ' : ' ';
            $childClass .= $itemPositionClassPrefix . '-' .$counter;
            $childClass .= ' category-item ' . $this->_getActiveClasses($category->getEntityId());
            $html .= '<li class="level' . ($level -2) . $childClass . '"><a href="' . $category->getUrl() . '"><span>' . $category->getName() . $count . $this->getCatLabel($category) . "</span></a>\n";
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
            if($lab) $html .= '<span class="cat_label '.$lab.'" rel='.__(trim($lab)).'></span>';
        }
        return $html;
    }

    public function getImage($category)
    {
        $url = '';
        $image = $this->_dirMedia . 'magiccart/magicmenu/images/' . $category->getEntityId() .'.png';
        if(file_exists($image)) $url = $this->_urlMedia . 'magiccart/magicmenu/images/' . $category->getEntityId() . '.png';
        if($url) return '<a class="a-image" href="' . $category->getUrl() . '"><img class="img-responsive" alt="' . $category->getName() . '" src="' . $url . '"></a>';
    }

    public function getThumbnail($category)
    {
        $url = '';
        $image = $this->_dirMedia . 'magiccart/magicmenu/thumbnail/' . $category->getEntityId() .'.png';
        if(file_exists($image)) $url = $this->_urlMedia . 'magiccart/magicmenu/thumbnail/' . $category->getEntityId() . '.png';
        if($url) return '<img class="img-responsive" alt="' . $category->getName() . '" src="' . $url . '">';
    }

}
