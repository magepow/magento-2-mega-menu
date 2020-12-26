<?php

namespace Magiccart\Magicmenu\Model\Cache;

class Type extends \Magento\Framework\Cache\Frontend\Decorator\TagScope
{
/**
* Cache type code unique among all cache types
*/
const TYPE_IDENTIFIER = 'megamenu';

/**
* Cache tag used to distinguish the cache type from all other cache
*/
const CACHE_TAG = 'MEGAMENU';

/**
* @param \Magento\Framework\App\Cache\Type\FrontendPool $cacheFrontendPool
*/
public function __construct(\Magento\Framework\App\Cache\Type\FrontendPool $cacheFrontendPool)
{
parent::__construct($cacheFrontendPool->get(self::TYPE_IDENTIFIER), self::CACHE_TAG);
}

}