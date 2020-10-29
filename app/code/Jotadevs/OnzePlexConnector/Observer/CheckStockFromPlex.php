<?php

namespace Jotadevs\OnzePlexConnector\Observer;

use Jotadevs\OnzePlexConnector\Model\OnzePlexApi;
use Jotadevs\OnzePlexConnector\Model\PlexProductFactory;
use Magento\CatalogInventory\Model\Stock\StockItemRepository;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Psr\Log\LoggerInterface;

class CheckStockFromPlex implements ObserverInterface
{
    protected $logger;
    /** @var StockItemRepository Magento\CatalogInventory\Model\Stock\StockItemRepository */
    protected $_stockItemRepository;
    protected $apiPlex;
    protected $plexProduct;

    public function __construct(
        LoggerInterface $logger,
        StockItemRepository $stockItemRepository,
        OnzePlexApi $apiPlex,
        PlexProductFactory $plexProduct
    ) {
        $this->logger = $logger;
        $this->_stockItemRepository = $stockItemRepository;
        $this->apiPlex = $apiPlex;
        $this->plexProduct = $plexProduct;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return self
     */
    public function execute(Observer $observer)
    {
        /**
         * @var $product \Magento\Catalog\Model\Product
         */
        $product = $observer->getEvent()->getProduct();
        $plex_product = $this->plexProduct->create()->load($product->getId(), 'id_magento');

        $stock_plex = $this->apiPlex->getStockFromPlex([$plex_product->getCodproduct()]);


        $prod_plex = $this->apiPlex->processStockFromPlex($stock_plex);
        $result = $this->apiPlex->updateStockItem($prod_plex);
        $this->logger->debug("Se actualizo el Producto: " . $product->getName());
        $this->logger->debug("Stock update result: " . $result['qty_product_stock_update']);

        return $this;
    }
}
