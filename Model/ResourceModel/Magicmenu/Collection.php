<?php
/**
 * Copyright © Magiccart. All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Magiccart\Magicmenu\Model\ResourceModel\Magicmenu;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

    protected function _construct()
    {
        $this->_init('Magiccart\Magicmenu\Model\Magicmenu', 'Magiccart\Magicmenu\Model\ResourceModel\Magicmenu');
    }
}
