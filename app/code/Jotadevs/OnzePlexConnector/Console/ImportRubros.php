<?php

namespace Jotadevs\OnzePlexConnector\Console;

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
        $this->setName('jotadevs:op:importrubros');
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
       /* $rubros = $this->onzeplexapi->getRubrosOnexPlex();
        var_dump($rubros);
        $subrubros = $this->onzeplexapi->getSubRubrosOnexPlex();
        var_dump($subrubros);*/
        var_dump($this->onzeplexapi->testeandoCategorias());
    }
}
