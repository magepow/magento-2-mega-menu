<?php
/**
 * Copyright © Magiccart. All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Magiccart\Magicmenu\Block;

use Magento\Catalog\Block\Navigation;
use Magento\Catalog\Helper\Category as CatalogCategory;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\Indexer\Category\Flat\State as FlatState;
use Magento\Catalog\Model\Layer\Resolver;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Cms\Block\BlockByIdentifierFactory as CmsBlockFactory;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\Data\Tree\Node;
use Magento\Framework\Data\Tree\Node\Collection;
use Magento\Framework\Data\Tree\NodeFactory;
use Magento\Framework\Data\TreeFactory;
use Magento\Framework\Filesystem\Driver\File as FileDriver;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\UrlInterface;
use Magento\Store\Block\SwitcherFactory;
use Magiccart\Magicmenu\Model\Config as MagicmenuConfig;
use Magiccart\Magicmenu\Model\ResourceModel\Magicmenu\CollectionFactory as MagicmenuCollectionFactory;

class Menu extends Navigation
{
    /**
     * Cache tag of the rendered menu.
     */
    public const DEFAULT_CACHE_TAG = 'MAGICCART_MAGICMENU';

    /**
     * @var Json
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
     * @var MagicmenuCollectionFactory
     */
    protected $magicmenuCollectionFactory;

    /**
     * @var string
     */
    protected $urlMedia;

    /**
     * @var string
     */
    protected $dirMedia;

    /**
     * @var array
     */
    protected $extData = [];

    /**
     * @var object
     */
    public $sysCfg;

    /**
     * @var MagicmenuConfig
     */
    public $config;

    /**
     * @var \Magento\Catalog\Model\Category
     */
    public $rootCategory;

    /**
     * @var CmsBlockFactory
     */
    private $cmsBlockFactory;

    /**
     * @var FileDriver
     */
    private $fileDriver;

    /**
     * @var SwitcherFactory
     */
    private $switcherFactory;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        CategoryFactory $categoryFactory,
        ProductCollectionFactory $productCollectionFactory,
        Resolver $layerResolver,
        HttpContext $httpContext,
        CatalogCategory $catalogCategory,
        Registry $registry,
        FlatState $flatState,
        MagicmenuConfig $config,
        MagicmenuCollectionFactory $magicmenuCollectionFactory,
        NodeFactory $nodeFactory,
        TreeFactory $treeFactory,
        Json $serializer,
        CmsBlockFactory $cmsBlockFactory,
        FileDriver $fileDriver,
        SwitcherFactory $switcherFactory,
        array $data = []
    ) {
        $this->config = $config;
        $this->magicmenuCollectionFactory = $magicmenuCollectionFactory;
        $this->nodeFactory = $nodeFactory;
        $this->treeFactory = $treeFactory;
        $this->serializer = $serializer;
        $this->cmsBlockFactory = $cmsBlockFactory;
        $this->fileDriver = $fileDriver;
        $this->switcherFactory = $switcherFactory;

        $configModule = $this->config->getModuleConfig();
        $configModule['topmenu']['notIncludeNav'] = $configModule['topmenu']['notIncludeNav'] ?? '';
        $configModule['vmenu']['notIncludeNav'] = $configModule['vmenu']['notIncludeNav'] ?? '';
        $this->sysCfg = (object)$configModule;

        parent::__construct(
            $context,
            $categoryFactory,
            $productCollectionFactory,
            $layerResolver,
            $httpContext,
            $catalogCategory,
            $registry,
            $flatState,
            $data
        );

        $this->dirMedia = $this->getMediaDirectory()->getAbsolutePath();
        $this->urlMedia = $this->_storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);
    }

    /**
     * @return int
     */
    protected function getCacheLifetime()
    {
        return parent::getCacheLifetime() ?: 86400;
    }

    /**
     * @return array
     */
    public function getCacheKeyInfo()
    {
        $keyInfo = parent::getCacheKeyInfo();
        $keyInfo[] = $this->getCurrentCategory()->getId();

        return $keyInfo;
    }

    /**
     * @return array
     */
    public function getIdentities()
    {
        return [
            self::DEFAULT_CACHE_TAG,
            self::DEFAULT_CACHE_TAG . '_' . $this->getCurrentCategory()->getId()
        ];
    }

    /**
     * @return bool
     */
    public function getIsHomePage()
    {
        return $this->getUrl('') == $this->getUrl('*/*/*', ['_current' => true, '_use_rewrite' => true]);
    }

    /**
     * @param int|string $catId
     * @return bool
     */
    public function isCategoryActive($catId)
    {
        return $this->getCurrentCategory()
            ? in_array($catId, $this->getCurrentCategory()->getPathIds())
            : false;
    }

    /**
     * @param int|string $catId
     * @return bool
     */
    public function isHasActive($catId)
    {
        return ($catId != $this->getCurrentCategory()->getId()) ? true : false;
    }

    /**
     * @param int|string $catId
     * @return string
     */
    protected function _getActiveClasses($catId)
    {
        $classes = '';
        if ($this->isCategoryActive($catId)) {
            if ($this->isHasActive($catId)) {
                $classes = 'has-active active';
            } else {
                $classes = 'active';
            }
        }

        return $classes;
    }

    /**
     * @return \Magento\Catalog\Model\Category
     */
    public function getRootCategory()
    {
        if (!$this->rootCategory) {
            $rootCatId = $this->_storeManager->getStore()->getRootCategoryId();
            $this->rootCategory = $this->_categoryInstance->load($rootCatId);
        }

        return $this->rootCategory;
    }

    /**
     * @return string
     */
    public function getRootName()
    {
        return $this->getRootCategory()->getName();
    }

    /**
     * @return string
     */
    public function drawHomeMenu()
    {
        if ($this->hasData('homeMenu')) {
            return $this->getData('homeMenu');
        }
        $drawHomeMenu = '';
        $drawDemoMenu = '';

        if ($this->sysCfg->topmenu['demo']) {
            $demos = [];
            $currentStore = $this->_storeManager->getStore();
            $switcher = $this->switcherFactory->create();
            foreach ($this->_storeManager->getWebsites() as $website) {
                $groups = $website->getGroups();
                if (count($groups) > 1) {
                    foreach ($groups as $group) {
                        $store = $group->getDefaultStore();
                        if ($store && !$store->getIsActive()) {
                            $stores = $group->getStores();
                            foreach ($stores as $activeStore) {
                                if ($activeStore->getIsActive()) {
                                    $store = $activeStore;
                                    break;
                                }
                            }
                        }
                        if ($store) {
                            if ($store->getCode() == $currentStore->getCode()
                                || $this->config->getScopeValue('web/url/use_store')
                            ) {
                                $demos[] = '<li class="level1"><a href="' . $this->_escaper->escapeUrl(
                                    $store->getBaseUrl()
                                ) . '"><span class="demo-home">' . $this->_escaper->escapeHtml(
                                    $group->getName()
                                ) . '</span></a></li>';
                            } else {
                                $dataPost = $switcher->getTargetStorePostData($store);
                                $dataPost = $this->serializer->unserialize($dataPost);
                                if (isset($dataPost['action']) && isset($dataPost['data'])) {
                                    $href = $dataPost['action'] . '?' . http_build_query($dataPost['data']);
                                    $demos[] = '<li class="switcher-option level1"><a href="'
                                        . $this->_escaper->escapeUrl($href) . '"><span class="demo-home">'
                                        . $this->_escaper->escapeHtml($group->getName())
                                        . '</span></a></li>';
                                }
                            }
                        }
                    }
                }
            }
            if (!empty($demos)) {
                natsort($demos);
                $drawDemoMenu = '<ul class="level0 category-item submenu">' . implode(' ', $demos) . '</ul>';
            }
        }

        $active = ($this->getIsHomePage()) ? ' active' : '';
        $drawHomeMenu .= '<li class="level0 category-item level-top dropdown home' . $active
            . ($drawDemoMenu ? ' hasChild' : '') . '">';
        $drawHomeMenu .= '<a class="level-top" href="' . $this->_escaper->escapeUrl($this->getBaseUrl())
            . '"><span class="icon fa fa-home"></span><span class="icon-text">'
            . $this->_escaper->escapeHtml(__('Home')) . '</span>';
        $drawHomeMenu .= '</a>';
        $drawHomeMenu .= $drawDemoMenu;
        $drawHomeMenu .= '</li>';
        $this->setData('homeMenu', $drawHomeMenu);

        return $drawHomeMenu;
    }

    /**
     * @return array
     */
    public function drawMainMenu()
    {
        if ($this->hasData('mainMenu')) {
            return $this->getData('mainMenu');
        }

        $desktopHtml = [];
        $mobileHtml = [];
        $rootCategory = $this->getRootCategory();
        $storeId = $this->_storeManager->getStore()->getId();
        $rootCatId = $rootCategory->getId();
        $categories = $this->getTreeMenu($storeId, $rootCatId);
        $contentCatTop = $this->getContentCatTop();

        foreach ($contentCatTop as $ext) {
            if ($ext->getCatId()) {
                $this->extData[$ext->getCatId()] = $ext->getData();
            }
        }
        $dropdownIds = explode(',', (string)$this->sysCfg->general['dropdown']);
        $counter = 1;
        $this->removeChildrenWithoutActiveParent($categories, 0);

        foreach ($categories as $catTop) {
            if (!$catTop->getData('is_parent_active')) {
                continue;
            }
            $parentPositionClass = '';
            $itemPositionClassPrefix = $parentPositionClass ? $parentPositionClass . '-' : 'nav-';
            $idTop = $catTop->getEntityId();
            $urlTop = '<a class="level-top" href="' . $this->_escaper->escapeUrl($catTop->getUrl()) . '">'
                . $this->getThumbnail($catTop)
                . '<span>' . $this->_escaper->escapeHtml($catTop->getName()) . $this->getCatLabel($catTop)
                . '</span><span class="boder-menu"></span></a>';

            $itemPositionClassPrefixTop = $itemPositionClassPrefix . $counter;
            $classTop = $itemPositionClassPrefixTop . ' ' . $this->_getActiveClasses($idTop);
            $isDropdown = in_array($idTop, $dropdownIds) ? ' dropdown' : '';

            $options = '';
            if ($isDropdown) {
                $classTop .= $isDropdown;
                $catChild = $catTop->getChildren();
                $childHtml = $this->getTreeCategories($catChild, $itemPositionClassPrefixTop);
                $menu = ['desktop' => $childHtml, 'mobile' => $childHtml];
            } else {
                $data = $this->extData[$idTop] ?? '';
                $blocks = ['top' => '', 'left' => '', 'right' => '', 'bottom' => ''];
                if ($data) {
                    foreach ($blocks as $key => $value) {
                        $html = $this->getStaticBlock($data[$key]);
                        if ($html) {
                            $blocks[$key] = "<div class='mage-column mega-block-" . $this->_escaper->escapeHtmlAttr($key)
                                . "'>" . $html . '</div>';
                        }
                    }
                    $remove = ['top' => '', 'left' => '', 'right' => '', 'bottom' => '', 'cat_id' => ''];
                    foreach ($remove as $key => $value) {
                        unset($data[$key]);
                    }
                    $opt = $this->serializer->serialize($data);
                    $options = $opt ? " data-options='" . $this->_escaper->escapeHtmlAttr($opt) . "'" : '';
                }
                $menu = $this->getMegamenu($catTop, $blocks, $itemPositionClassPrefixTop);
            }

            if ($menu['desktop']) {
                $classTop .= ' hasChild parent';
            }

            $desktopHtml[$idTop] = '<li class="level0 category-item level-top cat ' . $classTop . '"' . $options . '>'
                . $urlTop . $menu['desktop'] . '</li>';
            $mobileHtml[$idTop] = '<li class="level0 category-item level-top cat ' . $classTop . '">'
                . $urlTop . $menu['mobile'] . '</li>';
            $counter++;
        }

        $menu = [];
        $menu['desktop'] = $desktopHtml;
        $menu['mobile'] = implode("\n", $mobileHtml);
        $this->setData('mainMenu', $menu);

        return $menu;
    }

    /**
     * @param Node $catTop
     * @param array $blocks
     * @param string $itemPositionClassPrefix
     * @return array
     */
    public function getMegamenu($catTop, $blocks, $itemPositionClassPrefix)
    {
        $hasChild = $catTop->hasChildren();
        $desktopTmp = '';
        $mobileTmp = '';

        if ($hasChild || $blocks['top'] || $blocks['left'] || $blocks['right'] || $blocks['bottom']) {
            $desktopTmp .= '<div class="level-top-mega">';
            $desktopTmp .= '<div class="content-mega">';
            $desktopTmp .= $blocks['top'];
            $desktopTmp .= '<div class="content-mega-horizontal">';
            $desktopTmp .= $blocks['left'];
            if ($hasChild) {
                $desktopTmp .= '<ul class="level0 category-item mage-column cat-mega">';
                $mobileTmp .= '<ul class="submenu">';
                $childTop = $catTop->getChildren();
                $childLevel = $this->getChildLevel($catTop->getLevel());
                $this->removeChildrenWithoutActiveParent($childTop, $childLevel);
                $counter = 1;
                foreach ($childTop as $cat) {
                    if (!$cat->getData('is_parent_active')) {
                        continue;
                    }
                    $itemPositionClassPrefixChild = $itemPositionClassPrefix . '-' . $counter;
                    $class = 'level1 category-item ' . $itemPositionClassPrefixChild . ' '
                        . $this->_getActiveClasses($cat->getEntityId());
                    $url = '<a href="' . $this->_escaper->escapeUrl($cat->getUrl()) . '"><span>'
                        . $this->_escaper->escapeHtml($cat->getName()) . $this->getCatLabel($cat) . '</span></a>';
                    $catChild = $cat->getChildren();
                    $childHtml = $this->getTreeCategories($catChild, $itemPositionClassPrefixChild);
                    $desktopTmp .= '<li class="children ' . $class . '">' . $this->getImage($cat) . $url
                        . $childHtml . '</li>';
                    $mobileTmp .= '<li class="' . $class . '">' . $url . $childHtml . '</li>';
                    $counter++;
                }
                $desktopTmp .= '</ul>';
                $mobileTmp .= '</ul>';
            }
            $desktopTmp .= $blocks['right'];
            $desktopTmp .= '</div>';
            $desktopTmp .= $blocks['bottom'];
            $desktopTmp .= '</div>';
            $desktopTmp .= '</div>';
        }

        return ['desktop' => $desktopTmp, 'mobile' => $mobileTmp];
    }

    /**
     * @return string
     */
    public function drawExtraMenu()
    {
        if ($this->hasData('extraMenu')) {
            return $this->getData('extraMenu');
        }
        $extMenu = $this->getExtraMenu();
        $count = count($extMenu);
        $drawExtraMenu = '';

        if ($count) {
            $i = 1;
            $class = 'first';
            $currentUrl = $this->getUrl('*/*/*', ['_current' => true, '_use_rewrite' => true]);
            foreach ($extMenu as $ext) {
                $link = $ext->getLink();
                $url = (filter_var($link, FILTER_VALIDATE_URL)) ? $link : $this->getUrl('', ['_direct' => $link]);
                $active = ($link && $url == $currentUrl) ? ' active' : '';
                $html = $this->getStaticBlock($ext->getExtContent());
                $class .= $ext->getCatCol() ? ' ' . $ext->getCatCol() : ' dropdown';
                if ($html) {
                    $active .= ' hasChild parent';
                }
                $drawExtraMenu .= "<li class='level0 category-item level-top ext " . $this->_escaper->escapeHtmlAttr(
                    $active . ' ' . $class
                ) . "'>";
                if ($link) {
                    $drawExtraMenu .= '<a class="level-top" href="' . $this->_escaper->escapeUrl($url) . '"><span>'
                        . $this->_escaper->escapeHtml($ext->getName()) . $this->getCatLabel($ext) . '</span></a>';
                } else {
                    $drawExtraMenu .= '<span class="level-top"><span>' . $this->_escaper->escapeHtml($ext->getName())
                        . $this->getCatLabel($ext) . '</span></span>';
                }
                $drawExtraMenu .= $html;
                $drawExtraMenu .= '</li>';
                $i++;
                $class = ($i == $count) ? 'last' : '';
            }
        }
        $this->setData('extraMenu', $drawExtraMenu);

        return $drawExtraMenu;
    }

    /**
     * @param int $storeId
     * @param int $rootId
     * @return Collection
     */
    public function getTreeMenu($storeId, $rootId)
    {
        $collection = $this->getCategoryTree($storeId, $rootId);
        $currentCategory = $this->getCurrentCategory();
        $mapping = [$rootId => $this->getMenu()];

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

            $mapping[$category->getId()] = $categoryNode;
        }

        return isset($mapping[$rootId]) ? $mapping[$rootId]->getChildren() : [];
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

    /**
     * @param int $storeId
     * @param int $rootId
     * @return \Magento\Catalog\Model\ResourceModel\Category\Collection
     */
    protected function getCategoryTree($storeId, $rootId)
    {
        /** @var \Magento\Catalog\Model\ResourceModel\Category\Collection $collection */
        $collection = $this->_categoryInstance->getCollection();
        $collection->setStoreId($storeId);
        $collection->addAttributeToSelect(['name', 'magic_label']);
        $collection->addFieldToFilter('path', ['like' => '1/' . $rootId . '/%']);
        $collection->addFieldToFilter('include_in_menu', 1);
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
     * @return int
     */
    private function getChildLevel($parentLevel): int
    {
        return $parentLevel === null ? 0 : $parentLevel + 1;
    }

    /**
     * @return \Magento\Catalog\Model\ResourceModel\Magicmenu\Collection
     */
    public function getExtraMenu()
    {
        $store = $this->_storeManager->getStore()->getStoreId();
        $collection = $this->magicmenuCollectionFactory->create()
            ->addFieldToSelect(['link', 'name', 'cat_col', 'magic_label', 'ext_content', 'order'])
            ->addFieldToFilter('extra', 1)
            ->addFieldToFilter('status', 1);
        $collection->getSelect()
            ->where('find_in_set(0, stores) OR find_in_set(?, stores)', $store)
            ->order('order');

        return $collection;
    }

    /**
     * Render the CMS static block by its identifier.
     *
     * @param string|null $identifier
     * @return string
     */
    public function getStaticBlock($identifier)
    {
        if (!$identifier) {
            return '';
        }

        return $this->cmsBlockFactory->create()
            ->setData('identifier', (string)$identifier)
            ->toHtml();
    }

    /**
     * @return \Magento\Catalog\Model\ResourceModel\Magicmenu\Collection
     */
    public function getContentCatTop()
    {
        $store = $this->_storeManager->getStore()->getStoreId();
        $collection = $this->magicmenuCollectionFactory->create()
            ->addFieldToSelect(
                [
                    'cat_id',
                    'cat_col',
                    'cat_proportion',
                    'top',
                    'right',
                    'right_proportion',
                    'bottom',
                    'left',
                    'left_proportion'
                ]
            )
            ->addFieldToFilter('stores', [['finset' => 0], ['finset' => $store]])
            ->addFieldToFilter('status', 1);

        return $collection;
    }

    /**
     * Render the category tree.
     *
     * @param Collection $categories
     * @param string $itemPositionClassPrefix
     * @param string $count
     * @return string
     */
    public function getTreeCategories($categories, $itemPositionClassPrefix, $count = '')
    {
        $html = '';
        $counter = 1;
        $level = 0;

        foreach ($categories as $category) {
            if (!$category->getData('is_parent_active')) {
                continue;
            }
            if ($count) {
                $cat = $this->_categoryInstance->load($category->getEntityId());
                $count = $count ? '(' . $cat->getProductCount() . ')' : '';
            }
            $level = $category->getLevel();
            $catChild = $category->getChildren();
            $childLevel = $this->getChildLevel($level);
            $this->removeChildrenWithoutActiveParent($catChild, $childLevel);
            $childHtml = $this->getTreeCategories($catChild, $itemPositionClassPrefix);
            $childClass = $childHtml ? ' hasChild parent ' : ' ';
            $childClass .= $itemPositionClassPrefix . '-' . $counter;
            $childClass .= ' category-item ' . $this->_getActiveClasses($category->getEntityId());
            $html .= '<li class="level' . ($level - 2) . $childClass . '"><a href="'
                . $this->_escaper->escapeUrl($category->getUrl()) . '"><span>'
                . $this->_escaper->escapeHtml($category->getName()) . $count . $this->getCatLabel($category)
                . '</span></a>' . "\n";
            $html .= $childHtml;
            $html .= '</li>';
            $counter++;
        }
        if ($html) {
            $html = '<ul class="level' . ($level - 3) . ' submenu">' . $html . '</ul>';
        }

        return $html;
    }

    /**
     * @param \Magento\Framework\DataObject $cat
     * @return string
     */
    public function getCatLabel($cat)
    {
        $html = '';
        $label = explode(',', (string)$cat->getMagicLabel());
        foreach ($label as $lab) {
            $lab = trim($lab);
            if ($lab) {
                $html .= '<span class="cat_label ' . $this->_escaper->escapeHtmlAttr($lab) . '" rel="'
                    . $this->_escaper->escapeHtmlAttr(__($lab)) . '"></span>';
            }
        }

        return $html;
    }

    /**
     * @param \Magento\Catalog\Model\Category $category
     * @return string
     */
    public function getImage($category)
    {
        $url = $this->getCategoryImageUrl($category, 'images');

        if ($url) {
            return '<a class="a-image" href="' . $this->_escaper->escapeUrl($category->getUrl()) . '">'
                . '<img class="img-responsive" alt="' . $this->_escaper->escapeHtmlAttr($category->getName())
                . '" src="' . $this->_escaper->escapeUrl($url) . '"></a>';
        }

        return '';
    }

    /**
     * @param \Magento\Catalog\Model\Category $category
     * @return string
     */
    public function getThumbnail($category)
    {
        $url = $this->getCategoryImageUrl($category, 'thumbnail');

        if ($url) {
            return '<img class="img-responsive" alt="' . $this->_escaper->escapeHtmlAttr($category->getName())
                . '" src="' . $this->_escaper->escapeUrl($url) . '">';
        }

        return '';
    }

    /**
     * Resolve the media url of a category image if the file exists.
     *
     * @param \Magento\Catalog\Model\Category $category
     * @param string $type
     * @return string
     */
    private function getCategoryImageUrl($category, $type)
    {
        $relativePath = 'magiccart/magicmenu/' . $type . '/' . $category->getEntityId() . '.png';
        $absolutePath = $this->dirMedia . $relativePath;

        if (!$this->fileDriver->isExists($absolutePath)) {
            return '';
        }

        return $this->urlMedia . $relativePath;
    }
}
