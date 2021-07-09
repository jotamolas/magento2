<?php

namespace Jotadevs\OnzePlexConnector\Console\Command;

use Jotadevs\OnzePlexConnector\Model\OnzePlexApi;
use Magento\Framework\App\State;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateOneProduct extends Command
{
    private $onzeplexapi;
    private $state;
    const NAME_ARGUMENT = "sku";

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
        $this->setName('jotadevs:op:oneproduct:update');
        $this->setDescription('This command update an product from Middleware.');
        $this->setDefinition(
            [
                new InputArgument(self::NAME_ARGUMENT, InputArgument::REQUIRED, "sku")
            ]
        );
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
        
        $response = $this->onzeplexapi->updateOneProductFromMiddleware($input->getArgument(self::NAME_ARGUMENT));
        var_dump($response);
    }
}
