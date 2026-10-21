<?php
/**
 * Copyright © Magiccart. All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Magiccart\Magicmenu\Block\Adminhtml\Extra\Edit\Tab;

use Magento\Backend\Block\Store\Switcher\Form\Renderer\Fieldset\Element as FieldsetElement;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Store\Model\System\Store;
use Magiccart\Magicmenu\Api\Data\MagicmenuInterface;
use Magiccart\Magicmenu\Api\MagicmenuRepositoryInterface;
use Magiccart\Magicmenu\Model\MagicmenuFactory;
use Magiccart\Magicmenu\Model\Status;
use Magiccart\Magicmenu\Model\System\Config\Blocks;

class Form extends Generic implements TabInterface
{
    /**
     * @var Blocks
     */
    protected $_blocks;

    /**
     * @var Store
     */
    protected $_systemStore;

    /**
     * @var MagicmenuRepositoryInterface
     */
    private $magicmenuRepository;

    /**
     * @var MagicmenuFactory
     */
    private $magicmenuFactory;

    /**
     * @var MagicmenuInterface|null
     */
    private $magicmenu;

    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        Store $systemStore,
        MagicmenuRepositoryInterface $magicmenuRepository,
        MagicmenuFactory $magicmenuFactory,
        Blocks $blocks,
        array $data = []
    ) {
        $this->_blocks = $blocks;
        $this->_systemStore = $systemStore;
        $this->magicmenuRepository = $magicmenuRepository;
        $this->magicmenuFactory = $magicmenuFactory;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * prepare layout.
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        $this->getLayout()->getBlock('page.title')->setPageTitle($this->getPageTitle());

        return $this;
    }

    /**
     * Prepare form.
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        $model = $this->getMagicmenu();

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $form->setHtmlIdPrefix('magic_');

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Extra Menu Information')]);

        if ($model->getId()) {
            $fieldset->addField('magicmenu_id', 'hidden', ['name' => 'magicmenu_id']);
        }

        $fieldset->addField('name', 'text',
            [
                'label' => __('Name'),
                'title' => __('Name'),
                'name'  => 'name',
                'required' => true,
            ]
        );

        $fieldset->addField('magic_label', 'text',
            [
                'label' => __('Label'),
                'title' => __('Label'),
                'name'  => 'magic_label',
                'after_element_html' => '<p class="nm"><small>Example: New,Hot,...</small></p>',
            ]
        );

        $fieldset->addField('link', 'text',
            [
                'label' => __('Link'),
                'title' => __('Link'),
                'name'  => 'link',
                'required' => true,
                'after_element_html' => '
                <p class="nm"><small>Link for Menu:</small></p>
                <p class="nm"><small>Ex1: http://domain.com/contact</small></p>
                <p class="nm"><small>Ex2: magicproduct/index/product/type/bestseller</small></p>
                <p class="nm"><small>Ex3: bestseller (URL Rewrite)</small></p>
            ',
            ]
        );

        $fieldset->addField('cat_col', 'text',
            [
                'label' => __('Classes'),
                'title' => __('Classes'),
                'name'  => 'cat_col',
                'after_element_html' => '
                <p class="nm"><small>Example: dropdown</small></p>',
                'value' => 'dropdown',
            ]
        );

        $fieldset->addField('ext_content', 'select',
            [
                'label' => __('Extra Content'),
                'title' => __('Extra Content'),
                'name'  => 'ext_content',
                'values' => $this->_blocks->toOptionArray(),
                'after_element_html' => '<p class="nm"><small>Content Dropdown while hover</small></p>',
            ]
        );

        /* Check is single store mode */
        if (!$this->_storeManager->isSingleStoreMode()) {
            $field = $fieldset->addField(
                'stores',
                'multiselect',
                [
                    'name' => 'stores[]',
                    'label' => __('Store View'),
                    'title' => __('Store View'),
                    'required' => true,
                    'value' => 0,
                    'values' => $this->_systemStore->getStoreValuesForForm(false, true)
                ]
            );
            $renderer = $this->getLayout()->createBlock(FieldsetElement::class);
            $field->setRenderer($renderer);
        } else {
            $fieldset->addField(
                'stores',
                'hidden',
                ['name' => 'stores[]', 'value' => $this->_storeManager->getStore(true)->getId()]
            );
            $model->setStoreId($this->_storeManager->getStore(true)->getId());
        }

        $fieldset->addField('order', 'text',
            [
                'label' => __('Order'),
                'title' => __('Order'),
                'name' => 'order',
                'value' => 0,
                'after_element_html' => 'Sort Numbers Ascending'
            ]
        );

        $fieldset->addField('status', 'select',
            [
                'label' => __('Status'),
                'title' => __('Status'),
                'name' => 'status',
                'options' => Status::getAvailableStatuses(),
            ]
        );

        $form->addValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Retrieve the magicmenu entity currently being edited.
     *
     * @return MagicmenuInterface
     */
    public function getMagicmenu()
    {
        if ($this->magicmenu === null) {
            $model = $this->magicmenuFactory->create();
            $id = $this->getRequest()->getParam('magicmenu_id');

            if ($id) {
                try {
                    $model = $this->magicmenuRepository->getById($id);
                } catch (NoSuchEntityException $exception) {
                    $model = $this->magicmenuFactory->create();
                }
            }

            $model->setStoreViewId($this->getRequest()->getParam('store'));

            $formData = $this->_backendSession->getFormData(true);
            if (!empty($formData)) {
                $model->setData($formData);
            }

            $this->magicmenu = $model;
        }

        return $this->magicmenu;
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    public function getPageTitle()
    {
        return $this->getMagicmenu()->getId()
            ? __("Edit Extra Menu '%1'", $this->_escaper->escapeHtml((string)$this->getMagicmenu()->getName()))
            : __('New Extra Menu');
    }

    /**
     * Prepare label for tab.
     *
     * @return string
     */
    public function getTabLabel()
    {
        return __('General Information');
    }

    /**
     * Prepare title for tab.
     *
     * @return string
     */
    public function getTabTitle()
    {
        return $this->getTabLabel();
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }
}
