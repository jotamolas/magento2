<?php

namespace Jotadevs\OnzePlexConnector\Cron;

use Jotadevs\OnzePlexConnector\Model\OnzePlexApi;
use Jotadevs\OnzePlexConnector\Model\PlexProductFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Psr\Log\LoggerInterface;

class updateStock
{
    protected $plex_product;
    protected $plex_api;
    protected $logger;
    private $timezone;
    protected $messageManager;

    public function __construct(
        PlexProductFactory $plex_product,
        OnzePlexApi $plexApi,
        LoggerInterface $logger,
        ManagerInterface $messageManager,
        TimezoneInterface $timezone
    ) {
        $this->plex_product = $plex_product;
        $this->plex_api = $plexApi;
        $this->logger = $logger;
        $this->timezone = $timezone;
        $this->messageManager = $messageManager;
    }

    /**
     * Este método es ejecutado por un crontab u otro metodo para actualizar el stock masivamente desde Plex.
     */
    public function updateStock()
    {
        $total_time = $this->timezone->date();
        //Envio un prd dummy para testear ws
        $ws_plex_status = $this->plex_api->getStockFromPlex([1101]);
        if ($ws_plex_status['state'] == 'success') {
            //Traigo todos los productos plex sincronizados.
            $op_products = $this->plex_product->create()->getCollection()
                ->addFieldToFilter('is_synchronized', ['eq' => true])
                ->addFieldToFilter('is_op_enabled', ['eq' => true])
                ->setPageSize(400);
            $this->logger->info("| Jotadevs Update Stock Product || Cantidad de productos a consultar stock" .
                count($op_products->getAllIds()));
            if (!empty($op_products->getAllIds())) {
                $pages = $op_products->getLastPageNumber();
                $this->logger->info("Cantidad de paginas a procesar " . $pages);
                for ($i = 1; $i <= $pages; $i++) {
                    $op_products = $this->plex_product->create()->getCollection()
                        ->addFieldToFilter('is_synchronized', ['eq' => true])
                        ->addFieldToFilter('is_op_enabled', ['eq' => true])
                        ->setPageSize(400);
                    $this->logger->info(" || Jotadevs Update Stock Product || Comenzando con Página nro.: " . $i);
                    $op_products->setCurPage($i);
                    $this->logger->info(" || Jotadevs Update Stock Product || Consultando el stock de : "
                        . count($op_products->getColumnValues('sku')) . " Productos");
                    $init_time_plex_call =  $this->timezone->date();
                    $stockFromPlex = $this->plex_api->getStockFromPlex($op_products->getColumnValues('codproduct'));
                    $end_time_plex_call = $this->timezone->date();
                    $total_time_plex_call = date_diff($init_time_plex_call, $end_time_plex_call);
                    $this->logger->info(" || Jotadevs Update Stock Product || Tiempo utilizado para llamada a Plex --> " .
                        $total_time_plex_call->format("%i:%s"));

                    $init_time_pr_op_mag =  $this->timezone->date();
                    $processedProducts = $this->plex_api->processStockFromPlex($stockFromPlex);
                    $end_time_pr_op_mag =  $this->timezone->date();
                    $total_time_pr_op_mag = date_diff($init_time_pr_op_mag, $end_time_pr_op_mag);
                    $this->logger->info(" || Jotadevs Update Stock Product ||
                Tiempo utilizado para procesar los productos en Magento Modelo Plex --> " .
                        $total_time_pr_op_mag->format("%i:%s"));

                    $init_time_pr_prod_mag =  $this->timezone->date();
                    $updatedProducts = $this->plex_api->updateStockItem($processedProducts);
                    $end_time_pr_prod_mag =  $this->timezone->date();
                    $total_time_pr_prod_mag  = date_diff($init_time_pr_prod_mag, $end_time_pr_prod_mag);
                    $this->logger->info(" || Jotadevs Update Stock Product ||
                Tiempo utilizado para procesar los productos en Magento Modelo Magento --> " .
                        $total_time_pr_prod_mag->format("%i:%s"));

                    $this->logger->debug(" || Jotadevs Update Stock Product ||Se actualizaron los stocks de " .
                        $updatedProducts['qty_product_stock_update'] .
                        " productos");
                }
            } else {
                $this->logger->debug("Cron Job -Jotadevs-StockUpdate -> No hay productos para actualizar sus stocks");
            }
        } else {
            $error_msg = " No se pudo conectar con el WS de Plex. Error: " .
                $ws_plex_status['message'] . " Mensaje: " . $ws_plex_status['message'];
            $this->messageManager->addErrorMessage($error_msg);
            $this->logger->debug($error_msg);
            $this->logger->info(" || Jotadevs Update Stock Product || Tiempo total de ejecución : "
                . date_diff($total_time, $this->timezone->date())->format("%i:%s"));
            throw new LocalizedException(__('%1', $error_msg));
        }
        $this->logger->info(" || Jotadevs Update Stock Product || Tiempo total de ejecución : "
            . date_diff($total_time, $this->timezone->date())->format("%i:%s"));
        return $this;
    }
}
