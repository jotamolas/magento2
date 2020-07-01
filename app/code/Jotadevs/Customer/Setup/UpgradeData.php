<?php
namespace Jotadevs\Customer\Setup;

use Magento\Customer\Model\Customer;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;

class UpgradeData implements UpgradeDataInterface
{
    /**
    * @var EavSetupFactory
    */
    private $eavSetupFactory;

    /**
    * UpgradeData constructor.
    * @param EavSetupFactory $eavSetupFactory
    */

    private $eavConfig;
    private $attributeResource;

    public function __construct(
        EavSetupFactory $eavSetupFactory,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Customer\Model\ResourceModel\Attribute $attributeResource
    ) {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->eavConfig = $eavConfig;
        $this->attributeResource = $attributeResource;
    }

    /**
    * @param ModuleDataSetupInterface $setup
    * @param ModuleContextInterface $context
    */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '1.0.2') < 0) {
            $this->upgradeSchema201($setup);
        }

        $setup->endSetup();
    }

    /**
    * @param ModuleDataSetupInterface $setup
    */
    private function upgradeSchema201(ModuleDataSetupInterface $setup)
    {
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
        $eavSetup->removeAttribute(Customer::ENTITY, "doc");
        $eavSetup->removeAttribute(Customer::ENTITY, "tipo_doc");

        $attributeSetId = $eavSetup->getDefaultAttributeSetId(Customer::ENTITY);
        $attributeGroupId = $eavSetup->getDefaultAttributeGroupId(Customer::ENTITY);

        $eavSetup->addAttribute(Customer::ENTITY, 'doc', [
            'type' => 'integer',
            'label' => 'Doc. Nacional de Identidad',
            'input' => 'text',
            'required' => true,
            'visible' => true,
            'user_defined' => true,
            'sort_order' => 990,
            'position' => 990,
            'system' => 0,
        ]);
        $attribute = $this->eavConfig->getAttribute(Customer::ENTITY, 'doc');
        $attribute->setData('attribute_set_id', $attributeSetId);
        $attribute->setData('attribute_group_id', $attributeGroupId);
        $attribute->setData('used_in_forms', [
            'adminhtml_customer',
            'customer_account_create',
            'customer_account_edit'
        ]);

        $this->attributeResource->save($attribute);
        $eavSetup->addAttribute(Customer::ENTITY, 'tipo_doc', [
            'type' => 'varchar',
            'label' => 'Tipo de Documento',
            'input' => 'select',
            'source' => 'Jotadevs\Customer\Model\Source\CustomerTipoDocSource',
            'required' => true,
            'visible' => true,
            'user_defined' => true,
            'sort_order' => 990,
            'position' => 990,
            'system' => 0,
            'global' => \Magento\Catalog\Model\ResourceModel\Eav\Attribute::SCOPE_STORE,
            'searchable' => false,
            'filterable' => false
        ]);
        $attribute = $this->eavConfig->getAttribute(Customer::ENTITY, 'tipo_doc');
        $attribute->setData('attribute_set_id', $attributeSetId);
        $attribute->setData('attribute_group_id', $attributeGroupId);
        $attribute->setData('used_in_forms', [
            'adminhtml_customer',
            'customer_account_create',
            'customer_account_edit'
        ]);
        $this->attributeResource->save($attribute);
    }
}
