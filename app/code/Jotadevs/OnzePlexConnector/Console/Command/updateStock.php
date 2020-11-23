<?php

namespace Jotadevs\OnzePlexConnector\Console\Command;

use Jotadevs\OnzePlexConnector\Model\OnzePlexApi;
use Jotadevs\OnzePlexConnector\Model\PlexProductFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class updateStock extends Command
{
    private $externalApi;
    private $state;
    private $plexproduct;
    public function __construct(
        OnzePlexApi $externalApi,
        PlexProductFactory $plexproduct,
        \Magento\Framework\App\State $state
    ) {
        $this->externalApi = $externalApi;
        $this->plexproduct = $plexproduct;
        $this->state = $state;
        parent::__construct();
    }

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this->setName('jotadevs:op:stock:update');
        $this->setDescription('Update stock op products from Plex');
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
        $op_products = $this->plexproduct->create()->getCollection()
            ->addFieldToFilter('is_synchronized', ['eq' => true])
            ->addFieldToFilter('is_op_enabled', ['eq' => true])
            ->addFieldToFilter('stock', ['gt' => 0])
            ->setPageSize(400);
        $pages = $op_products->getLastPageNumber();
        for ($i = 1; $i <= $pages; $i++) {
            $op_products = $this->plexproduct->create()->getCollection()
                ->addFieldToFilter('is_synchronized', ['eq' => true])
                ->addFieldToFilter('is_op_enabled', ['eq' => true])
                ->addFieldToFilter('stock', ['gt' => 0])
                ->setPageSize(400);
            $op_products->setCurPage($i);
            $stockFromPlex = $this->externalApi->getStockFromPlex($op_products->getColumnValues('codproduct'));
            var_dump("Vuelta numero N" .$i);
            //var_dump($stockFromPlex);
        }
    }
}
