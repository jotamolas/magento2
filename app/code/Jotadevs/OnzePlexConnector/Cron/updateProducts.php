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
        $products_plex_updated = $this->plex_api->updateProductsFromPlex();
        $products_add_categories = $this->plex_api->addCategoryToProduct();
        $this->logger->debug('||Jotadevs-Cron-UpdateProducts|| Add Categories --> ' . $products_add_categories['message']);
        $this->logger->debug('||Jotadevs-Cron-UpdateProducts|| Updated --> ' . $products_plex_updated['message']);
        return $this;
    }
}
