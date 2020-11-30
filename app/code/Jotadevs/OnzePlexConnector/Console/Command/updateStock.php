<?php

namespace Jotadevs\OnzePlexConnector\Console\Command;

use Jotadevs\OnzePlexConnector\Model\OnzePlexApi;
use Jotadevs\OnzePlexConnector\Model\PlexProductFactory;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class updateStock extends Command
{
    private $externalApi;
    private $state;
    private $plexproduct;
    private $timezone;

    public function __construct(
        OnzePlexApi $externalApi,
        PlexProductFactory $plexproduct,
        \Magento\Framework\App\State $state,
        TimezoneInterface $timezone
    ) {
        $this->externalApi = $externalApi;
        $this->plexproduct = $plexproduct;
        $this->state = $state;
        $this->timezone = $timezone;
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
        $total_time = $this->timezone->date();
        $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_GLOBAL);
        $op_products = $this->plexproduct->create()->getCollection()
            ->addFieldToFilter('is_synchronized', ['eq' => true])
            ->addFieldToFilter('is_op_enabled', ['eq' => true])
            //->addFieldToFilter('stock', ['gt' => 0])
            ->setPageSize(400);
        var_dump("Cantidad de productos a consultar stock (Stock > 0) " . count($op_products->getAllIds()));
        $pages = $op_products->getLastPageNumber();
        var_dump("Cantidad de paginas a procesar " . $pages);
        for ($i = 1; $i <= $pages; $i++) {
            var_dump("updateStock, vuelta numero N" . $i);
            $op_products = $this->plexproduct->create()->getCollection()
                ->addFieldToFilter('is_synchronized', ['eq' => true])
                ->addFieldToFilter('is_op_enabled', ['eq' => true])
                //->addFieldToFilter('stock', ['gt' => 0])
                ->setPageSize(400);
            $op_products->setCurPage($i);
            $init_time_plex_call =  $this->timezone->date();
            $stockFromPlex = $this->externalApi->getStockFromPlex($op_products->getColumnValues('codproduct'));
            $end_time_plex_call = $this->timezone->date();

            $total_time_plex_call = date_diff($init_time_plex_call, $end_time_plex_call);
            var_dump("Tiempo utilizado para llamada a Plex --> " .
                $total_time_plex_call->format("%i:%s"));

            $init_time_pr_op_mag =  $this->timezone->date();
            $processedProducts = $this->externalApi->processStockFromPlex($stockFromPlex);
            $end_time_pr_op_mag =  $this->timezone->date();
            $total_time_pr_op_mag = date_diff($init_time_pr_op_mag, $end_time_pr_op_mag);
            var_dump("Tiempo utilizado para procesar los productos en Magento Modelo Plex --> " .
                $total_time_pr_op_mag->format("%i:%s"));


            $init_time_pr_prod_mag =  $this->timezone->date();
            $updatedProducts = $this->externalApi->updateStockItem($processedProducts);
            $end_time_pr_prod_mag =  $this->timezone->date();
            $total_time_pr_prod_mag  = date_diff($init_time_pr_prod_mag, $end_time_pr_prod_mag);
            var_dump("Tiempo utilizado para procesar los productos en Magento Modelo Magento --> " .
                $total_time_pr_prod_mag->format("%i:%s"));
            var_dump("Cantidad de productos: " . $updatedProducts['qty_product_stock_update']);

        }
        var_dump("Tiempo total de ejecución : " . date_diff($total_time, $this->timezone->date())->format("%i:%s"));
    }
}
