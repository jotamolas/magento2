<?php

namespace Jotadevs\OnzePlexConnector\Observer;

use Jotadevs\OnzePlexConnector\Model\OnzePlexApi;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Psr\Log\LoggerInterface;

class PostOrderToPlex implements ObserverInterface
{
    protected $logger;
    protected $apiPlex;
    public function __construct(
        OnzePlexApi $apiPlex,
        LoggerInterface $logger
    ) {
        $this->logger = $logger;
        $this->apiPlex = $apiPlex;
    }

    public function execute(Observer $observer)
    {
        $order = $observer->getOrder();
        $quote = $observer->getQuote();

        // Do whatever you want here

        return $this;
    }
}
