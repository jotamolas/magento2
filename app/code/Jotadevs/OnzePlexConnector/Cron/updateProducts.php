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
        // Traigo todos los productos Plex ya sincronizados.
        $this->logger->debug('||Jotadevs-Cron-UpdateProducts|| Updating Products');
        $this->plex_api->updateProductsOrchestor();
        //$this->logger->debug('||Jotadevs-Cron-UpdateProducts|| Updated --> ' . $products_plex_updated['message']);
        return $this;
    }
}
