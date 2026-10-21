<?php
/**
 * Copyright © Magiccart. All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Magiccart\Magicmenu\Model\System\Config;

use Magento\Framework\Option\ArrayInterface;

class Width implements ArrayInterface
{
    /**
     * Boxed width.
     */
    public const WIDTH_BOXED = 0;

    /**
     * Automatic width.
     */
    public const WIDTH_AUTO = 1;

    /**
     * Full width.
     */
    public const WIDTH_FULL = 2;

    /**
     * Get the available widths in "key-value" format.
     *
     * @return array
     */
    public static function getWidth(): array
    {
        return [
            self::WIDTH_AUTO => __('Auto'),
            self::WIDTH_BOXED => __('Boxed'),
            self::WIDTH_FULL => __('Full'),
        ];
    }

    /**
     * Get the available widths as select options.
     *
     * @return array
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => self::WIDTH_AUTO, 'label' => __('Auto')],
            ['value' => self::WIDTH_BOXED, 'label' => __('Boxed')],
            ['value' => self::WIDTH_FULL, 'label' => __('Full')],
        ];
    }

    /**
     * Get the available widths in "key-value" format.
     *
     * @return array
     */
    public function toArray(): array
    {
        return self::getWidth();
    }
}
