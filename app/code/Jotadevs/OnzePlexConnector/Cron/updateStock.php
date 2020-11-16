<?php

namespace Jotadevs\OnzePlexConnector\Cron;

use Jotadevs\OnzePlexConnector\Model\OnzePlexApi;
use Jotadevs\OnzePlexConnector\Model\PlexProductFactory;
use Psr\Log\LoggerInterface;

class updateStock
{
    protected $plex_product;
    protected $plex_api;
    protected $logger;

    public function __construct(
        PlexProductFactory $plex_product,
        OnzePlexApi $plexApi,
        LoggerInterface $logger
    ) {
        $this->plex_product = $plex_product;
        $this->plex_api = $plexApi;
        $this->logger = $logger;
    }

    /**
     * Este método es ejecutado por un crontab u otro metodo para actualizar el stock masivamente desde Plex.
     */
    public function updateStock()
    {
        //Traigo todos los productos plex sincronizados.
        $op_products = $this->plex_product->create()->getCollection()
            ->addFieldToFilter('is_synchronized', ['eq' => true])
            ->addFieldToFilter('is_op_enabled', ['eq' => true])
            ->setPageSize(400);
        if (!empty($op_products->getAllIds())) {
            $pages = $op_products->getLastPageNumber();
            for ($i = 1; $i <= $pages; $i++) {
                $op_products = $this->plex_product->create()->getCollection()
                    ->addFieldToFilter('is_synchronized', ['eq' => true])
                    ->addFieldToFilter('is_op_enabled', ['eq' => true])
                    ->setPageSize(400);
                $this->logger->info(" || Jotadevs Update Stock Product || Comenzando con Página nro.: " . $i);
                $op_products->setCurPage($i);
                $this->logger->info(" || Jotadevs Update Stock Product || Consultando el stock de : "
                    . count($op_products->getColumnValues('sku')) . " productos");

                $stockFromPlex = $this->plex_api->getStockFromPlex($op_products->getColumnValues('codproduct'));
                $stockFromPlex ?
                    $processedProducts = $this->plex_api->processStockFromPlex($stockFromPlex) :
                    $processedProducts = null;
                $updatedProducts = $this->plex_api->updateStockItem($processedProducts);
                $this->logger->debug("Cron Job -Jotadevs-StockUpdate -> Se actualizaron los stocks de " .
                    $updatedProducts['qty_product_stock_update'] .
                    " productos");

            }
            return $this;
        } else {
            $this->logger->debug("Cron Job -Jotadevs-StockUpdate -> No hay productos para actualizar sus stocks");
            return $this;
        }
    }
}
