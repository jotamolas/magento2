<?php

namespace Jotadevs\OnzePlexConnector\Console\Command;

use Jotadevs\OnzePlexConnector\Model\OnzePlexApi;
use Magento\Framework\App\State;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateProduct extends Command
{
    private $onzeplexapi;
    private $state;

    public function __construct(
        OnzePlexApi $onzeplexapi,
        State $state
    ) {
        $this->onzeplexapi = $onzeplexapi;
        $this->state = $state;
        parent::__construct();
    }

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this->setName('jotadevs:op:product:update');
        $this->setDescription('This command update products from ERP.');
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
        $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_GLOBAL);
        $this->onzeplexapi->updateProductsOrchestor();
        // var_dump($response);
    }
}
