<?php
/**
 * Copyright © Magiccart. All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Magiccart\Magicmenu\Block\Adminhtml;

class Menu extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * Constructor.
     */
    protected function _construct()
    {
        $this->_controller = 'adminhtml_menu';
        $this->_blockGroup = 'Magiccart_Magicmenu';
        $this->_headerText = __('Menu Menu');
        $this->_addButtonLabel = __('Add New Menu');
        parent::_construct();
    }
}
