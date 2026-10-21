<?php
/**
 * Copyright © Magiccart. All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Magiccart\Magicmenu\Model;

class Status
{
    /**
     * Enabled status.
     */
    public const STATUS_ENABLED = 1;

    /**
     * Disabled status.
     */
    public const STATUS_DISABLED = 2;

    /**
     * Get the available statuses of a magicmenu.
     *
     * @return array
     */
    public static function getAvailableStatuses(): array
    {
        return [
            self::STATUS_ENABLED => __('Enabled'),
            self::STATUS_DISABLED => __('Disabled'),
        ];
    }
}
