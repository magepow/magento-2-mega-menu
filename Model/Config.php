<?php
/**
 * Copyright © Magiccart. All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Magiccart\Magicmenu\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Configuration reader of the Magiccart_Magicmenu module.
 */
class Config
{
    /**
     * Configuration section of the module.
     */
    public const SECTION = 'magicmenu';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Retrieve a single configuration value of the module section.
     *
     * @param string $path Relative path inside the module section, e.g. "topmenu/enabled"
     * @return mixed
     */
    public function getValue(string $path)
    {
        if ($path === '') {
            return $this->getModuleConfig();
        }

        return $this->scopeConfig->getValue(
            self::SECTION . '/' . $path,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Retrieve a configuration value by its absolute configuration path.
     *
     * @param string $path Absolute configuration path, e.g. "web/url/use_store"
     * @return mixed
     */
    public function getScopeValue(string $path)
    {
        return $this->scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE);
    }

    /**
     * Retrieve the whole module configuration.
     *
     * @return array
     */
    public function getModuleConfig(): array
    {
        $config = $this->scopeConfig->getValue(self::SECTION, ScopeInterface::SCOPE_STORE);

        return is_array($config) ? $config : [];
    }

    /**
     * Check whether a boolean configuration flag is enabled.
     *
     * @param string $path Relative path inside the module section
     * @return bool
     */
    public function isEnabled(string $path): bool
    {
        return (bool)$this->getValue($path);
    }
}
