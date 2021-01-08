<?php

namespace Jotadevs\OnzePlexConnector\Console\Command;

use Jotadevs\OnzePlexConnector\Model\OnzePlexApi;
use Jotadevs\OnzePlexConnector\Model\PlexProductFactory;
use Magento\Framework\App\State;
use Magento\Sales\Api\OrderRepositoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TestExternalApi extends Command
{
    private $externalApi;
    private $plexproduct;
    private $state;
    private $order_repository_magento;

    public function __construct(
        OnzePlexApi $externalApi,
        PlexProductFactory $plexproduct,
        State $state,
        OrderRepositoryInterface $orderRepository
    ) {
        $this->externalApi = $externalApi;
        $this->plexproduct = $plexproduct;
        $this->state = $state;
        $this->order_repository_magento = $orderRepository;
        parent::__construct();
    }

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this->setName('jotadevs:test');
        $this->setDescription('To test putooo');
        parent::configure();
    }

    /**
     * CLI command description
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_GLOBAL);
        //$response = $this->externalApi->addCategoryToProduct();
        //$response = $this->externalApi->updateProductsFromPlex();
        //$oder_mag = $this->order_repository_magento->get(5);
        //$response_prepared = $this->externalApi->prepareOrderToSync();
        /*$response_order_to_sync = $this->externalApi->getMagentoOrdersToSync();
        if ($response_order_to_sync['status'] == 'ok' and $response_order_to_sync['qty_to_sync'] > 0) {
            foreach ($response_order_to_sync['orders_to_sync'] as $order) {
                var_dump($this->externalApi->postOrderToPlex($order));
            }
        }*/
        //$response = $this->externalApi->addCategoryToProduct();
        //$response = $this->externalApi->getSucursalesPlex();
        $response = $this->externalApi->informPaymentToPlex();
        //var_dump($this->externalApi->getMediosPago());
        //$products_plex_stock = $this->externalApi->getStockFromPlex([1007900505]);
        //$products_plex_updated = $this->externalApi->processStockFromPlex($products_plex_stock);
        //$response = $this->externalApi->updateStockItem($products_plex_updated);
        //$response = $this->externalApi->updateProductsOrchestor();
        // $response = $this->externalApi->evaluatePriceVariation('403.11','383.92');
        //var_dump($response_prepared);
         //var_dump($response_order_to_sync);
        var_dump($response);
    }
}
