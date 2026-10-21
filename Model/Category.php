<?php
/**
 * Copyright © Magiccart. All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Magiccart\Magicmenu\Model;

class Category extends \Magento\Catalog\Model\Category
{
	protected function _construct()
    {
        $this->_init('catalog/category');
    }
}
