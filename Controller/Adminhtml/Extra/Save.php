<?php
/**
 * Copyright © Magiccart. All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Magiccart\Magicmenu\Controller\Adminhtml\Extra;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Image\AdapterFactory as ImageAdapterFactory;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Magiccart\Magicmenu\Controller\Adminhtml\Action;

class Save extends Action
{
    private UploaderFactory $uploaderFactory;
    private ImageAdapterFactory $imageAdapterFactory;
    private Filesystem $filesystem;

    public function __construct(
        Context $context,
        \Magiccart\Magicmenu\Model\MagicmenuFactory $magicmenuFactory,
        \Magiccart\Magicmenu\Model\ResourceModel\Magicmenu\CollectionFactory $magicmenuCollectionFactory,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory,
        \Magento\Backend\Helper\Js $jsHelper,
        UploaderFactory $uploaderFactory,
        ImageAdapterFactory $imageAdapterFactory,
        Filesystem $filesystem
    ) {
        parent::__construct(
            $context,
            $magicmenuFactory,
            $magicmenuCollectionFactory,
            $coreRegistry,
            $fileFactory,
            $resultPageFactory,
            $resultLayoutFactory,
            $resultForwardFactory,
            $jsHelper
        );
        $this->uploaderFactory = $uploaderFactory;
        $this->imageAdapterFactory = $imageAdapterFactory;
        $this->filesystem = $filesystem;
    }

    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();

        if ($data = $this->getRequest()->getPostValue()) {
            $model = $this->_magicmenuFactory->create();
            $storeViewId = $this->getRequest()->getParam('store');

            if ($id = $this->getRequest()->getParam('magicmenu_id')) {
                $model->load($id);
            }

            if (isset($_FILES['image']) && isset($_FILES['image']['name']) && strlen((string) $_FILES['image']['name'])) {
                try {
                    $uploader = $this->uploaderFactory->create(['fileId' => 'image']);
                    $uploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png']);

                    $imageAdapter = $this->imageAdapterFactory->create();

                    $uploader->addValidateCallback('extra_image', $imageAdapter, 'validateUploadFile');
                    $uploader->setAllowRenameFiles(true);
                    $uploader->setFilesDispersion(true);

                    $mediaDirectory = $this->filesystem
                        ->getDirectoryRead(DirectoryList::MEDIA);
                    $result = $uploader->save(
                        $mediaDirectory->getAbsolutePath('magicmenu/extra/')
                    );
                    $data['image'] = 'magicmenu/extra/' . $result['file'];
                } catch (\Exception $e) {
                    if ($e->getCode() == 0) {
                        $this->messageManager->addError($e->getMessage());
                    }
                }
            } else {
                if (isset($data['image']) && isset($data['image']['value'])) {
                    if (isset($data['image']['delete'])) {
                        $data['image'] = null;
                        $data['delete_image'] = true;
                    } elseif (isset($data['image']['value'])) {
                        $data['image'] = $data['image']['value'];
                    } else {
                        $data['image'] = null;
                    }
                }
            }

            if (!$data['link'] && !$data['ext_content']) {
                $this->messageManager->addError(__('You must add Link or Content'));
                return $resultRedirect->setPath('*/*/');
            } else {
                $data['extra'] = 1;
            }
            if (isset($data['stores'])) {
                $data['stores'] = implode(',', $data['stores']);
            }
            $model->setData($data)
                ->setStoreViewId($storeViewId);

            try {
                $model->save();

                $this->messageManager->addSuccess(__('The Extra menu has been saved.'));
                $this->_getSession()->setFormData(false);

                if ($this->getRequest()->getParam('back') === 'edit') {
                    return $resultRedirect->setPath(
                        '*/*/edit',
                        [
                            'magicmenu_id' => $model->getId(),
                            '_current' => true,
                            'store' => $storeViewId,
                            'current_magicmenu_id' => $this->getRequest()->getParam('current_magicmenu_id'),
                            'saveandclose' => $this->getRequest()->getParam('saveandclose'),
                        ]
                    );
                } elseif ($this->getRequest()->getParam('back') === 'new') {
                    return $resultRedirect->setPath(
                        '*/*/new',
                        ['_current' => true]
                    );
                }

                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                $this->messageManager->addException($e, __('Something went wrong while saving the magicmenu.'));
            }

            $this->_getSession()->setFormData($data);

            return $resultRedirect->setPath(
                '*/*/edit',
                ['magicmenu_id' => $this->getRequest()->getParam('magicmenu_id')]
            );
        }

        return $resultRedirect->setPath('*/*/');
    }
}
