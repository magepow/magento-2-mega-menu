<?php
/**
 * Copyright © Magiccart. All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Magiccart\Magicmenu\Controller\Adminhtml\Menu;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magiccart\Magicmenu\Block\Adminhtml\Menu\Grid as MenuGrid;
use Magiccart\Magicmenu\Controller\Adminhtml\Action;

class ExportXml extends Action implements HttpGetActionInterface
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
        $fileName = 'menus.xml';

        $resultPage = $this->resultPageFactory->create();
        $content = $resultPage->getLayout()->createBlock(MenuGrid::class)->getXml();

        return $this->fileFactory->create($fileName, $content, DirectoryList::VAR_DIR);
    }
}
