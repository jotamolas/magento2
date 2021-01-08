<?php


namespace Jotadevs\OnzePlexConnector\Cron;

use Jotadevs\OnzePlexConnector\Model\OnzePlexApi;
use Magento\Framework\App\State;
use Psr\Log\LoggerInterface;

class ImportAttributesFromPlex
{
    protected $plex_api;
    protected $logger;
    protected $state;

    public function __construct(
        OnzePlexApi $plexApi,
        LoggerInterface $logger,
        State $state
    ) {
        $this->plex_api = $plexApi;
        $this->logger = $logger;
        $this->state = $state;
    }

    public function importAttributes()
    {
        $this->logger->debug('||Jotadevs-Cron-Import Attributes from Plex||');
        $response =  $this->plex_api->importLaboratoriosFromPlex();
        $this->logger->debug('||Jotadevs-Cron-Import Attributes from Plex||
                    Laboratorios recibidos: '. $response['received']);
        $this->logger->debug('||Jotadevs-Cron-Import Attributes from Plex||
                    Laboratorios nuevos: '. $response['new']);
    }

}
