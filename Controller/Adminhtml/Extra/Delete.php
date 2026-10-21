<?php
/**
 * Copyright © Magiccart. All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Magiccart\Magicmenu\Controller\Adminhtml\Extra;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magiccart\Magicmenu\Controller\Adminhtml\Action;

class Delete extends Action implements HttpGetActionInterface
{
    /**
     * @inheritdoc
     */
    public const ADMIN_RESOURCE = 'Magiccart_Magicmenu::magicmenu_extra';

    /**
     * @inheritdoc
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('magicmenu_id');

        if (!$id || !is_numeric($id)) {
            $this->messageManager->addError(__('We can\'t find a menu to delete.'));

            return $this->resultRedirectFactory->create()->setPath('*/*/');
        }

        try {
            $this->magicmenuRepository->deleteById((int)$id);
            $this->messageManager->addSuccess(__('Delete successfully !'));
        } catch (NoSuchEntityException $exception) {
            $this->messageManager->addError(__('We can\'t find a menu to delete.'));
        } catch (CouldNotDeleteException $exception) {
            $this->messageManager->addError($exception->getMessage());
        }

        return $this->resultRedirectFactory->create()->setPath('*/*/');
    }
}
