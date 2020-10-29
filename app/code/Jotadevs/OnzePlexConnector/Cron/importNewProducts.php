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

        $this->logger->debug('|||||| Jotadevs-Cron-ImportNewProducts ||||||');
        // traigo las categorias
        $this->logger->debug('||Jotadevs-Cron-ImportNewProducts||Importando Rubros Plex');
        $import_rubros = $this->plex_api->importRubrosFromPlex();
        $this->logger->debug('||Jotadevs-Cron-ImportNewProducts|| ' .
            $import_rubros['message']);
        $this->logger->debug('||Jotadevs-Cron-ImportNewProducts||Importando Sub Rubros Plex');
        $import_subrubros = $this->plex_api->importSubRubrosFromPlex();
        $this->logger->debug('||Jotadevs-Cron-ImportNewProducts|| ' .
            $import_subrubros['message']);
        $this->logger->debug('||Jotadevs-Cron-ImportNewProducts||Importando Grupos Plex');
        $import_grupos = $this->plex_api->importGruposFromPlex();
        $this->logger->debug('||Jotadevs-Cron-ImportNewProducts|| ' .
            $import_grupos['message']);

        $this->logger->debug('||Jotadevs-Cron-ImportNewProducts||Sincronizando Rubros Plex hacia Magento');
        $rubros_convertion = $this->plex_api->convertToMagentoCategory();
        $this->logger->debug('||Jotadevs-Cron-ImportNewProducts|| ' .
            $rubros_convertion['message'] . " Cantidad: " . $rubros_convertion['qty']);


        // traigo laboratorios
        $this->logger->debug('||Jotadevs-Cron-ImportNewProducts||Importando Laboratorios Plex');
        $laboratorios = $this->plex_api->convertToMagentoCategory();
        $this->logger->debug('||Jotadevs-Cron-ImportNewProducts|| ' .
            $laboratorios['message']);

        // traer todos los productos desde la api???
        $products_from_plex = $this->plex_api->importProductsFromPlex();
        if ($products_from_plex['state'] == 'success') {
            $this->logger->debug('||Jotadevs-Cron-ImportNewProducts|| Productos Recibidos desde Plex: ' .
                $products_from_plex['received'] . ' Nuevos: ' . $products_from_plex['new']);
            $this->logger->debug('||Jotadevs-Cron-ImportNewProducts||Sincronizando Productos Plex hacia Magento');
            $conversion = $this->plex_api->convertToMagentoProduct();
            $this->logger->debug('||Jotadevs-Cron-ImportNewProducts|| Productos convertidos: ' . $conversion['qty']);

            $products_add_categories = $this->plex_api->addCategoryToProduct();
            $this->logger->debug('||Jotadevs-Cron-ImportNewProducts|| ' . $products_add_categories['message']);
            return $this;
        } else {
            $this->logger->error('||Jotadevs-Cron-ImportNewProducts||' . $products_from_plex['message']);
            return $this;
        }
    }
}
