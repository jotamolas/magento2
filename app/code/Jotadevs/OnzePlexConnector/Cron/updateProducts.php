<?php

namespace Jotadevs\OnzePlexConnector\Cron;

use Jotadevs\OnzePlexConnector\Model\OnzePlexApi;
use Jotadevs\OnzePlexConnector\Model\PlexProductFactory;
use Magento\Framework\App\State;
use Psr\Log\LoggerInterface;

class updateProducts
{
    protected $plex_product;
    protected $plex_api;
    protected $logger;
    protected $state;

    public function __construct(
        PlexProductFactory $plex_product,
        OnzePlexApi $plexApi,
        LoggerInterface $logger,
        State $state
    ) {
        $this->plex_product = $plex_product;
        $this->plex_api = $plexApi;
        $this->logger = $logger;
        $this->state = $state;
    }
    public function updateProducts()
    {
        //$this->state->setAreaCode(\Magento\Framework\App\Area::AREA_GLOBAL);

        $this->logger->debug('||Jotadevs-Cron-UpdateProducts|| Updating Products');

        $rs = $this->plex_api->updateProductsOrchestor(true);

        $this->logger->debug('||Jotadevs-Cron-UpdateProducts|| Total de Tiempo de ejecucion--> ' . $rs['total_time']);
        $this->logger->debug('||Jotadevs-Cron-UpdateProducts|| Products Processed --> ' . $rs['products_processed']);
        $this->logger->debug('||Jotadevs-Cron-UpdateProducts|| Products Updated --> ' . $rs['products_updated']);
        $this->logger->debug('||Jotadevs-Cron-UpdateProducts|| Products Disabled --> ' . $rs['products_disabled']);
        $this->logger->debug('||Jotadevs-Cron-UpdateProducts|| Products New Disabled --> ' .
            $rs['total_products_disabled_new']);
        $this->logger->debug('||Jotadevs-Cron-UpdateProducts|| Products Disabled for Price --> ' .
            $rs['products_disabled_for_price']);
        foreach ($rs['error_messages'] as $error_key => $error_value) {
            $this->logger->debug('||Jotadevs-Cron-UpdateProducts|| Error en  --> ' .
                $error_key . " Mensaje: " . $error_value['message']);
        }
        return $this;
    }
}
