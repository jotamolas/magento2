<?php

namespace Jotadevs\OnzePlexConnector\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class UpgradeSchema implements \Magento\Framework\Setup\UpgradeSchemaInterface
{

    /**
     * @inheritDoc
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        if (version_compare($context->getVersion(), '1.0.1', '<')) {
            $setup->getConnection()->addColumn(
                $setup->getTable('jotadevs_op_product'),
                'codproduct',
                [
                    'type' => Table::TYPE_TEXT,
                    'nullable' => false,
                    'comment' => 'Codigo del producto en Onze Plex'

                ]
            );
            $setup->getConnection()->addColumn(
                $setup->getTable('jotadevs_op_product'),
                'producto',
                [
                    'type' => Table::TYPE_TEXT,
                    'nullable' => false,
                    'comment' => 'Nombre del producto'

                ]
            );
            $setup->getConnection()->addColumn(
                $setup->getTable('jotadevs_op_product'),
                'rubro',
                [
                    'type' => Table::TYPE_TEXT,
                    'nullable' => false,
                    'comment' => 'Rubro del producto'

                ]
            );
            $setup->getConnection()->addColumn(
                $setup->getTable('jotadevs_op_product'),
                'subrubro',
                [
                    'type' => Table::TYPE_TEXT,
                    'nullable' => false,
                    'comment' => 'SubRubro del producto'

                ]
            );
            $setup->getConnection()->addColumn(
                $setup->getTable('jotadevs_op_product'),
                'idrubro',
                [
                    'type' => Table::TYPE_INTEGER,
                    'nullable' => false,
                    'comment' => 'Id rubro del producto'

                ]
            );
            $setup->getConnection()->addColumn(
                $setup->getTable('jotadevs_op_product'),
                'idsubrubro',
                [
                    'type' => Table::TYPE_INTEGER,
                    'nullable' => false,
                    'comment' => 'Id subrubro del producto'

                ]
            );
            $setup->getConnection()->addColumn(
                $setup->getTable('jotadevs_op_product'),
                'precio',
                [
                    'type' => Table::TYPE_DECIMAL,
                    'nullable' => false,
                    'scale' => 2,
                    'comment' => 'Precio del producto'

                ]
            );
            $setup->getConnection()->addColumn(
                $setup->getTable('jotadevs_op_product'),
                'totaldescuento',
                [
                    'type' => Table::TYPE_DECIMAL,
                    'nullable' => false,
                    'scale' => 2,
                    'comment' => 'Descuento del producto'

                ]
            );
            $setup->getConnection()->addColumn(
                $setup->getTable('jotadevs_op_product'),
                'stock',
                [
                    'type' => Table::TYPE_INTEGER,
                    'nullable' => false,
                    'comment' => 'Stock del producto'

                ]
            );
        }
        if (version_compare($context->getVersion(), '1.0.2', '<')) {
            $setup->getConnection()->addColumn(
                $setup->getTable('jotadevs_op_product'),
                'create_at',
                [
                    'type' => Table::TYPE_TIMESTAMP,
                    'nullable' => false,
                    'comment' => 'Fecha de primer importación desde el ERP',
                    'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT
                ]
            );
            $setup->getConnection()->addColumn(
                $setup->getTable('jotadevs_op_product'),
                'update_at',
                [
                    'type' => Table::TYPE_TIMESTAMP,
                    'nullable' => false,
                    'comment' => 'Fecha de actualización',
                    'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE

                ]
            );
        }
        if (version_compare($context->getVersion(), '1.0.3', '<')) {
            $setup->getConnection()->changeColumn(
                $setup->getTable('jotadevs_op_product'),
                'create_at',
                'create_at',
                [
                    'type' => Table::TYPE_TIMESTAMP,
                    'nullable' => false,
                    'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT
                ]
            );
            $setup->getConnection()->changeColumn(
                $setup->getTable('jotadevs_op_product'),
                'update_at',
                'update_at',
                [
                    'type' => Table::TYPE_TIMESTAMP,
                    'nullable' => false,
                    'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE
                ]
            );
        }
        if (version_compare($context->getVersion(), '1.0.4', '<')) {
            $setup->getConnection()->addColumn(
                $setup->getTable('jotadevs_op_product'),
                'last_id',
                [
                    'type' => Table::TYPE_INTEGER,
                    'nullable' => true,
                    'comment' => 'Id de ultimo elemento de la operación ejecutada'
                ]
            );
            $setup->getConnection()->addColumn(
                $setup->getTable('jotadevs_op_product'),
                'code',
                [
                    'type' => Table::TYPE_TEXT,
                    'size' => 4,
                    'nullable' => false,
                    'comment' => 'Código de operacion'
                ]
            );
        }
        if (version_compare($context->getVersion(), '1.0.5', '<')) {
            $setup->getConnection()->addColumn(
                $setup->getTable('jotadevs_op_operation'),
                'last_id',
                [
                    'type' => Table::TYPE_INTEGER,
                    'nullable' => true,
                    'comment' => 'Id de ultimo elemento de la operación ejecutada'
                ]
            );
            $setup->getConnection()->addColumn(
                $setup->getTable('jotadevs_op_product'),
                'code',
                [
                    'type' => Table::TYPE_TEXT,
                    'size' => 4,
                    'nullable' => false,
                    'comment' => 'Código de operacion'
                ]
            );
            $setup->getConnection()->dropColumn(
                $setup->getTable('jotadevs_op_product'),
                'last_id'
            );
            $setup->getConnection()->dropColumn(
                $setup->getTable('jotadevs_op_product'),
                'code'
            );
        }
        if (version_compare($context->getVersion(), '1.0.6', '<')) {
            $setup->getConnection()->dropColumn(
                $setup->getTable('jotadevs_op_product'),
                'code'
            );
            $setup->getConnection()->addColumn(
                $setup->getTable('jotadevs_op_operation'),
                'code',
                [
                    'type' => Table::TYPE_TEXT,
                    'size' => 4,
                    'nullable' => false,
                    'comment' => 'Código de operacion'
                ]
            );
        }
        if (version_compare($context->getVersion(), '1.0.7', '<')) {
            $setup->getConnection()->addColumn(
                $setup->getTable('jotadevs_op_product'),
                'id_magento',
                [
                    'type' => Table::TYPE_INTEGER,
                    'nullable' => true,
                    'comment' => 'Codigo del producto en Magento para tomar la trazabilidad'

                ]
            );
            $setup->getConnection()->addColumn(
                $setup->getTable('jotadevs_op_rubros'),
                'id_magento',
                [
                    'type' => Table::TYPE_INTEGER,
                    'nullable' => true,
                    'comment' => 'Codigo de la Categoria en Magento para tomar la trazabilidad'

                ]
            );
        }
        if (version_compare($context->getVersion(), '1.0.8', '<')) {
            $setup->getConnection()->addColumn(
                $setup->getTable('jotadevs_op_product'),
                'is_synchronized',
                [
                    'type' => Table::TYPE_BOOLEAN,
                    'size' => null,
                    'nullable' => false,
                    'default' => false,
                    'comment' => 'Informa si el producto esta sincronizado en Magento'

                ]
            );
        }
        if (version_compare($context->getVersion(), '1.0.9', '<')) {
            $setup->getConnection()->addColumn(
                $setup->getTable('jotadevs_op_rubros'),
                'is_plex_group',
                [
                    'type' => Table::TYPE_BOOLEAN,
                    'size' => null,
                    'nullable' => false,
                    'default' => false,
                    'comment' => 'Informa si se trata de un Grupo de Onex Plex'

                ]
            );
        }
        if (version_compare($context->getVersion(), '1.1.0', '<')) {
            $setup->getConnection()->addColumn(
                $setup->getTable('jotadevs_op_product'),
                'grupo',
                [
                    'type' => Table::TYPE_TEXT,
                    'nullable' => true,
                    'default' => null,
                    'comment' => 'Grupo del producto'

                ]
            );
            $setup->getConnection()->addColumn(
                $setup->getTable('jotadevs_op_product'),
                'idgrupo',
                [
                    'type' => Table::TYPE_INTEGER,
                    'nullable' => true,
                    'comment' => 'Id grupo del producto',
                    'default' => null

                ]
            );
        }
        if (version_compare($context->getVersion(), '1.1.1', '<')) {
            $setup->getConnection()->dropColumn(
                $setup->getTable('jotadevs_op_product'),
                'grupo'
            );
            $setup->getConnection()->addColumn(
                $setup->getTable('jotadevs_op_product'),
                'grupo',
                [
                    'type' => Table::TYPE_TEXT,
                    'nullable' => true,
                    'default' => null,
                    'comment' => 'Grupo del producto'

                ]
            );
            $setup->getConnection()->dropColumn(
                $setup->getTable('jotadevs_op_product'),
                'idgrupo'
            );
            $setup->getConnection()->addColumn(
                $setup->getTable('jotadevs_op_product'),
                'idgrupo',
                [
                    'type' => Table::TYPE_INTEGER,
                    'nullable' => true,
                    'comment' => 'Id grupo del producto',
                    'default' => null

                ]
            );
        }
        if (version_compare($context->getVersion(), '1.1.2', '<')) {
            $setup->getConnection()->addColumn(
                $setup->getTable('jotadevs_op_order'),
                'id_magento',
                [
                    'type' => Table::TYPE_INTEGER,
                    'nullable' => false,
                    'comment' => 'Id de la orden en Magento.'

                ]
            );
            $setup->getConnection()->addColumn(
                $setup->getTable('jotadevs_op_order'),
                'id_plex',
                [
                    'type' => Table::TYPE_INTEGER,
                    'nullable' => false,
                    'comment' => 'Id de la orden en Onze Plex.'
                ]
            );
            $setup->getConnection()->addColumn(
                $setup->getTable('jotadevs_op_order'),
                'is_synchronized',
                [
                    'type' => Table::TYPE_BOOLEAN,
                    'size' => null,
                    'nullable' => false,
                    'default' => false,
                    'comment' => 'Informa si la orden fué sincronizada en Plex'

                ]
            );
            $setup->getConnection()->addColumn(
                $setup->getTable('jotadevs_op_order'),
                'create_at',
                [
                    'type' => Table::TYPE_TIMESTAMP,
                    'nullable' => false,
                    'comment' => 'Fecha de primer importación desde Magento',
                    'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT,
                ]
            );
            $setup->getConnection()->addColumn(
                $setup->getTable('jotadevs_op_order'),
                'update_at',
                [
                    'type' => Table::TYPE_TIMESTAMP,
                    'nullable' => false,
                    'comment' => 'Fecha de actualización',
                    'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE
                ]
            );
        }
        if (version_compare($context->getVersion(), '1.1.3', '<')) {
            $setup->getConnection()->changeColumn(
                $setup->getTable('jotadevs_op_order'),
                'id_plex',
                'id_plex',
                [
                    'type' => Table::TYPE_TEXT,
                    'nullable' => false,
                    'default' => false
                ]
            );
            $setup->getConnection()->addColumn(
                $setup->getTable('jotadevs_op_order'),
                'is_payment_informed',
                [
                    'type' => Table::TYPE_BOOLEAN,
                    'size' => null,
                    'nullable' => false,
                    'default' => false,
                    'comment' => 'Informa si el pago de la orden fue sincronizada en Plex'

                ]
            );
        }
        if (version_compare($context->getVersion(), '1.1.4', '<')) {
            $setup->getConnection()->addColumn(
                $setup->getTable('jotadevs_op_product'),
                'id_laboratorio',
                [
                    'type' => Table::TYPE_INTEGER,
                    'size' => null,
                    'nullable' => true,
                    'default' => false,
                    'comment' => 'Informa el Id de laboratorio del producto'

                ]
            );
            $setup->getConnection()->addColumn(
                $setup->getTable('jotadevs_op_laboratorio'),
                'id_plex',
                [
                    'type' => Table::TYPE_INTEGER,
                    'size' => null,
                    'nullable' => false,
                    'comment' => 'Informa el Id de laboratorio en plex'
                ]
            );
        }
        if (version_compare($context->getVersion(), '1.1.5', '<')) {
            $setup->getConnection()->addColumn(
                $setup->getTable('jotadevs_op_product'),
                'is_op_enabled',
                [
                    'type' => Table::TYPE_BOOLEAN,
                    'nullable' => false,
                    'comment' => 'Indica si el producto fue habilitado o deshabilitado en Plex'

                ]
            );
            $setup->getConnection()->addColumn(
                $setup->getTable('jotadevs_op_product'),
                'observations',
                [
                    'type' => Table::TYPE_TEXT,
                    'nullable' => true,
                    'size' => 500,
                    'comment' => 'Observaciones de la importacion/actualización'

                ]
            );
        }
        if (version_compare($context->getVersion(), '1.1.6', '<')) {
            $setup->getConnection()->addColumn(
                $setup->getTable('jotadevs_op_product'),
                'codebar',
                [
                    'type' => Table::TYPE_TEXT,
                    'nullable' => true,
                    'comment' => 'Codigo EAN principal del Producto en Onze Plex'

                ]
            );
            $setup->getConnection()->addColumn(
                $setup->getTable('jotadevs_op_product'),
                'codesbar',
                [
                    'type' => Table::TYPE_TEXT,
                    'nullable' => true,
                    'comment' => 'Codigos EAN secundarios del Producto en Onze Plex'

                ]
            );
        }
        $setup->endSetup();
    }

}
