<?php

namespace Jotadevs\RedCustoms\Setup;

use Magento\Catalog\Model\Product;
use Magento\Customer\Model\Customer;
use Magento\Eav\Model\Config;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class InstallData implements InstallDataInterface
{
    private $eavSetupFactory;
    private $eavConfig;

    public function __construct(EavSetupFactory $eavSetupFactory, Config $eavConfig)
    {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->eavConfig = $eavConfig;
    }

    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        /* @var $eavSetup \Magento\Eav\Setup\EavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
        $eavSetup->addAttribute(
            Customer::ENTITY,
            'dni',
            [
                'type' => 'varchar',
                'label' => 'D.N.I.',
                'input' => 'text',
                'required' => false,
                'visible' => true,
                'user_defined' => true,
                'position' => 999,
                'system' => 0
            ]
        );
        $eavSetup->addAttribute(
            Product::ENTITY,
            'laboratorio',
            [
              'type' => 'text',
              'label' => 'Laboratorio',
              'input' => 'text',
              'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
              'required' => false,
              'user_defined' => false,
              'comparable' => false,
              'filterable_in_search' => true,
              'filterable' => true,
              'is_filterable_in_grid' => true,
              'is_used_in_grid' => true,
              'searchable' => true,
              'system' => true,
              'use_for_promo_rules' => true,
              'visible_in_advanced_search' => true,
              'visible_on_front' => true,
              'visible' => true
            ]
        );
        $dniAttribute = $this->eavConfig->getAttribute(Customer::ENTITY, 'dni');
        $dniAttribute->setData(
            'used_in_forms',
            ['adminhtml_customer','customer_account_edit']
        );
        $dniAttribute->save();
        $laboratorioAttribute = $this->eavConfig->getAttribute(Product::ENTITY, 'laboratorio');
        $laboratorioAttribute->save();
    }
}
