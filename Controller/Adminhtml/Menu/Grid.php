<?php
/**
 * Copyright © Magiccart. All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Magiccart\Magicmenu\Controller\Adminhtml\Menu;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magiccart\Magicmenu\Controller\Adminhtml\Action;

class Grid extends Action implements HttpGetActionInterface
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
        $resultLayout = $this->resultLayoutFactory->create();

        return $resultLayout;
    }
}
