<?php

namespace Jotadevs\OnzePlexConnector\Observer;

use Jotadevs\OnzePlexConnector\Model\OnzePlexApi;
use Jotadevs\OnzePlexConnector\Model\PlexProductFactory;
use Jotadevs\OnzePlexConnector\Model\ResourceModel\PlexProduct;
use Magento\CatalogInventory\Model\Stock\StockItemRepository;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\ManagerInterface;
use Psr\Log\LoggerInterface;

class CheckStockFromPlex implements ObserverInterface
{
    protected $logger;
    protected $apiPlex;
    protected $plexProduct;
    protected $resourceProduct;
    protected $messageManager;
    /**
     * @var \Magento\CatalogInventory\Api\StockRegistryInterface
     */
    protected $stockRegistry;
    /**
     * @var \Magento\CatalogInventory\Model\Stock\StockItemRepository
     */
    protected $stockItem;

    /**
     * CheckStockFromPlex constructor.
     * @param LoggerInterface $logger
     * @param StockItemRepository $stockItemRepository
     * @param OnzePlexApi $apiPlex
     * @param PlexProductFactory $plexProduct
     * @param PlexProduct $resourceProduct
     * @param ManagerInterface $messageManager
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
     * @param StockItemRepository $stockItem
     */
    public function __construct(
        LoggerInterface $logger,
        StockItemRepository $stockItemRepository,
        OnzePlexApi $apiPlex,
        PlexProductFactory $plexProduct,
        PlexProduct $resourceProduct,
        ManagerInterface $messageManager,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        StockItemRepository $stockItem
    ) {
        $this->logger = $logger;
        $this->_stockItemRepository = $stockItemRepository;
        $this->apiPlex = $apiPlex;
        $this->plexProduct = $plexProduct;
        $this->messageManager = $messageManager;
        $this->resourceProduct = $resourceProduct;
        $this->stockRegistry = $stockRegistry;
        $this->stockItem = $stockItem;
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
        $info = $observer->getEvent()->getInfo();

        $stockItem = $this->stockRegistry->getStockItem($product->getId(), $product->getStore()->getWebsiteId());
        $minQty = $stockItem->getMinSaleQty();
        $stockQty = $this->stockItem->get($product->getId())->getQty();

        if ($info instanceof \Magento\Framework\DataObject) {
            $request = $info;
        /// $this->logger->debug(" Es una instancia de Data Object");
        } elseif (is_numeric($info)) {
            $request = new \Magento\Framework\DataObject(['qty' => $info]);
        // $this->logger->debug(" Es numerico");
        } elseif (is_array($info)) {
            $request = new \Magento\Framework\DataObject($info);
        /// $this->logger->debug(" Es una array");
        } else {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('We found an invalid request for adding product to quote.')
            );
        }
        if (
            $minQty
            && $minQty > 0
            && !$request->getQty()
        ) {
            $request->setQty($minQty);
        }

        $plex_product = $this->plexProduct->create();
        $this->resourceProduct->load($plex_product, $product->getId(), 'id_magento');

        $this->logger->debug("Chequeando el stock de : " .
            $plex_product->getProducto() . " -- Codigo " . $plex_product->getCodproduct());

        $this->logger->debug(" Cantidad Pedida : " . $request->getQty());
        $this->logger->debug(" Cantidad Minima seteada : " . $minQty);
        $this->logger->debug(" Stock Actual : " . $stockQty);

        if (($stockQty - $request->getQty()) < 3 && ($stockQty - $request->getQty()) >= 0) {
            $stock_plex = $this->apiPlex->getStockFromPlex([$plex_product->getCodproduct()]);
            if ($stock_plex['state'] == 'success') {
                $this->logger->debug(" If call to Plex is success, do something");
                $prod_plex = $this->apiPlex->processStockFromPlex($stock_plex);
                $result = $this->apiPlex->updateStockItem($prod_plex);
                $this->logger->debug("Se actualizo el Producto: " . $product->getName());
                $this->logger->debug("Stock update result: " . $result['qty_product_stock_update']);
                $this->logger->debug("Se actualizo el Producto: " . $product->getName());
                return $this;
            } else {
                $this->logger->debug(" If call to Plex came with error send a message");
                $msg = "No se pudo agregar el producto " .
                    $plex_product->getProducto() .
                    " - Ocurrió un Error chequeando el stock disponible. Intente en unos minutos.";
                //$this->messageManager->addErrorMessage($msg);
                //LANZAR UNA Excepcion PARA NO CARGAR EL PRODUCTO
                throw new LocalizedException(__('%1', $msg));
            }
        }
    }
}
