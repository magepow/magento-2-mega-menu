<?php
/**
 * Copyright © Magiccart. All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Magiccart\Magicmenu\Model\System\Config;

use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\Config\Source\Category as CategorySource;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Option\ArrayInterface;
use Magento\Store\Model\StoreManagerInterface;

class Category implements ArrayInterface
{
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var CategoryFactory
     */
    private $categoryFactory;

    /**
     * @var CategorySource
     */
    private $category;

    /**
     * @var array
     */
    private $options = [];

    public function __construct(
        RequestInterface $request,
        StoreManagerInterface $storeManager,
        CategoryFactory $categoryFactory,
        CategorySource $category
    ) {
        $this->request = $request;
        $this->storeManager = $storeManager;
        $this->categoryFactory = $categoryFactory;
        $this->category = $category;
    }

    /**
     * Retrieve the category tree as select options.
     *
     * @return array
     */
    public function toOptionArray(): array
    {
        if ($this->options) {
            return $this->options;
        }

        $store = $this->request->getParam('store');
        if (!$store) {
            $categories = $this->category->toOptionArray();
        } else {
            $rootCategoryId = $this->storeManager->getStore($store)->getRootCategoryId();
            $label = $this->categoryFactory->create()->load($rootCategoryId)->getName();
            $categories = [['value' => $rootCategoryId, 'label' => $label]];
        }

        $options = [];
        foreach ($categories as $category) {
            if (empty($category['value'])) {
                continue;
            }
            $rootOption = ['label' => $category['label']];
            $childOptions = [];
            foreach ($this->categoryFactory->create()->getCategories($category['value']) as $childCategory) {
                $childOptions[] = [
                    'label' => $childCategory->getName(),
                    'value' => $childCategory->getId(),
                ];
            }
            $rootOption['value'] = $childOptions;
            $options[] = $rootOption;
        }

        $this->options = $options;

        return $this->options;
    }
}
