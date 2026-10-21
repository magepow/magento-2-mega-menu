<?php
/**
 * Copyright © Magiccart. All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Magiccart\Magicmenu\Block\Adminhtml\Helper\Renderer\Grid;

use Magento\Backend\Block\Context;
use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Framework\DataObject;

class Category extends AbstractRenderer
{
    /**
     * @var CategoryFactory
     */
    private $categoryFactory;

    public function __construct(
        Context $context,
        CategoryFactory $categoryFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->categoryFactory = $categoryFactory;
    }

    /**
     * Render the category name of the current row.
     *
     * @param DataObject $row
     * @return string
     */
    public function render(DataObject $row)
    {
        $categoryId = $row->getData($this->getColumn()->getIndex());

        if (!$categoryId) {
            return '';
        }

        $category = $this->categoryFactory->create()->load($categoryId);
        $url = $this->getUrl('catalog/category/edit', ['id' => $categoryId]);

        return '<a href="' . $this->_escaper->escapeUrl($url) . '" title="'
            . $this->_escaper->escapeHtmlAttr($url) . '">'
            . $this->_escaper->escapeHtml($category->getName()) . '</a>';
    }
}
