<?php

namespace Jotadevs\OnzePlexConnector\Console\Command;

use Jotadevs\OnzePlexConnector\Model\OnzePlexApi;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\App\State;

class ConvertPlexProduct extends Command
{
    private $onzeplexapi;
    private $state;

    public function __construct(OnzePlexApi $onzeplexapi, State $state)
    {
        $this->onzeplexapi = $onzeplexapi;
	$this->state = $state;
        parent::__construct();
    }

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this->setName('jotadevs:op:product:convert');
        $this->setDescription(
            'Este comando convierte productos importados desde Plex y almacenados
             en base de datos que no hayan sido convertidos a productos Magento.'
        );
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
	$this->state->setAreaCode(\Magento\Framework\App\Area::AREA_GLOBAL);
        $response = $this->onzeplexapi->convertToMagentoProduct();
        $output->writeln('Estado de Operación: ' . $response['state']);
        $output->writeln('Cantidad convertida: ' . $response['qty']);
        $output->writeln($response['message']);
    }
}
