<?php
/**
 * Copyright © Magiccart. All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Magiccart\Magicmenu\Model\System\Config;

use Magento\Cms\Model\ResourceModel\Block\CollectionFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Option\ArrayInterface;

class Blocks implements ArrayInterface
{
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var CollectionFactory
     */
    private $blockCollectionFactory;

    /**
     * @var array
     */
    private $options = [];

    public function __construct(
        RequestInterface $request,
        CollectionFactory $blockCollectionFactory
    ) {
        $this->request = $request;
        $this->blockCollectionFactory = $blockCollectionFactory;
    }

    /**
     * Retrieve the CMS static blocks as select options.
     *
     * @return array
     */
    public function toOptionArray(): array
    {
        if ($this->options) {
            return $this->options;
        }

        $store = $this->request->getParam('store');
        $collection = $this->blockCollectionFactory->create();
        $collection->addStoreFilter($store)->load();

        $options = [];
        foreach ($collection as $block) {
            $options[$block->getIdentifier()] = $block->getTitle();
        }
        array_unshift($options, ['value' => '', 'label' => __('Please select a static block.')]);

        $this->options = $options;

        return $this->options;
    }
}
