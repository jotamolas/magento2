<?php

namespace Jotadevs\OnzePlexConnector\Cron;

use Jotadevs\OnzePlexConnector\Model\OnzePlexApi;
use Jotadevs\OnzePlexConnector\Model\PlexProductFactory;
use Magento\Framework\App\State;
use Psr\Log\LoggerInterface;

class importNewProducts
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

    public function importNewProducts()
    {
        //$this->state->setAreaCode(\Magento\Framework\App\Area::AREA_GLOBAL);
        // traer todos los productos desde la api???
        $products_from_plex = $this->plex_api->importProductsFromPlex();
        if ($products_from_plex['state'] == 'success') {
            $this->logger->debug('||Jotadevs-Cron-ImportNewProducts|| Productos Recibidos desde Plex: ' .
                $products_from_plex['received'] . ' Nuevos: ' . $products_from_plex['new']);
            $this->logger->debug('||Jotadevs-Cron-ImportNewProducts||Sincronizando Productos Plex hacia Magento');
            $conversion = $this->plex_api->convertToMagentoProduct();
            $this->logger->debug('||Jotadevs-Cron-ImportNewProducts|| Productos convertidos: ' . $conversion['qty']);
            return $this;
        } else {
            $this->logger->error('||Jotadevs-Cron-ImportNewProducts||' . $products_from_plex['message']);
            return $this;
        }
    }
}
