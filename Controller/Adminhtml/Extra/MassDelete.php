<?php
/**
 * Copyright © Magiccart. All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Magiccart\Magicmenu\Controller\Adminhtml\Extra;

class MassDelete extends \Magiccart\Magicmenu\Controller\Adminhtml\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    public function execute()
    {
        $magicmenuIds = $this->getRequest()->getParam('magicmenu');
        if (!is_array($magicmenuIds) || empty($magicmenuIds)) {
            $this->messageManager->addError(__('Please select magicmenu(s).'));
        } else {
            $collection = $this->_magicmenuCollectionFactory->create()
                ->addFieldToFilter('magicmenu_id', ['in' => $magicmenuIds]);
            try {
                foreach ($collection as $item) {
                    $item->delete();
                }
                $this->messageManager->addSuccess(
                    __('A total of %1 record(s) have been deleted.', count($magicmenuIds))
                );
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
        }
        $resultRedirect = $this->resultRedirectFactory->create();

        return $resultRedirect->setPath('*/*/');
    }
}
