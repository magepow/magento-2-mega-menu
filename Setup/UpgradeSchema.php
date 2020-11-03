<?php

declare(strict_types=1);

namespace Magiccart\Magicmenu\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    /** @var SplitDatabaseConnectionProvider */
    private $connectionProvider;

    public function __construct(SplitDatabaseConnectionProvider $connectionProvider)
    {
        $this->connectionProvider = $connectionProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        /**
         * CURRENT VERSION LOWER THAN 2.1.0
         */
        if (version_compare($context->getVersion(), '2.1.0') == -1) {
            $this->addExtraMenuVisibility($installer);
        }


        $installer->endSetup();
    }

    /**
     * @param $installer
     */
    private function addExtraMenuVisibility(SchemaSetupInterface $installer)
    {
        $table = $installer
            ->getConnection()
            ->getTable($installer->getTable('magiccart_magicmenu'))
            ->addColumn(
                'visibility',
                Table::TYPE_INTEGER, null,
                ['unsigned' => true, 'nullable' => false, 'default' => 0], 'Extra Menu Visibility');

        $installer->endSetup();
    }
}
