<?php
/**
 * Copyright © Magiccart. All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Magiccart\Magicmenu\Model\System\Config;

class Event implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        return [
            'click'=>   __('Click'),
            'hover'=>   __('Hover'),
        ];
    }
}
