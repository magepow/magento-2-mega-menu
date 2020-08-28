<?php

/**
 * @Author: nguyen
 * @Date:   2020-08-28 10:53:10
 * @Last Modified by:   nguyen
 * @Last Modified time: 2020-08-28 11:15:40
 */

namespace Magiccart\Magicmenu\Model\System\Config;


class Width implements \Magento\Framework\Option\ArrayInterface
{

    const WIDTH_BOXED = 0;
    const WIDTH_AUTO  = 1;
    const WIDTH_FULL  = 2;

    /**
     * get available statuses.
     *
     * @return []
     */
    public static function getWidth()
    {
        return [
            self::WIDTH_AUTO => __('Auto')
            , self::WIDTH_BOXED => __('Boxed')
            , self::WIDTH_FULL => __('Full')
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
            ['value' => self::WIDTH_AUTO, 'label' => __('Auto')], 
            ['value' => self::WIDTH_BOXED, 'label' => __('Boxed')],
            ['value' => self::WIDTH_FULL, 'label' => __('Full')]
        ];
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return self::getWidth();
    }
}

