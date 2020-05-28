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
            ['nullable' => false, 'primary' => true],
            'Id del Producto en el ERP'
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
        $setup->endSetup();
    }
}
