<?php
/**
 * Copyright © Magiccart. All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Magiccart\Magicmenu\Controller\Adminhtml\Extra;

use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magiccart\Magicmenu\Controller\Adminhtml\Action;

class MassStatus extends Action implements HttpPostActionInterface
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
        $status = $this->getRequest()->getParam('status');
        $storeViewId = $this->getRequest()->getParam('store');

        if (!is_array($magicmenuIds) || empty($magicmenuIds)) {
            $this->messageManager->addError(__('Please select Extra Menu(s).'));
        } else {
            $updated = 0;
            foreach ($magicmenuIds as $magicmenuId) {
                if (!is_numeric($magicmenuId)) {
                    continue;
                }
                try {
                    $magicmenu = $this->magicmenuRepository->getById((int)$magicmenuId);
                    $magicmenu->setStoreViewId($storeViewId)
                        ->setStatus($status);
                    $this->magicmenuRepository->save($magicmenu);
                    $updated++;
                } catch (NoSuchEntityException $exception) {
                    continue;
                } catch (CouldNotSaveException $exception) {
                    $this->messageManager->addError($exception->getMessage());
                }
            }

            if ($updated) {
                $this->messageManager->addSuccess(
                    __('A total of %1 record(s) have been changed status.', $updated)
                );
            }
        }

        return $this->resultRedirectFactory->create()->setPath('*/*/', ['store' => $storeViewId]);
    }
}
