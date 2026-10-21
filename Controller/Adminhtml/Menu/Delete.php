<?php
/**
 * Copyright © Magiccart. All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Magiccart\Magicmenu\Controller\Adminhtml\Menu;

class Delete extends \Magiccart\Magicmenu\Controller\Adminhtml\Action
{
    public function execute()
    {
        $id = $this->getRequest()->getParam('magicmenu_id');
        try {
            $item = $this->_magicmenuFactory->create()->setId($id);
            $item->delete();
            $this->messageManager->addSuccess(
                __('Delete successfully !')
            );
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        }

        $resultRedirect = $this->resultRedirectFactory->create();

        return $resultRedirect->setPath('*/*/');
    }
}
