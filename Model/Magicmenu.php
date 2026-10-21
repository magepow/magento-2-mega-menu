<?php
/**
 * Copyright © Magiccart. All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Magiccart\Magicmenu\Model;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magiccart\Magicmenu\Api\Data\MagicmenuInterface;

class Magicmenu extends AbstractModel implements MagicmenuInterface
{
    public function __construct(
        Context $context,
        Registry $registry,
        ResourceModel\Magicmenu $resource,
        ResourceModel\Magicmenu\Collection $resourceCollection
    ) {
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection
        );
    }

    /**
     * @inheritdoc
     */
    public function getCatId()
    {
        return $this->getData(self::KEY_CAT_ID);
    }

    /**
     * @inheritdoc
     */
    public function setCatId($catId)
    {
        return $this->setData(self::KEY_CAT_ID, $catId);
    }

    /**
     * @inheritdoc
     */
    public function getCatCol()
    {
        return $this->getData(self::KEY_CAT_COL);
    }

    /**
     * @inheritdoc
     */
    public function setCatCol($catCol)
    {
        return $this->setData(self::KEY_CAT_COL, $catCol);
    }

    /**
     * @inheritdoc
     */
    public function getCatProportion()
    {
        return $this->getData(self::KEY_CAT_PROPORTION);
    }

    /**
     * @inheritdoc
     */
    public function setCatProportion($catProportion)
    {
        return $this->setData(self::KEY_CAT_PROPORTION, $catProportion);
    }

    /**
     * @inheritdoc
     */
    public function getTop()
    {
        return $this->getData(self::KEY_TOP);
    }

    /**
     * @inheritdoc
     */
    public function setTop($top)
    {
        return $this->setData(self::KEY_TOP, $top);
    }

    /**
     * @inheritdoc
     */
    public function getRight()
    {
        return $this->getData(self::KEY_RIGHT);
    }

    /**
     * @inheritdoc
     */
    public function setRight($right)
    {
        return $this->setData(self::KEY_RIGHT, $right);
    }

    /**
     * @inheritdoc
     */
    public function getRightProportion()
    {
        return $this->getData(self::KEY_RIGHT_PROPORTION);
    }

    /**
     * @inheritdoc
     */
    public function setRightProportion($rightProportion)
    {
        return $this->setData(self::KEY_RIGHT_PROPORTION, $rightProportion);
    }

    /**
     * @inheritdoc
     */
    public function getBottom()
    {
        return $this->getData(self::KEY_BOTTOM);
    }

    /**
     * @inheritdoc
     */
    public function setBottom($bottom)
    {
        return $this->setData(self::KEY_BOTTOM, $bottom);
    }

    /**
     * @inheritdoc
     */
    public function getLeft()
    {
        return $this->getData(self::KEY_LEFT);
    }

    /**
     * @inheritdoc
     */
    public function setLeft($left)
    {
        return $this->setData(self::KEY_LEFT, $left);
    }

    /**
     * @inheritdoc
     */
    public function getLeftProportion()
    {
        return $this->getData(self::KEY_LEFT_PROPORTION);
    }

    /**
     * @inheritdoc
     */
    public function setLeftProportion($leftProportion)
    {
        return $this->setData(self::KEY_LEFT_PROPORTION, $leftProportion);
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return $this->getData(self::KEY_NAME);
    }

    /**
     * @inheritdoc
     */
    public function setName($name)
    {
        return $this->setData(self::KEY_NAME, $name);
    }

    /**
     * @inheritdoc
     */
    public function getMagicLabel()
    {
        return $this->getData(self::KEY_MAGIC_LABEL);
    }

    /**
     * @inheritdoc
     */
    public function setMagicLabel($magicLabel)
    {
        return $this->setData(self::KEY_MAGIC_LABEL, $magicLabel);
    }

    /**
     * @inheritdoc
     */
    public function getLink()
    {
        return $this->getData(self::KEY_LINK);
    }

    /**
     * @inheritdoc
     */
    public function setLink($link)
    {
        return $this->setData(self::KEY_LINK, $link);
    }

    /**
     * @inheritdoc
     */
    public function getExtContent()
    {
        return $this->getData(self::KEY_EXT_CONTENT);
    }

    /**
     * @inheritdoc
     */
    public function setExtContent($extContent)
    {
        return $this->setData(self::KEY_EXT_CONTENT, $extContent);
    }

    /**
     * @inheritdoc
     */
    public function getExtra()
    {
        return $this->getData(self::KEY_EXTRA);
    }

    /**
     * @inheritdoc
     */
    public function setExtra($extra)
    {
        return $this->setData(self::KEY_EXTRA, $extra);
    }

    /**
     * @inheritdoc
     */
    public function getStores()
    {
        return $this->getData(self::KEY_STORES);
    }

    /**
     * @inheritdoc
     */
    public function setStores($stores)
    {
        return $this->setData(self::KEY_STORES, $stores);
    }

    /**
     * @inheritdoc
     */
    public function getStatus()
    {
        return $this->getData(self::KEY_STATUS);
    }

    /**
     * @inheritdoc
     */
    public function setStatus($status)
    {
        return $this->setData(self::KEY_STATUS, $status);
    }

    /**
     * @inheritdoc
     */
    public function getStoreViewId()
    {
        return $this->getData(self::KEY_STORE_VIEW_ID);
    }

    /**
     * @inheritdoc
     */
    public function setStoreViewId($storeViewId)
    {
        return $this->setData(self::KEY_STORE_VIEW_ID, $storeViewId);
    }
}
