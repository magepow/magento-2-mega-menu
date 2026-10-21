<?php
/**
 * Copyright © Magiccart. All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Magiccart\Magicmenu\Model;

use Magento\Framework\Api\SearchResults;
use Magiccart\Magicmenu\Api\Data\MagicmenuSearchResultsInterface;

/**
 * Service data object with the magicmenu search results.
 */
class MagicmenuSearchResults extends SearchResults implements MagicmenuSearchResultsInterface
{
}
