<?php
/**
 * Magepow 
 * @category    Magepow 
 * @copyright   Copyright (c) 2014 Magiccart (https://www.magepow.com) 
 * @license     https://www.magepow.com/license-agreement.html
 * @Author: Magiccart<team.magiccart@gmail.com>
 * @@Create Date: 2022-11-1 19:10:30
 * @@Modify Date: 2022-11-1 22:16:38
 * @@Function:
 */

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
