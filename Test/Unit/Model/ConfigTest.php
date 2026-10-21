<?php
/**
 * Copyright © Magiccart. All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Magiccart\Magicmenu\Test\Unit\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magiccart\Magicmenu\Model\Config;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    /**
     * @var ScopeConfigInterface|MockObject
     */
    private $scopeConfig;

    /**
     * @var Config
     */
    private $config;

    protected function setUp(): void
    {
        $this->scopeConfig = $this->getMockForAbstractClass(ScopeConfigInterface::class);

        $this->config = new Config($this->scopeConfig);
    }

    public function testGetValueReturnsModuleScopedValue(): void
    {
        $this->scopeConfig->expects($this->once())
            ->method('getValue')
            ->with('magicmenu/topmenu/enabled', ScopeInterface::SCOPE_STORE)
            ->willReturn('1');

        $this->assertSame('1', $this->config->getValue('topmenu/enabled'));
    }

    public function testGetValueWithoutPathReturnsWholeModuleConfig(): void
    {
        $this->scopeConfig->expects($this->once())
            ->method('getValue')
            ->with('magicmenu', ScopeInterface::SCOPE_STORE)
            ->willReturn(['topmenu' => ['enabled' => '1']]);

        $this->assertSame(['topmenu' => ['enabled' => '1']], $this->config->getValue(''));
    }

    public function testGetModuleConfigReturnsArray(): void
    {
        $this->scopeConfig->method('getValue')
            ->with('magicmenu', ScopeInterface::SCOPE_STORE)
            ->willReturn('not-an-array');

        $this->assertSame([], $this->config->getModuleConfig());
    }

    public function testGetScopeValueUsesAbsolutePath(): void
    {
        $this->scopeConfig->expects($this->once())
            ->method('getValue')
            ->with('web/url/use_store', ScopeInterface::SCOPE_STORE)
            ->willReturn(1);

        $this->assertSame(1, $this->config->getScopeValue('web/url/use_store'));
    }

    public function testIsEnabledCastsToBool(): void
    {
        $this->scopeConfig->method('getValue')->willReturn('0');

        $this->assertFalse($this->config->isEnabled('topmenu/enabled'));
    }
}
