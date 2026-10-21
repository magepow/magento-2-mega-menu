<?php
/**
 * Copyright © Magiccart. All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Magiccart\Magicmenu\Model;

use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magiccart\Magicmenu\Api\Data\MagicmenuInterface;
use Magiccart\Magicmenu\Api\Data\MagicmenuSearchResultsInterface;
use Magiccart\Magicmenu\Api\Data\MagicmenuSearchResultsInterfaceFactory;
use Magiccart\Magicmenu\Api\MagicmenuRepositoryInterface;
use Magiccart\Magicmenu\Model\ResourceModel\Magicmenu as ResourceMagicmenu;
use Magiccart\Magicmenu\Model\ResourceModel\Magicmenu\CollectionFactory as MagicmenuCollectionFactory;

/**
 * Magicmenu repository.
 */
class MagicmenuRepository implements MagicmenuRepositoryInterface
{
    /**
     * @var ResourceMagicmenu
     */
    private $resource;

    /**
     * @var MagicmenuFactory
     */
    private $magicmenuFactory;

    /**
     * @var MagicmenuCollectionFactory
     */
    private $magicmenuCollectionFactory;

    /**
     * @var MagicmenuSearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    public function __construct(
        ResourceMagicmenu $resource,
        MagicmenuFactory $magicmenuFactory,
        MagicmenuCollectionFactory $magicmenuCollectionFactory,
        MagicmenuSearchResultsInterfaceFactory $searchResultsFactory,
        CollectionProcessorInterface $collectionProcessor
    ) {
        $this->resource = $resource;
        $this->magicmenuFactory = $magicmenuFactory;
        $this->magicmenuCollectionFactory = $magicmenuCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionProcessor = $collectionProcessor;
    }

    /**
     * @inheritdoc
     */
    public function save(MagicmenuInterface $magicmenu)
    {
        try {
            $this->resource->save($magicmenu);
        } catch (LocalizedException $exception) {
            throw new CouldNotSaveException(
                __('Could not save the magicmenu: %1', $exception->getMessage()),
                $exception
            );
        } catch (\Throwable $exception) {
            throw new CouldNotSaveException(
                __('Could not save the magicmenu: %1', __('Something went wrong while saving the magicmenu.')),
                $exception
            );
        }

        return $magicmenu;
    }

    /**
     * @inheritdoc
     */
    public function getById($magicmenuId)
    {
        $magicmenu = $this->magicmenuFactory->create();
        $magicmenu->load($magicmenuId);

        if (!$magicmenu->getId()) {
            throw new NoSuchEntityException(
                __('The magicmenu with the "%1" ID doesn\'t exist.', $magicmenuId)
            );
        }

        return $magicmenu;
    }

    /**
     * @inheritdoc
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        $collection = $this->magicmenuCollectionFactory->create();
        $this->collectionProcessor->process($searchCriteria, $collection);

        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());

        return $searchResults;
    }

    /**
     * @inheritdoc
     */
    public function delete(MagicmenuInterface $magicmenu)
    {
        try {
            $this->resource->delete($magicmenu);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(
                __('Could not delete the magicmenu: %1', $exception->getMessage()),
                $exception
            );
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function deleteById($magicmenuId)
    {
        return $this->delete($this->getById($magicmenuId));
    }
}
