<?php
/**
 * Magiccart 
 * @category    Magiccart 
 * @copyright   Copyright (c) 2014 Magiccart (http://www.magiccart.net/) 
 * @license     http://www.magiccart.net/license-agreement.html
 * @Author: Magiccart<team.magiccart@gmail.com>
 * @@Create Date: 2016-01-07 22:10:30
 * @@Modify Date: 2016-03-29 19:16:38
 * @@Function:
 */

namespace Magiccart\Magicmenu\Model\System\Config;

class Category implements \Magento\Framework\Option\ArrayInterface
{

    /**
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    protected $_categoryFactory;

    /**
     * @var \Magento\Catalog\Model\Config\Source\Category
     */
    protected $_category;  

    protected $_request;

    protected $_storeManager;

    protected  $_options = array();

    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Catalog\Model\Config\Source\Category $category
    )
    {
        $this->_request = $request;
        $this->_storeManager = $storeManager;
        $this->_categoryFactory = $categoryFactory;
        $this->_category = $category;
    }

    public function toOptionArray() {

        if(!$this->_options){
            $store = $this->_request->getParam('store');
            if(!$store) $categories = $this->_category->toOptionArray();
            else {
                $rootCategoryId = $this->_storeManager->getStore($store)->getRootCategoryId();
                $label = $this->_categoryFactory->create()->load($rootCategoryId)->getName();
                $categories = array(array('value' => $rootCategoryId, 'label' => $label));
            }
            $options = array();
            foreach ($categories as $category) {
                if($category['value']) {
                    $rootOption = array('label' => $category['label']);
                    $_categories = $this->_categoryFactory->create()->getCategories($category['value']);
                    $childOptions = array();
                    foreach ($_categories as $_category) {
                        $childOptions[] = array(
                            'label' => $_category->getName(),
                            'value' => $_category->getId()
                        );
                    }
                    $rootOption['value'] = $childOptions;
                    $options[] = $rootOption;
                }
            }
            $this->_options = $options;      
        }

        return $this->_options;
    }
 
    // public function toOptionArray() { // show all category
    // 
    //     if(!$this->_options){
    //         $categories = $this->_category->toOptionArray();
    //         $options = array();
    //         foreach ($categories as $category) {
    //             if($category['value']) {
    //                 $rootOption = array('label' => $category['label']);
    //                 $_categories = $this->_categoryFactory->create()->getCategories($category['value']);
    //                 $childOptions = array();
    //                 foreach ($_categories as $_category) {
    //                     $childOptions[] = array(
    //                         'label' => $_category->getName(),
    //                         'value' => $_category->getId()
    //                     );
    //                 }
    //                 $rootOption['value'] = $childOptions;
    //                 $options[] = $rootOption;
    //             }
    //         }
    //         $this->_options = $options;      
    //     }

    //     return $this->_options;
    // }

}
