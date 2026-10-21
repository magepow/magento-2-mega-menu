<?php
/**
 * Copyright © Magiccart. All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Magiccart\Magicmenu\Controller\Adminhtml\Extra;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magiccart\Magicmenu\Block\Adminhtml\Extra\Grid as ExtraGrid;
use Magiccart\Magicmenu\Controller\Adminhtml\Action;

class ExportXml extends Action implements HttpGetActionInterface
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
        $fileName = 'extras.xml';

        $resultPage = $this->resultPageFactory->create();
        $content = $resultPage->getLayout()->createBlock(ExtraGrid::class)->getXml();

        return $this->fileFactory->create($fileName, $content, DirectoryList::VAR_DIR);
    }
}
