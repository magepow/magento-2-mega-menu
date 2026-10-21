<?php
/**
 * Copyright © Magiccart. All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Magiccart\Magicmenu\Model;

class Magicmenu extends \Magento\Framework\Model\AbstractModel
{

    protected $_magicmenuCollectionFactory;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magiccart\Magicmenu\Model\ResourceModel\Magicmenu\CollectionFactory $magicmenuCollectionFactory,
        \Magiccart\Magicmenu\Model\ResourceModel\Magicmenu $resource,
        \Magiccart\Magicmenu\Model\ResourceModel\Magicmenu\Collection $resourceCollection
    ) {
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection
        );
        $this->_magicmenuCollectionFactory = $magicmenuCollectionFactory;
    }

}
