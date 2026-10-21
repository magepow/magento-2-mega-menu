<?php
/**
 * Copyright © Magiccart. All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Magiccart\Magicmenu\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\Module\Manager;
use Magiccart\Magicmenu\Model\Config;

/**
 * @deprecated Use \Magiccart\Magicmenu\Model\Config instead.
 */
class Data extends AbstractHelper
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var Manager
     */
    private $moduleManager;

    /**
     * @var array
     */
    private $configModule = [];

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        Manager $moduleManager,
        Config $config
    ) {
        parent::__construct($context);
        $this->moduleManager = $moduleManager;
        $this->config = $config;
        $this->configModule = $this->config->getModuleConfig();
    }

    /**
     * Retrieve a configuration value.
     *
     * @param string $cfg
     * @return mixed
     */
    public function getConfig($cfg = '')
    {
        if ($cfg) {
            return $this->config->getScopeValue($cfg);
        }

        return $this->config->getModuleConfig();
    }

    /**
     * Retrieve a nested value of the module configuration.
     *
     * @param string $cfg
     * @param mixed $value
     * @return mixed
     */
    public function getConfigModule($cfg = '', $value = null)
    {
        $values = $this->configModule;
        if (!$cfg) {
            return $values;
        }

        $config = explode('/', (string)$cfg);
        $end = count($config) - 1;
        foreach ($config as $key => $item) {
            if (isset($values[$item])) {
                if ($key == $end) {
                    $value = $values[$item];
                } else {
                    $values = $values[$item];
                }
            }
        }

        return $value;
    }

    /**
     * Check whether a module is enabled.
     *
     * @param string $moduleName
     * @return bool
     */
    public function isEnabledModule($moduleName)
    {
        return $this->moduleManager->isEnabled($moduleName);
    }
}
