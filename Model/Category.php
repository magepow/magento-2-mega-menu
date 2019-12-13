<?php
/**
 * Magiccart 
 * @category    Magiccart 
 * @copyright   Copyright (c) 2014 Magiccart (http://www.magiccart.net/) 
 * @license     http://www.magiccart.net/license-agreement.html
 * @Author: DOng NGuyen<nguyen@dvn.com>
 * @@Create Date: 2016-01-11 23:15:05
 * @@Modify Date: 2016-03-04 18:51:16
 * @@Function:
 */

namespace Magiccart\Magicmenu\Model;

class Category extends \Magento\Catalog\Model\Category
{
	protected function _construct()
    {
        $this->_init('catalog/category');
    }
}
