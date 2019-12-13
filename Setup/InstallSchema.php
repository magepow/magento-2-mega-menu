<?php
/**
 * Magiccart 
 * @category    Magiccart 
 * @copyright   Copyright (c) 2014 Magiccart (http://www.magiccart.net/) 
 * @license     http://www.magiccart.net/license-agreement.html
 * @Author: DOng NGuyen<nguyen@dvn.com>
 * @@Create Date: 2016-01-05 10:40:51
 * @@Modify Date: 2016-03-23 16:23:08
 * @@Function:
 */

namespace Magiccart\Magicmenu\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

class InstallSchema implements InstallSchemaInterface
{
    /**
     * Installs DB schema for a module
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();

        $table = $installer->getConnection()
            ->newTable($installer->getTable('magiccart_magicmenu'))
            ->addColumn(
                'magicmenu_id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'nullable' => false, 'primary' => true],
                'Magicmenu ID'
            )
            ->addColumn('cat_id', Table::TYPE_TEXT, 255, ['nullable' => true], 'Category Id')
            ->addColumn('cat_col', Table::TYPE_TEXT, 255, ['nullable' => true], 'Columns category')
            ->addColumn('cat_proportion', Table::TYPE_INTEGER, null, ['nullable' => false, 'default' => '0'], 'Proportion category')
            ->addColumn('top', Table::TYPE_TEXT, 255, ['nullable' => true], 'Category content top')
            ->addColumn('right', Table::TYPE_TEXT, 255, ['nullable' => true], 'Category content right')
            ->addColumn('right_proportion', Table::TYPE_INTEGER, null, ['nullable' => false, 'default' => '0'], 'Proportion category content right')
            ->addColumn('bottom', Table::TYPE_TEXT, 255, ['nullable' => true], 'Category content bottom')
            ->addColumn('left', Table::TYPE_TEXT, 255, ['nullable' => true], 'Category content right')
            ->addColumn('left_proportion', Table::TYPE_INTEGER, null, ['nullable' => false, 'default' => '0'], 'Proportion category content left')


            ->addColumn('name', Table::TYPE_TEXT, 255, ['nullable' => true], 'Extra menu name')
            ->addColumn('magic_label', Table::TYPE_TEXT, 255, ['nullable' => true], 'Extra menu label')
            ->addColumn('link', Table::TYPE_TEXT, 255, ['nullable' => true], 'Extra menu link')
            ->addColumn('ext_content', Table::TYPE_TEXT, 1024, ['nullable' => true], 'Extra menu content')
            ->addColumn('extra', Table::TYPE_SMALLINT, null, ['nullable' => false, 'default' => '0'], 'Is Extra')

            ->addColumn('stores', Table::TYPE_TEXT, 255, ['nullable' => true], 'Stores')
            ->addColumn('order', Table::TYPE_INTEGER, null, ['nullable' => false, 'default' => '0'], 'Order')
            ->addColumn('status', Table::TYPE_SMALLINT, null, ['nullable' => false, 'default' => '1'], 'Status')
            ->setComment('Magiccart Magicmenu');

        $installer->getConnection()->createTable($table);

        $installer->endSetup();
    }

}
