<?php
/**
 * Copyright © Magiccart. All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Magiccart\Magicmenu\Api;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magiccart\Magicmenu\Api\Data\MagicmenuInterface;
use Magiccart\Magicmenu\Api\Data\MagicmenuSearchResultsInterface;

/**
 * Magicmenu CRUD interface.
 *
 * @api
 */
interface MagicmenuRepositoryInterface
{
    /**
     * Save magicmenu.
     *
     * @param MagicmenuInterface $magicmenu
     * @return MagicmenuInterface
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function save(MagicmenuInterface $magicmenu);

    /**
     * Retrieve magicmenu by id.
     *
     * @param int $magicmenuId
     * @return MagicmenuInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($magicmenuId);

    /**
     * Retrieve magicmenus matching the specified criteria.
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return MagicmenuSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(SearchCriteriaInterface $searchCriteria);

    /**
     * Delete magicmenu.
     *
     * @param MagicmenuInterface $magicmenu
     * @return bool
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function delete(MagicmenuInterface $magicmenu);

    /**
     * Delete magicmenu by id.
     *
     * @param int $magicmenuId
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function deleteById($magicmenuId);
}
