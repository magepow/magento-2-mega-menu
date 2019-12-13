<?php
/**
 * Magiccart 
 * @category    Magiccart 
 * @copyright   Copyright (c) 2014 Magiccart (http://www.magiccart.net/) 
 * @license     http://www.magiccart.net/license-agreement.html
 * @Author: Magiccart<team.magiccart@gmail.com>
 * @@Create Date: 2016-01-07 22:10:30
 * @@Modify Date: 2016-03-29 18:20:18
 * @@Function:
 */

namespace Magiccart\Magicmenu\Model\System\Config;

use Magento\Cms\Model\ResourceModel\Block\CollectionFactory;

class Blocks implements \Magento\Framework\Option\ArrayInterface  // extends \Magento\Catalog\Model\Category\Attribute\Source\Page
{

    /**
     * Block collection factory
     *
     * @var CollectionFactory
     */
    protected $_blockCollectionFactory;
    
    protected $_request;

    protected $_blocks;

    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        CollectionFactory $blockCollectionFactory
        )
    {
        $this->_request = $request;
        $this->_blockCollectionFactory = $blockCollectionFactory;
    }

    public function toOptionArray() {
        if (!$this->_blocks) {
            $store = $this->_request->getParam('store');
            $collection = $this->_blocks = $this->_blockCollectionFactory->create();
            $collection->addStoreFilter($store)->load();
            $options = array();
            foreach ($collection as $block) {
                $options[$block->getIdentifier()] = $block->getTitle();
            }
            array_unshift($options, ['value' => '', 'label' => __('Please select a static block.')]);
            $this->_blocks = $options;
        }
        return $this->_blocks;
    }
 
}
