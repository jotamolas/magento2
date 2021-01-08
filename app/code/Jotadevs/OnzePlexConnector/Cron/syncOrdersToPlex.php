<?php

namespace Jotadevs\OnzePlexConnector\Cron;

use Jotadevs\OnzePlexConnector\Model\OnzePlexApi;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Psr\Log\LoggerInterface;

class syncOrdersToPlex
{
    protected $plex_api;
    protected $logger;
    private $timezone;
    protected $messageManager;

    public function __construct(
        OnzePlexApi $plexApi,
        LoggerInterface $logger,
        ManagerInterface $messageManager,
        TimezoneInterface $timezone
    ) {
        $this->plex_api = $plexApi;
        $this->logger = $logger;
        $this->timezone = $timezone;
        $this->messageManager = $messageManager;
    }

    /**
     * Este método es ejecutado por un crontab u otro metodo para actualizar el stock masivamente desde Plex.
     */
    public function syncOrdersToPlex()
    {
        $total_time = $this->timezone->date();
        //Testeo si esta disponible el ws de onzeplex
        $ws_plex_status = $this->plex_api->getStockFromPlex([1101]);
        if ($ws_plex_status['state'] == 'success') {
            //preparo ordenes
            $response_prepared = $this->plex_api->prepareOrderToSync();
            $this->logger->info(" || Jotadevs Sync Orders To Plex  || Ordenes preparadas: "
                . $response_prepared['qty']);
            $response_order_to_sync = $this->plex_api->getMagentoOrdersToSync();
            $this->logger->info(" || Jotadevs Sync Orders To Plex  || Ordenes para enviar a plex: "
                . $response_order_to_sync['qty_to_sync']);
            if ($response_order_to_sync['status'] == 'ok' and $response_order_to_sync['qty_to_sync'] > 0) {
                foreach ($response_order_to_sync['orders_to_sync'] as $order) {
                    $rs = $this->plex_api->postOrderToPlex($order);
                    $this->logger->info(" || Jotadevs Sync Orders To Plex  || Resultado : "
                        . $rs['state'] . " Mensaje: " .$rs['message']);
                }
            }
            $payments = $this->plex_api->informPaymentToPlex();
            if ($payments['status'] == 'ok'){
                $this->logger->info(" || Jotadevs Sync Orders To Plex  || Pagos Posteados: "
                    . $payments['q_order_posted_to_plex'] . " Pagos no posteados: "
                    . $payments['q_order_not_posted_to_plex']);
            }else{
                $this->logger->info(" || Jotadevs Sync Orders To Plex  || NO hay pagos para informar ");
            }
        } else {
            $error_msg = " No se pudo conectar con el WS de Plex. Error: " .
                $ws_plex_status['message'] . " Mensaje: " . $ws_plex_status['message'];
            $this->messageManager->addErrorMessage($error_msg);
            $this->logger->debug($error_msg);
            $this->logger->info(" || Jotadevs Sync Orders To Plex  || Tiempo total de ejecución : "
                . date_diff($total_time, $this->timezone->date())->format("%i:%s"));
            throw new LocalizedException(__('%1', $error_msg));
        }
        $this->logger->info(" || Jotadevs Sync Orders To Plex || Tiempo total de ejecución : "
            . date_diff($total_time, $this->timezone->date())->format("%i:%s"));
        return $this;
    }
}
