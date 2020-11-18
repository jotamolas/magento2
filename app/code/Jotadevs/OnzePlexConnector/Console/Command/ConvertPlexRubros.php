<?php

namespace Jotadevs\OnzePlexConnector\Console\Command;

use Jotadevs\OnzePlexConnector\Model\OnzePlexApi;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ConvertPlexRubros extends Command
{
    private $onzeplexapi;
    public function __construct(OnzePlexApi $onzeplexapi)
    {
        $this->onzeplexapi = $onzeplexapi;
        parent::__construct();
    }
    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this->setName('jotadevs:op:rubro:convert');
        $this->setDescription('Convierte Rubros de OnePlex en Categorias Magento');
        parent::configure();
    }

    /**
     * CLI command description
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $output->writeln('Metodo deprecado hasta implementar Categorias Plex');
        /*$response = $this->onzeplexapi->convertToMagentoCategory();
        $output->writeln('Estado de Operación: ' . $response['state']);
        $output->writeln('Catidad convertida: ' . $response['qty']);
        $output->writeln($response['message']);*/
    }
}
