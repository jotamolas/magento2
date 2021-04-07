<?php
namespace Jotadevs\OnzePlexConnector\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Sales\Model\Order\StatusFactory;
use Magento\Sales\Model\ResourceModel\Order\StatusFactory as StatusResourceFactory;

class AddPlexOrderStatus implements DataPatchInterface
{

    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var StatusFactory
     */
    protected $statusFactory;

    /**
     * @var StatusResourceFactory
     */
    protected $statusResourceFactory;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param StatusFactory $statusFactory
     * @param StatusResourceFactory $statusResourceFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        StatusFactory $statusFactory,
        StatusResourceFactory $statusResourceFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->statusFactory = $statusFactory;
        $this->statusResourceFactory = $statusResourceFactory;
    }

    /**
     * @inheritdoc
     */
    public function apply()
    {
        $status = $this->statusFactory->create();
        $statusResource = $this->statusResourceFactory->create();

        $status->setData([
            'status' => 'plex_prepared_to_sync',
            'label' => 'Prepared to Sync with Plex',
        ]);

        /**
         * Save the new status
         */
        $statusResource->save($status);

        /**
         * Assign status to state
         */
        $status->assignState('plex_prepared_to_sync', true, true);

        /**
         * Second Status and State
         */
        $status->setData([
            'status' => 'plex_sync_complete',
            'label' => 'Sync Order and Payment with Plex ',
        ]);
        $statusResource->save($status);
        $status->assignState('plex_sync_complete', true, true);

        /**
         * Third
         */
        $status->setData([
            'status' => 'plex_sync_without_payment',
            'label' => 'Sync Order without Payment with Plex ',
        ]);
        $statusResource->save($status);
        $status->assignState('plex_sync_without_payment', true, true);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }

    public function getVersion()
    {
        return '1.1.7';
    }
}
