<?php
/**
 * Copyright © Magiccart. All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Magiccart\Magicmenu\Api\Data;

/**
 * Magicmenu data interface.
 *
 * @api
 */
interface MagicmenuInterface
{
    /**
     * Data keys of the magicmenu entity.
     */
    public const KEY_ID = 'magicmenu_id';
    public const KEY_CAT_ID = 'cat_id';
    public const KEY_CAT_COL = 'cat_col';
    public const KEY_CAT_PROPORTION = 'cat_proportion';
    public const KEY_TOP = 'top';
    public const KEY_RIGHT = 'right';
    public const KEY_RIGHT_PROPORTION = 'right_proportion';
    public const KEY_BOTTOM = 'bottom';
    public const KEY_LEFT = 'left';
    public const KEY_LEFT_PROPORTION = 'left_proportion';
    public const KEY_NAME = 'name';
    public const KEY_MAGIC_LABEL = 'magic_label';
    public const KEY_LINK = 'link';
    public const KEY_EXT_CONTENT = 'ext_content';
    public const KEY_EXTRA = 'extra';
    public const KEY_STORES = 'stores';
    public const KEY_ORDER = 'order';
    public const KEY_STATUS = 'status';
    public const KEY_STORE_VIEW_ID = 'store_view_id';

    /**
     * Get magicmenu id.
     *
     * @return int|null
     */
    public function getId();

    /**
     * Set magicmenu id.
     *
     * @param int|null $id
     * @return $this
     */
    public function setId($id);

    /**
     * Get category id the menu is bound to.
     *
     * @return string|null
     */
    public function getCatId();

    /**
     * Set category id the menu is bound to.
     *
     * @param string|null $catId
     * @return $this
     */
    public function setCatId($catId);

    /**
     * Get number of columns of the category block.
     *
     * @return string|null
     */
    public function getCatCol();

    /**
     * Set number of columns of the category block.
     *
     * @param string|null $catCol
     * @return $this
     */
    public function setCatCol($catCol);

    /**
     * Get proportion of the category block.
     *
     * @return int|null
     */
    public function getCatProportion();

    /**
     * Set proportion of the category block.
     *
     * @param int|null $catProportion
     * @return $this
     */
    public function setCatProportion($catProportion);

    /**
     * Get top block identifier.
     *
     * @return string|null
     */
    public function getTop();

    /**
     * Set top block identifier.
     *
     * @param string|null $top
     * @return $this
     */
    public function setTop($top);

    /**
     * Get right block identifier.
     *
     * @return string|null
     */
    public function getRight();

    /**
     * Set right block identifier.
     *
     * @param string|null $right
     * @return $this
     */
    public function setRight($right);

    /**
     * Get proportion of the right block.
     *
     * @return int|null
     */
    public function getRightProportion();

    /**
     * Set proportion of the right block.
     *
     * @param int|null $rightProportion
     * @return $this
     */
    public function setRightProportion($rightProportion);

    /**
     * Get bottom block identifier.
     *
     * @return string|null
     */
    public function getBottom();

    /**
     * Set bottom block identifier.
     *
     * @param string|null $bottom
     * @return $this
     */
    public function setBottom($bottom);

    /**
     * Get left block identifier.
     *
     * @return string|null
     */
    public function getLeft();

    /**
     * Set left block identifier.
     *
     * @param string|null $left
     * @return $this
     */
    public function setLeft($left);

    /**
     * Get proportion of the left block.
     *
     * @return int|null
     */
    public function getLeftProportion();

    /**
     * Set proportion of the left block.
     *
     * @param int|null $leftProportion
     * @return $this
     */
    public function setLeftProportion($leftProportion);

    /**
     * Get menu name.
     *
     * @return string|null
     */
    public function getName();

    /**
     * Set menu name.
     *
     * @param string|null $name
     * @return $this
     */
    public function setName($name);

    /**
     * Get menu label.
     *
     * @return string|null
     */
    public function getMagicLabel();

    /**
     * Set menu label.
     *
     * @param string|null $magicLabel
     * @return $this
     */
    public function setMagicLabel($magicLabel);

    /**
     * Get menu link.
     *
     * @return string|null
     */
    public function getLink();

    /**
     * Set menu link.
     *
     * @param string|null $link
     * @return $this
     */
    public function setLink($link);

    /**
     * Get extra menu content.
     *
     * @return string|null
     */
    public function getExtContent();

    /**
     * Set extra menu content.
     *
     * @param string|null $extContent
     * @return $this
     */
    public function setExtContent($extContent);

    /**
     * Get extra menu flag.
     *
     * @return int|null
     */
    public function getExtra();

    /**
     * Set extra menu flag.
     *
     * @param int|null $extra
     * @return $this
     */
    public function setExtra($extra);

    /**
     * Get comma separated list of store ids.
     *
     * @return string|null
     */
    public function getStores();

    /**
     * Set comma separated list of store ids.
     *
     * @param string|null $stores
     * @return $this
     */
    public function setStores($stores);

    /**
     * Get menu status.
     *
     * @return int|null
     */
    public function getStatus();

    /**
     * Set menu status.
     *
     * @param int|null $status
     * @return $this
     */
    public function setStatus($status);

    /**
     * Get store view id the menu was created in.
     *
     * @return int|null
     */
    public function getStoreViewId();

    /**
     * Set store view id the menu was created in.
     *
     * @param int|null $storeViewId
     * @return $this
     */
    public function setStoreViewId($storeViewId);
}
