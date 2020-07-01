<?php
namespace Jotadevs\OnzePlexConnector\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class InstallSchema implements InstallSchemaInterface
{

    /**
     * @inheritDoc
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        $table = $setup->getConnection()->newTable(
            $setup->getTable('jotadevs_op_product')
        )->addColumn(
            'id',
            Table::TYPE_INTEGER,
            null,
            ['nullable' => false, 'primary' => true, 'identity' => true, 'auto-increment' => true],
            'Id del Producto en Magento'
        )->addColumn(
            'sku',
            Table::TYPE_TEXT,
            10,
            ['nullable' => false],
            'Codigo del producto en ERP'
        )->addIndex(
            $setup->getIdxName('jotadevs_op_product', ['sku']),
            ['sku']
        )->setComment(
            'Table of Products Sync from ERP'
        );
        $setup->getConnection()->createTable($table);

        $table = $setup->getConnection()->newTable(
            $setup->getTable('jotadevs_op_operation')
        )
            ->addColumn(
                'id',
                Table::TYPE_INTEGER,
                null,
                [
                    'nullable' => false,
                    'primary' => true,
                    'identity' => true,
                    'auto-increment' => true
                ]
            )->addColumn(
                'name',
                Table::TYPE_TEXT,
                50,
                [
                    'nullable' => false,
                ],
                'Nombre de la operacion ejecutada'
            )->addColumn(
                'message',
                Table::TYPE_TEXT,
                200,
                [
                    'nullable' => false
                ],
                'Mensaje de la operacion'
            )->addColumn(
                'create_at',
                Table::TYPE_TIMESTAMP,
                null,
                [
                    'nullable' => false,
                    'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT
                ]
            )->addIndex(
                $setup->getIdxName('jotadevs_op_operation', ['id']),
                ['id']
            )->setComment(
                'Table of sync operations'
            );
        $setup->getConnection()->createTable($table);

        $table = $setup->getConnection()->newTable(
            $setup->getTable('jotadevs_op_order')
        )
            ->addColumn(
                'id',
                Table::TYPE_INTEGER,
                null,
                [
                    'nullable' => false,
                    'primary' => true,
                    'identity' => true,
                    'auto-increment' => true
                ]
            )->addIndex(
                $setup->getIdxName('jotadevs_op_order', ['id']),
                ['id']
            )->setComment(
                'Table of order to post on OnzePlex'
            );
        $setup->getConnection()->createTable($table);
        $setup->endSetup();
    }
}