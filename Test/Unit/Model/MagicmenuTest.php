<?php
/**
 * Copyright © Magiccart. All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Magiccart\Magicmenu\Test\Unit\Model;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magiccart\Magicmenu\Api\Data\MagicmenuInterface;
use Magiccart\Magicmenu\Model\Magicmenu;
use Magiccart\Magicmenu\Model\System\Config\Visibility;
use PHPUnit\Framework\TestCase;

class MagicmenuTest extends TestCase
{
    /**
     * @var Magicmenu
     */
    private $model;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->model = $objectManager->getObject(Magicmenu::class);
    }

    public function testDataAccessors(): void
    {
        $this->assertNull($this->model->getName());

        $this->model->setName('Main menu');
        $this->assertSame('Main menu', $this->model->getName());
        $this->assertSame('Main menu', $this->model->getData(MagicmenuInterface::KEY_NAME));

        $this->model->setStatus(1);
        $this->assertSame(1, $this->model->getStatus());

        $this->model->setStores('1,2');
        $this->assertSame('1,2', $this->model->getStores());

        $this->model->setExtra(1);
        $this->assertSame(1, $this->model->getExtra());

        $this->model->setLink('https://example.com');
        $this->assertSame('https://example.com', $this->model->getLink());

        $this->model->setMagicLabel('new,hot');
        $this->assertSame('new,hot', $this->model->getMagicLabel());

        $this->model->setStoreViewId(3);
        $this->assertSame(3, $this->model->getStoreViewId());
    }

    public function testVisibilityAccessor(): void
    {
        $this->assertSame(Visibility::BOTH, $this->model->getVisibility());

        $this->model->setVisibility(Visibility::MOBILE_ONLY);
        $this->assertSame(Visibility::MOBILE_ONLY, $this->model->getVisibility());
        $this->assertSame(
            Visibility::MOBILE_ONLY,
            $this->model->getData(MagicmenuInterface::KEY_VISIBILITY)
        );
    }
}
