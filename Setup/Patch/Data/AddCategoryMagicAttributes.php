<?php
/**
 * Copyright © Magiccart. All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Magiccart\Magicmenu\Setup\Patch\Data;

use Magento\Catalog\Setup\CategorySetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class AddCategoryMagicAttributes implements DataPatchInterface
{
    private CategorySetupFactory $categorySetupFactory;
    private ModuleDataSetupInterface $moduleDataSetup;

    public function __construct(
        CategorySetupFactory $categorySetupFactory,
        ModuleDataSetupInterface $moduleDataSetup
    ) {
        $this->categorySetupFactory = $categorySetupFactory;
        $this->moduleDataSetup = $moduleDataSetup;
    }

    public function apply(): void
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        $categorySetup = $this->categorySetupFactory->create(['setup' => $this->moduleDataSetup]);

        $attributes = [
            'magic_label' => [
                'group' => 'Magiccart',
                'label' => 'Label',
                'input' => 'text',
                'type' => 'varchar',
                'required' => false,
                'frontend_class' => 'validate-length maximum-length-255',
                'note' => 'Example: New,Hot,... Maximum 255 chars',
                'sort_order' => 1,
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
            ],
            'magic_thumbnail' => [
                'group' => 'Magiccart',
                'label' => 'Thumbnail',
                'input' => 'image',
                'type' => 'varchar',
                'backend' => 'Magento\Catalog\Model\Category\Attribute\Backend\Image',
                'required' => false,
                'sort_order' => 2,
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
            ],
            'magic_image' => [
                'group' => 'Magiccart',
                'label' => 'Image',
                'input' => 'image',
                'type' => 'varchar',
                'backend' => 'Magento\Catalog\Model\Category\Attribute\Backend\Image',
                'required' => false,
                'sort_order' => 3,
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
            ],
        ];

        foreach ($attributes as $code => $data) {
            if (!$categorySetup->getAttribute(\Magento\Catalog\Model\Category::ENTITY, $code)) {
                $categorySetup->addAttribute(\Magento\Catalog\Model\Category::ENTITY, $code, $data);
            }
        }

        $this->moduleDataSetup->getConnection()->endSetup();
    }

    public static function getDependencies(): array
    {
        return [];
    }

    public function getAliases(): array
    {
        return [];
    }
}