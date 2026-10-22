<?php
/**
 * Copyright © Magiccart. All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Magiccart\Magicmenu\Block\Adminhtml\Extra;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Helper\Data as BackendHelper;
use Magento\Framework\DataObject;
use Magiccart\Magicmenu\Model\ResourceModel\Magicmenu\CollectionFactory as MagicmenuCollectionFactory;
use Magiccart\Magicmenu\Model\Status;
use Magiccart\Magicmenu\Model\System\Config\Visibility;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var MagicmenuCollectionFactory
     */
    protected $magicmenuCollectionFactory;


    /**
     * @param Context $context
     * @param BackendHelper $backendHelper
     * @param MagicmenuCollectionFactory $magicmenuCollectionFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        BackendHelper $backendHelper,
        MagicmenuCollectionFactory $magicmenuCollectionFactory,
        array $data = []
    ) {
        $this->magicmenuCollectionFactory = $magicmenuCollectionFactory;

        parent::__construct($context, $backendHelper, $data);
    }

    protected function _construct()
    {
        parent::_construct();
        $this->setId('magicmenuGrid');
        $this->setDefaultSort('magicmenu_id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    protected function _prepareCollection()
    {
        $store = $this->getRequest()->getParam('store');
        $collection = $this->magicmenuCollectionFactory->create();
        if($store) $collection->addFieldToFilter('stores',array( array('finset' => 0), array('finset' => $store)));
        $collection->addFieldToFilter('extra', 1);
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * @return $this
     */
    protected function _prepareColumns()
    {
        // $this->addColumn(
        //     'magicmenu_id',
        //     [
        //         'header' => __('Magicmenu ID'),
        //         'type' => 'number',
        //         'index' => 'magicmenu_id',
        //         'header_css_class' => 'col-id',
        //         'column_css_class' => 'col-id',
        //     ]
        // );

        $this->addColumn(
            'name',
            [
                'header' => __('Name'),
                'type' => 'text',
                'index' => 'name',
                'header_css_class' => 'col-name',
                'column_css_class' => 'col-name',
            ]
        );

        $this->addColumn(
            'link',
            [
                'header' => __('Link'),
                'type' => 'text',
                'index' => 'link',
                'header_css_class' => 'col-link',
                'column_css_class' => 'col-link',
            ]
        );

        $this->addColumn(
            'ext_content',
            [
                'header' => __('Dropdown Content'),
                'type' => 'text',
                'index' => 'ext_content',
                'renderer' => 'Magiccart\Magicmenu\Block\Adminhtml\Helper\Renderer\Grid\Block',
                'header_css_class' => 'col-link',
                'column_css_class' => 'col-link',
            ]
        );

        // if (!$this->_storeManager->isSingleStoreMode()) {
        //     $this->addColumn(
        //         'stores',
        //         [
        //             'header' => __('Store View'),
        //             'index' => 'stores',
        //             'type' => 'store',
        //             'store_all' => true,
        //             'store_view' => true,
        //             'sortable' => false,
        //             'filter_condition_callback' => [$this, '_filterStoreCondition']
        //         ]
        //     );
        // }

        $this->addColumn(
            'order',
            [
                'header' => __('Order'),
                'type' => 'text',
                'index' => 'order',
                'header_css_class' => 'col-order',
                'column_css_class' => 'col-order',
            ]
        );

        $this->addColumn(
            'visibility',
            [
                'header' => __('Visibility Location'),
                'index' => 'visibility',
                'type' => 'options',
                'options' => Visibility::getVisibility(),
                'header_css_class' => 'col-visibility',
                'column_css_class' => 'col-visibility',
            ]
        );

        $this->addColumn(
            'status',
            [
                'header' => __('Status'),
                'index' => 'status',
                'type' => 'options',
                'options' => Status::getAvailableStatuses(),
            ]
        );

        $this->addColumn(
            'edit',
            [
                'header' => __('Edit'),
                'type' => 'action',
                'getter' => 'getId',
                'actions' => [
                    [
                        'caption' => __('Edit'),
                        'url' => ['base' => '*/*/edit'],
                        'field' => 'magicmenu_id',
                    ],
                ],
                'filter' => false,
                'sortable' => false,
                'index' => 'stores',
                'header_css_class' => 'col-action',
                'column_css_class' => 'col-action',
            ]
        );
        $this->addExportType('*/*/exportCsv', __('CSV'));
        $this->addExportType('*/*/exportXml', __('XML'));
        $this->addExportType('*/*/exportExcel', __('Excel'));

        return parent::_prepareColumns();
    }

    /**
     * @return $this
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('magicmenu_id');
        $this->getMassactionBlock()->setFormFieldName('magicmenu');

        $this->getMassactionBlock()->addItem(
            'delete',
            [
                'label' => __('Delete'),
                'url' => $this->getUrl('magicmenu/*/massDelete'),
                'confirm' => __('Are you sure?'),
            ]
        );

        $statuses = Status::getAvailableStatuses();

        array_unshift($statuses, ['label' => '', 'value' => '']);
        $this->getMassactionBlock()->addItem(
            'status',
            [
                'label' => __('Change status'),
                'url' => $this->getUrl('magicmenu/*/massStatus', ['_current' => true]),
                'additional' => [
                    'visibility' => [
                        'name' => 'status',
                        'type' => 'select',
                        'class' => 'required-entry',
                        'label' => __('Status'),
                        'values' => $statuses,
                    ],
                ],
            ]
        );

        return $this;
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', ['_current' => true]);
    }

    /**
     * get row url
     * @param  object $row
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl(
            '*/*/edit',
            ['magicmenu_id' => $row->getId()]
        );
    }
}
