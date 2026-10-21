<?php
/**
 * Copyright © Magiccart. All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Magiccart\Magicmenu\Controller\Adminhtml;

use Magento\Backend\App\Action as BackendAction;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Helper\Js;
use Magento\Backend\Model\View\Result\ForwardFactory;
use Magento\Backend\Model\View\Result\RedirectFactory;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\View\Result\LayoutFactory;
use Magento\Framework\View\Result\PageFactory;
use Magiccart\Magicmenu\Api\MagicmenuRepositoryInterface;
use Magiccart\Magicmenu\Model\MagicmenuFactory;
use Magiccart\Magicmenu\Model\ResourceModel\Magicmenu\CollectionFactory;

abstract class Action extends BackendAction
{
    /**
     * Default ACL resource. Concrete controllers must override it.
     */
    public const ADMIN_RESOURCE = 'Magiccart_Magicmenu::magicmenu';

    /**
     * @var Js
     */
    protected $jsHelper;

    /**
     * @var ForwardFactory
     */
    protected $resultForwardFactory;

    /**
     * @var LayoutFactory
     */
    protected $resultLayoutFactory;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var RedirectFactory
     */
    protected $resultRedirectFactory;

    /**
     * @var MagicmenuFactory
     */
    protected $magicmenuFactory;

    /**
     * @var MagicmenuRepositoryInterface
     */
    protected $magicmenuRepository;

    /**
     * @var CollectionFactory
     */
    protected $magicmenuCollectionFactory;

    /**
     * File Factory.
     *
     * @var FileFactory
     */
    protected $fileFactory;

    public function __construct(
        Context $context,
        MagicmenuFactory $magicmenuFactory,
        MagicmenuRepositoryInterface $magicmenuRepository,
        CollectionFactory $magicmenuCollectionFactory,
        FileFactory $fileFactory,
        PageFactory $resultPageFactory,
        LayoutFactory $resultLayoutFactory,
        ForwardFactory $resultForwardFactory,
        Js $jsHelper
    ) {
        parent::__construct($context);
        $this->fileFactory = $fileFactory;
        $this->jsHelper = $jsHelper;

        $this->resultPageFactory = $resultPageFactory;
        $this->resultLayoutFactory = $resultLayoutFactory;
        $this->resultForwardFactory = $resultForwardFactory;
        $this->resultRedirectFactory = $context->getResultRedirectFactory();

        $this->magicmenuFactory = $magicmenuFactory;
        $this->magicmenuRepository = $magicmenuRepository;
        $this->magicmenuCollectionFactory = $magicmenuCollectionFactory;
    }
}
