<?php

namespace Jotadevs\OCAEPackShipping\Console\Command;

use Jotadevs\OCAEPackShipping\Model\Query\OcaApi;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Test extends Command
{
    private $oca;
    public function __construct(
        OcaApi $oca
    ) {
        $this->oca = $oca;
        parent::__construct();
    }

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this->setName('jotadevs:oca_api:test');
        $this->setDescription('To test putooo');
        $this->addArgument(
            'postalcode',
            InputArgument::REQUIRED,
            'Codigo Postal'
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
        //$response = $this->oca->tarifarEnvio($input->getArgument('postalcode'));
        $response = $this->oca->GetCentrosImposicionPorCP($input->getArgument('postalcode'));
        var_dump($response);
    }
}
