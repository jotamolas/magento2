<?php

declare (strict_types=1);
namespace Jotadevs\RedCustoms\Setup\Patch\Data;

use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

/**
 * Class CreateCustomAttr for Create Custom Product Attribute using Data Patch.
 */
class CreateCustomAttr implements DataPatchInterface
{

    /**
     * ModuleDataSetupInterface
     *
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * EavSetupFactory
     *
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        EavSetupFactory $eavSetupFactory
    )
    {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);

        $eavSetup->addAttribute('catalog_product', 'plex_codebar', [
            'type' => 'text',
            'backend' => '',
            'frontend' => '',
            'label' => 'Codigo Barra Plex',
            'input' => 'text',
            'class' => '',
            'source' => '',
            'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
            'visible' => true,
            'use_for_promo_rules' => true,
            'visible_in_advanced_search' => true,
            'required' => false,
            'user_defined' => false,
            'default' => '',
            'system' => true,
            'searchable' => true,
            'filterable' => true,
            'comparable' => false,
            'visible_on_front' => false,
            'used_in_product_listing' => true,
            'unique' => false,
            'apply_to' => '',
        ]);

        $eavSetup->addAttribute('catalog_product', 'plex_codesbar', [
            'type' => 'text',
            'backend' => '',
            'frontend' => '',
            'label' => 'Codigos de Barra Plex',
            'input' => 'text',
            'class' => '',
            'source' => '',
            'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
            'visible' => true,
            'use_for_promo_rules' => true,
            'visible_in_advanced_search' => true,
            'required' => false,
            'user_defined' => false,
            'default' => '',
            'system' => true,
            'searchable' => true,
            'filterable' => true,
            'comparable' => false,
            'visible_on_front' => false,
            'used_in_product_listing' => true,
            'unique' => false,
            'apply_to' => '',
        ]);


    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * {@inheritDoc}}
     */
    public function getVersion(){
        return '1.0.6';
    }
}
