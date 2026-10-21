<?php
/**
 * Copyright © Magiccart. All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Magiccart\Magicmenu\Controller\Adminhtml\Extra;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magiccart\Magicmenu\Controller\Adminhtml\Action;

class NewAction extends Action implements HttpGetActionInterface
{
    /**
     * @inheritdoc
     */
    public const ADMIN_RESOURCE = 'Magiccart_Magicmenu::magicmenu_extra';

    public function execute()
    {
        $resultForward = $this->resultForwardFactory->create();

        return $resultForward->forward('edit');
    }
}
