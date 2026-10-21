<?php
/**
 * Copyright © Magiccart. All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Magiccart\Magicmenu\Block\Adminhtml\Helper\Renderer\Grid;

class Category extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * Store manager.
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * block factory.
     *
     * @var \Magento\Cms\Model\BlockFactory
     */
    protected $_categoryFactory;

    /**
     * [__construct description].
     *
     * @param \Magento\Backend\Block\Context              $context
     * @param \Magento\Store\Model\StoreManagerInterface  $storeManager
     * @param \Magento\Cms\Model\BlockFactory $blockFactory
     * @param array                                       $data
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_storeManager = $storeManager;
        $this->_categoryFactory  = $categoryFactory;
    }

    /**
     * Render action.
     *
     * @param \Magento\Framework\DataObject $row
     *
     * @return string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $id = $row->getData($this->getColumn()->getIndex()); // $row->getCatId(); // $row->getData('cat_id');
        $category = $this->_categoryFactory->create()->load($id);
        $url = $this->getUrl('catalog/category/edit', ['id' => $id]);

        return '<a href="'.$url.'" alt="'.$url.'" >'. $category->getName() .'</a>';
    }
}
