<?php
/**
 * Copyright © Magiccart. All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Magiccart\Magicmenu\Model\System\Config;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Extra menu visibility options.
 */
class Visibility implements OptionSourceInterface
{
    /**
     * Show the extra menu on both desktop and mobile.
     */
    public const BOTH = 0;

    /**
     * Show the extra menu on desktop only.
     */
    public const DESKTOP_ONLY = 1;

    /**
     * Show the extra menu on mobile only.
     */
    public const MOBILE_ONLY = 2;

    /**
     * Get the available visibility options.
     *
     * @return array<int, \Magento\Framework\Phrase>
     */
    public static function getVisibility(): array
    {
        return [
            self::BOTH => __('Show On Desktop / Mobile'),
            self::DESKTOP_ONLY => __('Desktop Only'),
            self::MOBILE_ONLY => __('Mobile Only'),
        ];
    }

    /**
     * Get the supported visibility values.
     *
     * @return int[]
     */
    public static function getValues(): array
    {
        return [self::BOTH, self::DESKTOP_ONLY, self::MOBILE_ONLY];
    }

    /**
     * Check whether the given value is a supported visibility option.
     *
     * @param mixed $visibility
     * @return bool
     */
    public static function isValid($visibility): bool
    {
        return is_numeric($visibility) && in_array((int)$visibility, self::getValues(), true);
    }

    /**
     * Normalize an arbitrary value to a supported visibility option.
     *
     * @param mixed $visibility
     * @return int
     */
    public static function normalize($visibility): int
    {
        return self::isValid($visibility) ? (int)$visibility : self::BOTH;
    }

    /**
     * Check whether an item belongs to the given viewport.
     *
     * An item that is enabled for both viewports always belongs to a viewport.
     *
     * @param mixed $itemVisibility
     * @param int|string $visibility
     * @return bool
     */
    public static function matches($itemVisibility, $visibility): bool
    {
        $itemVisibility = self::normalize($itemVisibility);
        $visibility = self::normalize($visibility);

        return $itemVisibility === self::BOTH || $itemVisibility === $visibility;
    }

    /**
     * Options getter.
     *
     * @return array<int, array{value: int, label: \Magento\Framework\Phrase}>
     */
    public function toOptionArray(): array
    {
        $options = [];

        foreach (self::getVisibility() as $value => $label) {
            $options[] = ['value' => $value, 'label' => $label];
        }

        return $options;
    }
}
