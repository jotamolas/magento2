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
        $op_products = $this->plex_product->create()->getCollection();
        $op_products->addFieldToFilter('is_synchronized', ['eq' => true])
            ->load();
        if (!empty($op_products->getAllIds())) {
            $stockFromPlex = $this->plex_api->getStockFromPlex($op_products->getColumnValues('codproduct'));
            if ($stockFromPlex) {
                $processedProducts = $this->plex_api->processStockFromPlex($stockFromPlex);
            }
            $updatedProducts = $this->plex_api->updateStockItem($processedProducts);
            //TODO aca tengo q loguear los resultados...
            $this->logger->debug("Cron Job -Jotadevs-StockUpdate -> Se actualizaron los stocks de " .
                $updatedProducts['qty_product_stock_update'] .
                " productos");
            return $this;
        } else {
            $this->logger->debug("Cron Job -Jotadevs-StockUpdate -> No hay productos para actualizar sus stocks");
            return $this;
        }
    }
}
