<?php
/**
 * Copyright © Magiccart. All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Magiccart\Magicmenu\Controller\Adminhtml\Extra;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Filesystem;
use Magento\Framework\Image\AdapterFactory as ImageAdapterFactory;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Magiccart\Magicmenu\Api\MagicmenuRepositoryInterface;
use Magiccart\Magicmenu\Controller\Adminhtml\Action;
use Magiccart\Magicmenu\Model\MagicmenuFactory;
use Magiccart\Magicmenu\Model\ResourceModel\Magicmenu\CollectionFactory;
use Magiccart\Magicmenu\Model\System\Config\Visibility;

class Save extends Action implements HttpPostActionInterface
{
    /**
     * @inheritdoc
     */
    public const ADMIN_RESOURCE = 'Magiccart_Magicmenu::magicmenu_extra';

    /**
     * @var UploaderFactory
     */
    private $uploaderFactory;

    /**
     * @var ImageAdapterFactory
     */
    private $imageAdapterFactory;

    /**
     * @var Filesystem
     */
    private $filesystem;

    public function __construct(
        Context $context,
        MagicmenuFactory $magicmenuFactory,
        MagicmenuRepositoryInterface $magicmenuRepository,
        CollectionFactory $magicmenuCollectionFactory,
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
            $magicmenuRepository,
            $magicmenuCollectionFactory,
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

    /**
     * @inheritdoc
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $data = $this->getRequest()->getPostValue();

        if (!$data) {
            return $resultRedirect->setPath('*/*/');
        }

        $storeViewId = $this->getRequest()->getParam('store');
        $id = $this->getRequest()->getParam('magicmenu_id');

        $model = $this->magicmenuFactory->create();
        if ($id) {
            try {
                $model = $this->magicmenuRepository->getById($id);
            } catch (NoSuchEntityException $exception) {
                $model = $this->magicmenuFactory->create();
            }
        }

        $data = $this->prepareImageData($data);

        if (empty($data['link']) && empty($data['ext_content'])) {
            $this->messageManager->addError(__('You must add Link or Content'));

            return $resultRedirect->setPath('*/*/');
        }

        $data['extra'] = 1;

        if (isset($data['stores']) && is_array($data['stores'])) {
            $data['stores'] = implode(',', $data['stores']);
        }

        $data['visibility'] = Visibility::normalize($data['visibility'] ?? Visibility::BOTH);

        $model->setData($data)
            ->setStoreViewId($storeViewId);

        try {
            $this->magicmenuRepository->save($model);

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
        } catch (\Exception $exception) {
            $this->messageManager->addError($exception->getMessage());
            $this->messageManager->addException(
                $exception,
                __('Something went wrong while saving the magicmenu.')
            );
        }

        $this->_getSession()->setFormData($data);

        return $resultRedirect->setPath(
            '*/*/edit',
            ['magicmenu_id' => $id]
        );
    }

    /**
     * Process the uploaded image and return the sanitized post data.
     *
     * @param array $data
     * @return array
     */
    private function prepareImageData(array $data): array
    {
        $uploadedFile = $this->getRequest()->getFiles('image');
        if (!empty($uploadedFile['name'])) {
            try {
                $uploader = $this->uploaderFactory->create(['fileId' => 'image']);
                $uploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png']);

                $imageAdapter = $this->imageAdapterFactory->create();

                $uploader->addValidateCallback('extra_image', $imageAdapter, 'validateUploadFile');
                $uploader->setAllowRenameFiles(true);
                $uploader->setFilesDispersion(true);

                $mediaDirectory = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA);
                $result = $uploader->save(
                    $mediaDirectory->getAbsolutePath('magicmenu/extra/')
                );
                $data['image'] = 'magicmenu/extra/' . $result['file'];
            } catch (\Exception $exception) {
                if ($exception->getCode() === 0) {
                    $this->messageManager->addError($exception->getMessage());
                }
            }

            return $data;
        }

        if (isset($data['image']['value'])) {
            if (isset($data['image']['delete'])) {
                $data['image'] = null;
                $data['delete_image'] = true;
            } else {
                $data['image'] = $data['image']['value'];
            }
        }

        return $data;
    }
}
