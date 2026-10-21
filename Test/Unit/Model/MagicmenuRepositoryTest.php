<?php
/**
 * Copyright © Magiccart. All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Magiccart\Magicmenu\Test\Unit\Model;

use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magiccart\Magicmenu\Api\Data\MagicmenuInterface;
use Magiccart\Magicmenu\Api\Data\MagicmenuSearchResultsInterface;
use Magiccart\Magicmenu\Api\Data\MagicmenuSearchResultsInterfaceFactory;
use Magiccart\Magicmenu\Model\Magicmenu;
use Magiccart\Magicmenu\Model\MagicmenuFactory;
use Magiccart\Magicmenu\Model\MagicmenuRepository;
use Magiccart\Magicmenu\Model\ResourceModel\Magicmenu as ResourceMagicmenu;
use Magiccart\Magicmenu\Model\ResourceModel\Magicmenu\Collection;
use Magiccart\Magicmenu\Model\ResourceModel\Magicmenu\CollectionFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class MagicmenuRepositoryTest extends TestCase
{
    /**
     * @var ResourceMagicmenu|MockObject
     */
    private $resource;

    /**
     * @var MagicmenuFactory|MockObject
     */
    private $magicmenuFactory;

    /**
     * @var CollectionFactory|MockObject
     */
    private $collectionFactory;

    /**
     * @var MagicmenuSearchResultsInterfaceFactory|MockObject
     */
    private $searchResultsFactory;

    /**
     * @var CollectionProcessorInterface|MockObject
     */
    private $collectionProcessor;

    /**
     * @var MagicmenuRepository
     */
    private $repository;

    protected function setUp(): void
    {
        $this->resource = $this->createMock(ResourceMagicmenu::class);
        $this->magicmenuFactory = $this->createMock(MagicmenuFactory::class);
        $this->collectionFactory = $this->createMock(CollectionFactory::class);
        $this->searchResultsFactory = $this->createMock(MagicmenuSearchResultsInterfaceFactory::class);
        $this->collectionProcessor = $this->getMockForAbstractClass(CollectionProcessorInterface::class);

        $this->repository = new MagicmenuRepository(
            $this->resource,
            $this->magicmenuFactory,
            $this->collectionFactory,
            $this->searchResultsFactory,
            $this->collectionProcessor
        );
    }

    public function testSaveDelegatesToResource(): void
    {
        /** @var Magicmenu|MockObject $magicmenu */
        $magicmenu = $this->createMock(Magicmenu::class);

        $this->resource->expects($this->once())->method('save')->with($magicmenu);

        $this->assertSame($magicmenu, $this->repository->save($magicmenu));
    }

    public function testSaveThrowsCouldNotSaveException(): void
    {
        /** @var Magicmenu|MockObject $magicmenu */
        $magicmenu = $this->createMock(Magicmenu::class);
        $this->resource->method('save')->willThrowException(new \RuntimeException('failure'));

        $this->expectException(CouldNotSaveException::class);

        $this->repository->save($magicmenu);
    }

    public function testGetByIdReturnsModel(): void
    {
        /** @var Magicmenu|MockObject $magicmenu */
        $magicmenu = $this->createMock(Magicmenu::class);
        $magicmenu->method('getId')->willReturn(5);

        $this->magicmenuFactory->expects($this->once())
            ->method('create')
            ->willReturn($magicmenu);
        $magicmenu->expects($this->once())->method('load')->with(5);

        $this->assertSame($magicmenu, $this->repository->getById(5));
    }

    public function testGetByIdThrowsNoSuchEntityException(): void
    {
        /** @var Magicmenu|MockObject $magicmenu */
        $magicmenu = $this->createMock(Magicmenu::class);
        $magicmenu->method('getId')->willReturn(null);

        $this->magicmenuFactory->method('create')->willReturn($magicmenu);

        $this->expectException(NoSuchEntityException::class);

        $this->repository->getById(5);
    }

    public function testGetListReturnsSearchResults(): void
    {
        /** @var Collection|MockObject $collection */
        $collection = $this->createMock(Collection::class);
        $collection->method('getItems')->willReturn([]);
        $collection->method('getSize')->willReturn(0);

        /** @var MagicmenuSearchResultsInterface|MockObject $searchResults */
        $searchResults = $this->getMockForAbstractClass(MagicmenuSearchResultsInterface::class);

        $this->collectionFactory->method('create')->willReturn($collection);
        $this->searchResultsFactory->method('create')->willReturn($searchResults);
        $this->collectionProcessor->expects($this->once())
            ->method('process')
            ->with($this->isInstanceOf(SearchCriteriaInterface::class), $collection);

        $searchCriteria = $this->getMockForAbstractClass(SearchCriteriaInterface::class);

        $this->assertSame($searchResults, $this->repository->getList($searchCriteria));
    }

    public function testDeleteByIdDeletesTheEntity(): void
    {
        /** @var Magicmenu|MockObject $magicmenu */
        $magicmenu = $this->createMock(Magicmenu::class);
        $magicmenu->method('getId')->willReturn(7);

        $this->magicmenuFactory->method('create')->willReturn($magicmenu);
        $this->resource->expects($this->once())->method('delete')->with($magicmenu);

        $this->assertTrue($this->repository->deleteById(7));
    }

    public function testDeleteThrowsCouldNotDeleteException(): void
    {
        /** @var MagicmenuInterface|MockObject $magicmenu */
        $magicmenu = $this->getMockForAbstractClass(MagicmenuInterface::class);
        $this->resource->method('delete')->willThrowException(new \RuntimeException('failure'));

        $this->expectException(CouldNotDeleteException::class);

        $this->repository->delete($magicmenu);
    }
}
