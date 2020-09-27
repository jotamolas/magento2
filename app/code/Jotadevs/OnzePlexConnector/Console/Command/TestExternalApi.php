<?php

namespace Jotadevs\OnzePlexConnector\Console\Command;

use Jotadevs\OnzePlexConnector\Model\OnzePlexApi;
use Jotadevs\OnzePlexConnector\Model\PlexProductFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TestExternalApi extends Command
{
    private $externalApi;
    private $plexproduct;
    public function __construct(
        OnzePlexApi $externalApi,
        PlexProductFactory $plexproduct
    ) {
        $this->externalApi = $externalApi;
        $this->plexproduct = $plexproduct;
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
        //$response = $this->externalApi->prepareOrderToSync();
        /*$response = $this->externalApi->getMagentoOrdersToSync();
        if ($response['status'] == 'ok' and $response['qty_to_sync'] > 0) {
            foreach ($response['orders_to_sync'] as $order) {
                var_dump($this->externalApi->postOrderToPlex($order));
            }
        }*/
        //$response = $this->externalApi->addCategoryToProduct();
        $response = $this->externalApi->informPaymentToPlex();
        var_dump($response);
        //var_dump($this->externalApi->getMediosPago());
    }
}
