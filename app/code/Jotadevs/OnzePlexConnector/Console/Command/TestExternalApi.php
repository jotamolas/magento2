<?php

namespace Jotadevs\OnzePlexConnector\Console\Command;

use Jotadevs\OnzePlexConnector\Model\OnzePlexApi;
use Jotadevs\OnzePlexConnector\Model\PlexProductFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TestExternalApi extends Command
{
    private $externalApi;
    private $plexproduct;
    public function __construct(
        OnzePlexApi $externalApi,
        PlexProductFactory $plexproduct
    ) {
        $this->externalApi = $externalApi;
        $this->plexproduct = $plexproduct;
        parent::__construct();
    }

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this->setName('jotadevs:test:externalapi');
        $this->setDescription('To test putooo');
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
        $response = $this->externalApi->addCategoryToProduct();
        //var_dump($response);
        var_dump($response['items']);
        $output->writeln(count($response['items']));
    }
}
