<?php

namespace Jotadevs\RedCustoms\Setup;
use Magento\Customer\Model\Customer;
use Magento\Eav\Model\Config;
use Magento\Eav\Setup\EavSetup;
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
        $dniAttribute = $this->eavConfig->getAttribute(Customer::ENTITY, 'dni');
        $dniAttribute->setData(
            'used_in_forms',
            ['adminhtml_customer','customer_account_edit']
        );
        $dniAttribute->save();
    }
}
