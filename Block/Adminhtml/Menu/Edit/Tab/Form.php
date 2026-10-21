<?php
/**
 * Copyright © Magiccart. All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Magiccart\Magicmenu\Block\Adminhtml\Menu\Edit\Tab;

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
use Magiccart\Magicmenu\Model\System\Config\Category;

class Form extends Generic implements TabInterface
{
    /**
     * @var Blocks
     */
    protected $_blocks;

    /**
     * @var Category
     */
    protected $_category;

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
        Category $category,
        array $data = []
    ) {
        $this->_blocks = $blocks;
        $this->_category = $category;
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

        $fieldset = $form->addFieldset(
            'base_fieldset',
            [
                'legend' => __('Menu Information'),
                'comment' => __(
                    'The width of the mega menu is shared between the category list, the left block and the right block. '
                    . 'Enter the weight of each part, the total of the three weights is used to calculate the width of '
                    . 'every part. For example, Category = 2, Block Left = 1 and Block Right = 0 give 67% of the width '
                    . 'to the categories and 33% to the left block.'
                ),
            ]
        );

        if ($model->getId()) {
            $fieldset->addField('magicmenu_id', 'hidden', ['name' => 'magicmenu_id']);
        }

        $cat = $fieldset->addField('cat_id', 'select',
            [
                'label' => __('Category'),
                'title' => __('Category'),
                'name'  => 'cat_id',
                'values' => $this->_category->toOptionArray(),
                'after_element_html' => '<p class="note">' . $this->_escaper->escapeHtml(
                    __('Select the top category of this menu. The label, the image and the thumbnail of the category are set in Catalog > Manage Categories.')
                ) . '</p>',
            ]
        );

        $cat_proportion = $fieldset->addField('cat_proportion', 'text',
            [
            'label'     => __('Proportion: Category'),
            'class'     => 'validate-greater-than-zero',
            // 'required'  => true,
            'name'      => 'cat_proportion',
            'after_element_html' => '<p class="note">' . $this->_escaper->escapeHtml(
                __(
                    'Weight of the category list inside the mega menu. The higher the value, the wider the category list. '
                    . 'The width is the weight of this field divided by the total weight of Category, Block Left and Block Right.'
                )
            ) . '</p>',
            ]
        );

        // $cat_proportion->setAfterElementHtml(
        //     '<p class="nm"><small>Proportion weight</small></p>
        //     <script type="text/javascript">
        //     require([
        //         "jquery",
        //     ],  function($){
        //             jQuery(document).ready(function($) {
        //                 var map     = "#'.$cat->getHtmlId().'";
        //                 var depend  = "#'.$cat_proportion->getHtmlId().'";                  
        //                 if (!$(map).val()) {$(depend).prop("disabled", true); }
        //                 $(map).change(function() {
        //                     if (!$(map).val()){
        //                         $(depend).val(0).closest(".field-' . $cat_proportion->getName() . '").hide();
        //                     } 
        //                     else {
        //                         $(depend).prop("disabled", false).closest(".field-' . $cat_proportion->getName() . '").show();
        //                     }
        //                 });
        //             })
        //     })
        //     </script>
        //     '
        // );

        $cat_col= $fieldset->addField('cat_col', 'text', 
            [
            'label'     => __('Columns category'),
            'class'     => 'validate-greater-than-zero',
            // 'required'  => true,
            'name'      => 'cat_col',
            ]
        );

        $top = $fieldset->addField('top', 'select',
            [
                'label' => __('Block Top'),
                'title' => __('Block Top'),
                'name'  => 'top',
                'values' => $this->_blocks->toOptionArray(),
                'after_element_html' => '<p class="note">' . $this->_escaper->escapeHtml(
                    __('Add a static block displayed on the top of the mega menu.')
                ) . '</p>',
            ]
        );

        $right = $fieldset->addField('right', 'select',
            [
                'label' => __('Block Right'),
                'title' => __('Block Right'),
                'name'  => 'right',
                'values' => $this->_blocks->toOptionArray(),
                'after_element_html' => '<p class="note">' . $this->_escaper->escapeHtml(
                    __('Add a static block displayed on the right of the mega menu.')
                ) . '</p>',
            ]
        );

        $right_proportion = $fieldset->addField('right_proportion', 'text',
            [
            'label'     => __('Proportion: Block Right'),
            'class'     => 'validate-zero-or-greater',
            // 'required'  => true,
            'name'      => 'right_proportion',
            ]
        );

        $right_proportion->setAfterElementHtml(
            '<p class="note">' . $this->_escaper->escapeHtml(
                __(
                    'Weight of the block displayed on the right of the mega menu. Set 0 to hide the space of this block. '
                    . 'For example, Category = 2, Block Right = 1 and Block Left = 0 give 33% of the width to this block.'
                )
            ) . '</p>
            <script type="text/javascript">
            require([
                "jquery",
            ],  function($){
                    jQuery(document).ready(function($) {
                        var map     = "#'.$right->getHtmlId().'";
                        var depend  = "#'.$right_proportion->getHtmlId().'";                  
                        if (!$(map).val()) {$(depend).prop("disabled", true); }
                        $(map).change(function() {
                            if (!$(map).val()){
                                $(depend).val(0).closest(".field-' . $right_proportion->getName() . '").hide();
                            } 
                            else {
                                $(depend).prop("disabled", false).closest(".field-' . $right_proportion->getName() . '").show();
                            }
                        });
                    })
            })
            </script>
            '
        );


        $bottom = $fieldset->addField('bottom', 'select',
            [
                'label' => __('Block Bottom'),
                'title' => __('Block Bottom'),
                'name'  => 'bottom',
                'values' => $this->_blocks->toOptionArray(),
                'after_element_html' => '<p class="note">' . $this->_escaper->escapeHtml(
                    __('Add a static block displayed on the bottom of the mega menu.')
                ) . '</p>',
            ]
        );

        $left = $fieldset->addField('left', 'select',
            [
                'label' => __('Block Left'),
                'title' => __('Block Left'),
                'name'  => 'left',
                'values' => $this->_blocks->toOptionArray(),
                'after_element_html' => '<p class="note">' . $this->_escaper->escapeHtml(
                    __('Add a static block displayed on the left of the mega menu.')
                ) . '</p>',
            ]
        );

        $left_proportion = $fieldset->addField('left_proportion', 'text',
            [
            'label'     => __('Proportion: Block Left'),
            'class'     => 'validate-zero-or-greater',
            // 'required'  => true,
            'name'      => 'left_proportion',
            ]
        );

        $left_proportion->setAfterElementHtml(
            '<p class="note">' . $this->_escaper->escapeHtml(
                __(
                    'Weight of the block displayed on the left of the mega menu. Set 0 to hide the space of this block. '
                    . 'For example, Category = 2, Block Left = 1 and Block Right = 0 give 33% of the width to this block.'
                )
            ) . '</p>
            <script type="text/javascript">
            require([
                "jquery",
            ],  function($){
                    jQuery(document).ready(function($) {
                        var map     = "#'.$left->getHtmlId().'";
                        var depend  = "#'.$left_proportion->getHtmlId().'";                  
                        if (!$(map).val()) {$(depend).prop("disabled", true); }
                        $(map).change(function() {
                            if (!$(map).val()){
                                $(depend).val(0).closest(".field-' . $left_proportion->getName() . '").hide();
                            } 
                            else {
                                $(depend).prop("disabled", false).closest(".field-' . $left_proportion->getName() . '").show();
                            }
                        });
                    })
            })
            </script>
            '
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
            ? __("Edit Menu '%1'", $this->_escaper->escapeHtml((string)$this->getMagicmenu()->getName()))
            : __('New Menu');
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
