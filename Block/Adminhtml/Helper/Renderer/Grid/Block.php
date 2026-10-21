<?php
/**
 * Copyright © Magiccart. All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Magiccart\Magicmenu\Block\Adminhtml\Helper\Renderer\Grid;

use Magento\Backend\Block\Context;
use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Cms\Api\Data\BlockInterface;
use Magento\Cms\Api\GetBlockByIdentifierInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;

class Block extends AbstractRenderer
{
    /**
     * @var GetBlockByIdentifierInterface
     */
    private $blockByIdentifier;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    public function __construct(
        Context $context,
        GetBlockByIdentifierInterface $blockByIdentifier,
        StoreManagerInterface $storeManager,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->blockByIdentifier = $blockByIdentifier;
        $this->storeManager = $storeManager;
    }

    /**
     * Render the CMS block title of the current row.
     *
     * @param DataObject $row
     * @return string
     */
    public function render(DataObject $row)
    {
        $identifier = $row->getData($this->getColumn()->getIndex());

        if (!$identifier) {
            return '';
        }

        try {
            /** @var BlockInterface $block */
            $block = $this->blockByIdentifier->execute(
                (string)$identifier,
                (int)$this->storeManager->getStore()->getId()
            );
        } catch (NoSuchEntityException $exception) {
            return '';
        }

        $url = $this->getUrl('cms/block/edit', ['block_id' => $block->getId()]);

        return '<a href="' . $this->_escaper->escapeUrl($url) . '" title="'
            . $this->_escaper->escapeHtmlAttr($url) . '">'
            . $this->_escaper->escapeHtml($block->getTitle()) . '</a>';
    }
}
