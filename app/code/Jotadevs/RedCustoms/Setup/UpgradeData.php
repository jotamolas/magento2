<?php

namespace Jotadevs\RedCustoms\Setup;

use Magento\Catalog\Model\Product;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\ResourceModel\Attribute;
use Magento\Eav\Model\Config;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Sales\Model\Order\Status;

class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var EavSetup
     */
    private $eavSetupFactory;

    /**
     * UpgradeData constructor.
     * @param EavSetupFactory $eavSetupFactory
     */

    private $eavConfig;

    private $attributeResource;

    /** @var Status Magento\Sales\Model\Order\Status */
    private $orderStatus;

    public function __construct(
        EavSetupFactory $eavSetupFactory,
        Config $eavConfig,
        Attribute $attributeResource,
        Status $orderStatus
    ) {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->eavConfig = $eavConfig;
        $this->attributeResource = $attributeResource;
        $this->orderStatus = $orderStatus;
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
        if (version_compare($context->getVersion(), '1.0.3') < 0) {
            $this->upgradeOrderStatus103();
        }
        if (version_compare($context->getVersion(), '1.0.4') < 0) {
            $this->upgradeOrderStatus104();
        }
        if (version_compare($context->getVersion(), '1.0.5') < 0) {
            $this->upgradeProductSchema202($setup);
        }

       /* if (version_compare($context->getVersion(), '1.0.6') < 0) {
            $this->upgradeProductSchema203($setup);
        }*/

        $setup->endSetup();
    }

    /**
     * @param ModuleDataSetupInterface $setup
     */
    private function upgradeSchema201(ModuleDataSetupInterface $setup)
    {
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
        $eavSetup->removeAttribute(Customer::ENTITY, "dni");

        $attributeSetId = $eavSetup->getDefaultAttributeSetId(Customer::ENTITY);
        $attributeGroupId = $eavSetup->getDefaultAttributeGroupId(Customer::ENTITY);

        $eavSetup->addAttribute(Customer::ENTITY, 'doc', [
            'type' => 'int',
            'label' => 'Documento de Identidad',
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
    }

    private function upgradeOrderStatus103()
    {
        $data['status'] = 'sync_plex';
        $data['label'] = 'Synced with Plex';
        $data['store_labels'] = ['1' => 'sync_plex'];
        $orderStatus = $this->orderStatus->setData($data)->setStatus($data['status']);
        $orderStatus->save();
    }

    private function upgradeOrderStatus104()
    {
        $data['status'] = 'prepared_sync_plex';
        $data['label'] = 'Prepared to sync to Plex';
        $data['store_labels'] = ['1' => 'prepared_to_plex'];
        $orderStatus = $this->orderStatus->setData($data)->setStatus($data['status']);
        $orderStatus->save();
        $data['status'] = 'sync_plex_completed';
        $data['label'] = 'Sync with Plex and Complete';
        $data['store_labels'] = ['1' => 'sync_plex_completed'];
        $orderStatus = $this->orderStatus->setData($data)->setStatus($data['status']);
        $orderStatus->save();
    }

    private function upgradeProductSchema202(ModuleDataSetupInterface $setup)
    {
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

        $attributeSetId = $eavSetup->getDefaultAttributeSetId(Product::ENTITY);
        $attributeGroupId = $eavSetup->getDefaultAttributeGroupId(Product::ENTITY);

        $eavSetup->addAttribute(Product::ENTITY, 'rubro_plex', [
            'type' => 'text',
            'label' => 'Rubro Plex',
            'input' => 'text',
            'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
            'required' => false,
            'is_filterable_in_grid' => true,
            'is_used_in_grid' => true,
            'is_visible_in_grid' => true,
            'searchable' => true,
            'system' => true,
            'use_for_promo_rules' => true,
            'visible_in_advanced_search' => true,
            'visible' => true
        ]);
        $attribute = $this->eavConfig->getAttribute(Product::ENTITY, 'rubro_plex');
        $attribute->setData('attribute_set_id', $attributeSetId);
        $attribute->setData('attribute_group_id', $attributeGroupId);
        $this->attributeResource->save($attribute);

        $eavSetup->addAttribute(Product::ENTITY, 'subrubro_plex', [
            'type' => 'text',
            'label' => 'Sub-Rubro Plex',
            'input' => 'text',
            'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
            'required' => false,
            'is_filterable_in_grid' => true,
            'is_used_in_grid' => true,
            'is_visible_in_grid' => true,
            'searchable' => true,
            'system' => true,
            'use_for_promo_rules' => true,
            'visible_in_advanced_search' => true,
            'visible' => true
        ]);
        $attribute = $this->eavConfig->getAttribute(Product::ENTITY, 'subrubro_plex');
        $attribute->setData('attribute_set_id', $attributeSetId);
        $attribute->setData('attribute_group_id', $attributeGroupId);
        $this->attributeResource->save($attribute);

        $eavSetup->addAttribute(Product::ENTITY, 'grupo_plex', [
            'type' => 'text',
            'label' => 'Grupo Plex',
            'input' => 'text',
            'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
            'required' => false,
            'is_filterable_in_grid' => true,
            'is_used_in_grid' => true,
            'is_visible_in_grid' => true,
            'searchable' => true,
            'system' => true,
            'use_for_promo_rules' => true,
            'visible_in_advanced_search' => true,
            'visible' => true
        ]);
        $attribute = $this->eavConfig->getAttribute(Product::ENTITY, 'grupo_plex');
        $attribute->setData('attribute_set_id', $attributeSetId);
        $attribute->setData('attribute_group_id', $attributeGroupId);
        $this->attributeResource->save($attribute);

        $eavSetup->addAttribute(Product::ENTITY, 'observaciones_plex', [
            'type' => 'text',
            'label' => 'Observaciones',
            'input' => 'textarea',
            'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
            'required' => false,
            'is_filterable_in_grid' => true,
            'is_used_in_grid' => true,
            'is_visible_in_grid' => true,
            'searchable' => true,
            'system' => true,
            'use_for_promo_rules' => true,
            'visible_in_advanced_search' => true,
            'visible' => true
        ]);
        $attribute = $this->eavConfig->getAttribute(Product::ENTITY, 'observaciones_plex');
        $attribute->setData('attribute_set_id', $attributeSetId);
        $attribute->setData('attribute_group_id', $attributeGroupId);
        $this->attributeResource->save($attribute);
    }

   /* private function upgradeProductSchema203(ModuleDataSetupInterface $setup)
    {
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
        $attributeSetId = $eavSetup->getDefaultAttributeSetId(Product::ENTITY);
        $attributeGroupId = $eavSetup->getDefaultAttributeGroupId(Product::ENTITY);

        $eavSetup->addAttribute(Product::ENTITY, 'plex_codebar', [
            'type' => 'text',
            'label' => 'Codigo Barra Plex',
            'input' => 'text',
            'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
            'required' => false,
            'is_filterable_in_grid' => true,
            'is_used_in_grid' => true,
            'is_visible_in_grid' => true,
            'searchable' => true,
            'system' => true,
            'use_for_promo_rules' => true,
            'visible_in_advanced_search' => true,
            'visible' => true
        ]);
        $attribute = $this->eavConfig->getAttribute(Product::ENTITY, 'plex_codebar');
        $attribute->setData('attribute_set_id', $attributeSetId);
        $attribute->setData('attribute_group_id', $attributeGroupId);
        $this->attributeResource->save($attribute);

        $eavSetup->addAttribute(Product::ENTITY, 'plex_codesbar', [
            'type' => 'text',
            'label' => 'Codigos de Barra Plex',
            'input' => 'text',
            'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
            'required' => false,
            'is_filterable_in_grid' => true,
            'is_used_in_grid' => true,
            'is_visible_in_grid' => true,
            'searchable' => true,
            'system' => true,
            'use_for_promo_rules' => true,
            'visible_in_advanced_search' => true,
            'visible' => true
        ]);
        $attribute = $this->eavConfig->getAttribute(Product::ENTITY, 'plex_codesbar');
        $attribute->setData('attribute_set_id', $attributeSetId);
        $attribute->setData('attribute_group_id', $attributeGroupId);
        $this->attributeResource->save($attribute);
    }*/
}
