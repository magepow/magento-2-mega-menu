<?php
/**
 * Copyright © Magiccart. All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Magiccart\Magicmenu\Controller\Adminhtml\Extra;

use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magiccart\Magicmenu\Controller\Adminhtml\Action;

class MassDelete extends Action implements HttpPostActionInterface
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
        $magicmenuIds = $this->getRequest()->getParam('magicmenu');

        if (!is_array($magicmenuIds) || empty($magicmenuIds)) {
            $this->messageManager->addError(__('Please select magicmenu(s).'));
        } else {
            $deleted = 0;
            foreach ($magicmenuIds as $magicmenuId) {
                if (!is_numeric($magicmenuId)) {
                    continue;
                }
                try {
                    $this->magicmenuRepository->deleteById((int)$magicmenuId);
                    $deleted++;
                } catch (NoSuchEntityException $exception) {
                    continue;
                } catch (CouldNotDeleteException $exception) {
                    $this->messageManager->addError($exception->getMessage());
                }
            }

            if ($deleted) {
                $this->messageManager->addSuccess(
                    __('A total of %1 record(s) have been deleted.', $deleted)
                );
            }
        }

        return $this->resultRedirectFactory->create()->setPath('*/*/');
    }
}
