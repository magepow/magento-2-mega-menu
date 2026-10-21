<?php
/**
 * Copyright © Magiccart. All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Magiccart\Magicmenu\Controller\Adminhtml\Menu;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magiccart\Magicmenu\Controller\Adminhtml\Action;

class Edit extends Action implements HttpGetActionInterface
{
    /**
     * @inheritdoc
     */
    public const ADMIN_RESOURCE = 'Magiccart_Magicmenu::magicmenu_menu';

    /**
     * @inheritdoc
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('magicmenu_id');

        if ($id) {
            try {
                $this->magicmenuRepository->getById($id);
            } catch (NoSuchEntityException $exception) {
                $this->messageManager->addError(__('This Menu no longer exists.'));

                return $this->resultRedirectFactory->create()->setPath('*/*/');
            }
        }

        $resultPage = $this->resultPageFactory->create();

        return $resultPage;
    }
}
