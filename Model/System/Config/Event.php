<?php
/**
 * Copyright © Magiccart. All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Magiccart\Magicmenu\Model\System\Config;

use Magento\Framework\Option\ArrayInterface;

class Event implements ArrayInterface
{
    /**
     * Retrieve the available menu events.
     *
     * @return array
     */
    public function toOptionArray(): array
    {
        return [
            'click' => __('Click'),
            'hover' => __('Hover'),
        ];
    }
}
