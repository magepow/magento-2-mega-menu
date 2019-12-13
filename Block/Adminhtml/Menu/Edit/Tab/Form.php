<?php
/**
 * Magiccart 
 * @category    Magiccart 
 * @copyright   Copyright (c) 2014 Magiccart (http://www.magiccart.net/) 
 * @license     http://www.magiccart.net/license-agreement.html
 * @Author: DOng NGuyen<nguyen@dvn.com>
 * @@Create Date: 2016-01-05 10:40:51
 * @@Modify Date: 2018-03-22 20:20:56
 * @@Function:
 */

namespace Magiccart\Magicmenu\Block\Adminhtml\Menu\Edit\Tab;

use Magiccart\Magicmenu\Model\Status;
class Form extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * @var \Magento\Framework\DataObjectFactory
     */
    protected $_objectFactory;

    /**
     * @var Magiccart\Magicmenu\Model\System\Config\Blocks
     */    
    protected $_blocks;


    /**
     * @var Magiccart\Magicmenu\Model\System\Config\Category
     */    
    protected $_category;

    /**
     * @var \Magiccart\Magicmenu\Model\Magicmenu
     */

    protected $_magicmenu;

    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\DataObjectFactory $objectFactory,
        \Magento\Store\Model\System\Store $systemStore,
        \Magiccart\Magicmenu\Model\Magicmenu $magicmenu,
        \Magiccart\Magicmenu\Model\System\Config\Blocks $blocks,
        \Magiccart\Magicmenu\Model\System\Config\Category $category,
        array $data = []
    ) {
        $this->_objectFactory = $objectFactory;
        $this->_magicmenu = $magicmenu;
        $this->_blocks = $blocks;
        $this->_category = $category;
        $this->_systemStore = $systemStore;
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
        $model = $this->_coreRegistry->registry('magicmenu');

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $form->setHtmlIdPrefix('magic_');

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Menu Information')]);

        if ($model->getId()) {
            $fieldset->addField('magicmenu_id', 'hidden', ['name' => 'magicmenu_id']);
        }

        $cat = $fieldset->addField('cat_id', 'select',
            [
                'label' => __('Category'),
                'title' => __('Category'),
                'name'  => 'cat_id',
                'values' => $this->_category->toOptionArray(),
                'after_element_html' => '
                    <p class="nm"><small>Select top category</small></p>
                    <p class="nm"><small>Label && Image, Thumbanil add in Catalog > Manager Categories</small></p>
                ',
            ]
        );

        $cat_proportion = $fieldset->addField('cat_proportion', 'text', 
            [
            'label'     => __('Proportion: Category'),
            'class'     => 'validate-greater-than-zero',
            // 'required'  => true,
            'name'      => 'cat_proportion',
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
                'after_element_html' => '
                    <p class="nm"><small>Add Static Block to Top Megamenu</small></p>
                ',
            ]
        );

        $right = $fieldset->addField('right', 'select',
            [
                'label' => __('Block Right'),
                'title' => __('Block Right'),
                'name'  => 'right',
                'values' => $this->_blocks->toOptionArray(),
                'after_element_html' => '
                    <p class="nm"><small>Add Static Block to Right Megamenu</small></p>
                ',
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
            '<p class="nm"><small>Proportion weight</small></p>
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
                'after_element_html' => '
                    <p class="nm"><small>Add Static Block to Bottom Megamenu</small></p>
                ',
            ]
        );

        $left = $fieldset->addField('left', 'select',
            [
                'label' => __('Block Left'),
                'title' => __('Block Left'),
                'name'  => 'left',
                'values' => $this->_blocks->toOptionArray(),
                'after_element_html' => '
                    <p class="nm"><small>Add Static Block to Left Megamenu</small></p>
                ',
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
            '<p class="nm"><small>Proportion weight</small></p>
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
                    'values' => $this->_systemStore->getStoreValuesForForm(false, true)
                ]
            );
            $renderer = $this->getLayout()->createBlock(
                'Magento\Backend\Block\Store\Switcher\Form\Renderer\Fieldset\Element'
            );
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
     * @return mixed
     */
    public function getMagicmenu()
    {
        return $this->_coreRegistry->registry('magicmenu');
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    public function getPageTitle()
    {
        return $this->getMagicmenu()->getId()
            ? __("Edit Menu '%1'", $this->escapeHtml($this->getMagicmenu()->getTitle())) : __('New Menu');
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
