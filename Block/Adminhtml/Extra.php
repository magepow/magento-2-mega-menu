<?php
/**
 * Copyright © Magiccart. All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Magiccart\Magicmenu\Block\Adminhtml;

class Extra extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * Constructor.
     */
    protected function _construct()
    {
        $this->_controller = 'adminhtml_extra';
        $this->_blockGroup = 'Magiccart_Magicmenu';
        $this->_headerText = __('Extra Menu');
        $this->_addButtonLabel = __('Add New Extra Menu');
        parent::_construct();
    }
}
