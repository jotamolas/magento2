<?php

namespace Jotadevs\OnzePlexConnector\Console\Command;

use Jotadevs\OnzePlexConnector\Model\OnzePlexApi;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ImportRubros extends Command
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
        $this->setName('jotadevs:op:rubro:import');
        $this->setDescription('This command import Categories and SubCategories from ERP Onze Plex');
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
        $output->writeln("Comando deprecado hasta implementar categorias plex");
        /*$rubros = $this->onzeplexapi->importRubrosFromPlex();
        $subrubros = $this->onzeplexapi->importSubRubrosFromPlex();
        $grupos = $this->onzeplexapi->importGruposFromPlex();
        $output->writeln("Importacion de Rubros:");
        $output->writeln("------->Estado: " . $rubros['state']);
        $output->writeln("------->Recibidos: " . $rubros['received']);
        $output->writeln("------->Nuevos:" . $rubros['new']);

        $output->writeln("Importacion de Sub-Rubros:");
        $output->writeln("------->Estado: " . $subrubros['state']);
        $output->writeln("------->Recibidos: " . $subrubros['received']);
        $output->writeln("------->Nuevos:" . $subrubros['new']);

        $output->writeln("Importacion de Grupos:");
        $output->writeln("------->Estado: " . $grupos['state']);
        $output->writeln("------->Recibidos: " . $grupos['received']);
        $output->writeln("------->Nuevos:" . $grupos['new']);*/

    }
}
