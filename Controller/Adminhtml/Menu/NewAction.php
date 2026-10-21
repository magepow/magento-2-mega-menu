<?php
/**
 * Copyright © Magiccart. All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Magiccart\Magicmenu\Controller\Adminhtml\Menu;

class NewAction extends \Magiccart\Magicmenu\Controller\Adminhtml\Action
{
    public function execute()
    {
        $resultForward = $this->_resultForwardFactory->create();

        return $resultForward->forward('edit');
    }
}
