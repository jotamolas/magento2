<?php

namespace Jotadevs\OnzePlexConnector\Console\Command;

use Jotadevs\OnzePlexConnector\Model\OnzePlexApi;
use Jotadevs\OnzePlexConnector\Model\PlexProductFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ImportProducts extends Command
{
    private $plexproduct;
    private $onzeplexapi;
    public function __construct(
        OnzePlexApi $onzeplexapi,
        PlexProductFactory $plexproduct
    ) {
        $this->onzeplexapi = $onzeplexapi;
        $this->plexproduct = $plexproduct;
        parent::__construct();
    }

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this->setName('jotadevs:op:import');
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
        $response =  $this->onzeplexapi->convertToMagentoProduct();
        var_dump($response);
    }
}
