<?php

/**
 * @Author: nguyen
 * @Date:   2020-08-28 10:53:10
 * @Last Modified by:   nguyen
 * @Last Modified time: 2020-11-03 09:52:12
 */

namespace Magiccart\Magicmenu\Model\System\Config;


class Visibility implements \Magento\Framework\Option\ArrayInterface
{

    const BOTH = 0;
    const DESKTOP_ONLY  = 1;
    const MOBILE_ONLY  = 2;

    /**
     * get available statuses.
     *
     * @return []
     */
    public static function getVisibility()
    {
        return [
            self::BOTH => __('Show On Desktop / Mobile')
            , self::DESKTOP_ONLY => __('Desktop Only')
            , self::MOBILE_ONLY => __('Mobile Only')
        ];
    }

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::BOTH, 'label' => __('Show On Desktop / Mobile')],
            ['value' => self::DESKTOP_ONLY, 'label' => __('Desktop Only')],
            ['value' => self::MOBILE_ONLY, 'label' => __('Mobile Only')]
        ];
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return self::getVisibility();
    }
}

