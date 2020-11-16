<?php

namespace Jotadevs\OnzePlexConnector\Console\Command;

use Jotadevs\OnzePlexConnector\Model\OnzePlexApi;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ImportProducts extends Command
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
        $this->setName('jotadevs:op:product:import  ');
        $this->setDescription('This command retrieve New products from ERP.');
        parent::configure();
    }

    /**
     * CLI command description
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $response =  $this->onzeplexapi->importProductsFromPlex();
        if ($response['state'] == 'success') {
            $output->writeln('Estado: ' . $response['state']);
            $output->writeln('Productos Recibidos: ' . $response['received']);
            $output->writeln('Nuevos: ' . $response['new']);
        } else {
            $output->writeln('Nuevos: ' . $response['message']);
        }
        // var_dump($response);
    }
}
