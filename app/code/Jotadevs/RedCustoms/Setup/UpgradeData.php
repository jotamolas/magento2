<?php
namespace Jotadevs\RedCustoms\Setup;

use Magento\Customer\Model\Customer;
use Magento\Customer\Model\ResourceModel\Attribute;
use Magento\Eav\Model\Config;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Sales\Model\Order\Status;

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
            $this->upgradeOrderStatus103($setup);
        }
        if (version_compare($context->getVersion(), '1.0.4') < 0) {
            $this->upgradeOrderStatus104($setup);
        }
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


}
