<?php

namespace Jotadevs\OnzePlexConnector\Observer;

use Jotadevs\OnzePlexConnector\Model\OnzePlexApi;
use Jotadevs\OnzePlexConnector\Model\PlexProductFactory;
use Magento\CatalogInventory\Model\Stock\StockItemRepository;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\ManagerInterface;
use Psr\Log\LoggerInterface;

class CheckStockFromPlex implements ObserverInterface
{
    protected $logger;
    /** @var StockItemRepository Magento\CatalogInventory\Model\Stock\StockItemRepository */
    protected $_stockItemRepository;
    protected $apiPlex;
    protected $plexProduct;
    protected $messageManager;
    protected $resultRedirectFactory;

    public function __construct(
        LoggerInterface $logger,
        StockItemRepository $stockItemRepository,
        OnzePlexApi $apiPlex,
        PlexProductFactory $plexProduct,
        ManagerInterface $messageManager,
        RedirectFactory $resultRedirectFactory
    ) {
        $this->logger = $logger;
        $this->_stockItemRepository = $stockItemRepository;
        $this->apiPlex = $apiPlex;
        $this->plexProduct = $plexProduct;
        $this->messageManager = $messageManager;
        $this->resultRedirectFactory = $resultRedirectFactory;
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
        $plex_product = $this->plexProduct->create()->load($product->getId(), 'id_magento');
        $this->logger->debug("Chequeando el stock de : " .
            $plex_product->getProducto() . " -- Codigo " . $plex_product->getCodproduct() .
            "EL cliente pidio " . implode(',', $info));
        $this->logger->info(print_r($info));
        // call to plex
        $stock_plex = $this->apiPlex->getStockFromPlex([$plex_product->getCodproduct()]);
        /*Chequear la cantidad
            si la diferencia entre lo pedido al estock es igual o mayor a un umbral
           (luego que este dato sea seteable en la config) ..
            hoy lo seteamos en 3 ... no chequeamos
            esto para no ir siempre a consultar a plex.
        if ((qpedida - stock) => 3){}
        */

        if ($stock_plex['state'] == 'success') {
            $this->logger->debug(" If call to Plex is success, do something");
            $prod_plex = $this->apiPlex->processStockFromPlex($stock_plex);
            $result = $this->apiPlex->updateStockItem($prod_plex);
            $this->logger->debug("Se actualizo el Producto: " . $product->getName());
            $this->logger->debug("Stock update result: " . $result['qty_product_stock_update']);
            return $this;
        } else {
            $this->logger->debug(" If call to Plex came with error send a message");
            $msg ="No se pudo agregar el producto " .
                $plex_product->getProducto() .
                " - Ocurrió un Error chequeando el stock disponible. Intente en unos minutos.";
            $this->messageManager->addErrorMessage($msg);
            //LANZAR UNA Excepcion PARA NO CARGAR EL PRODUCTO
            throw new LocalizedException();
            //return  $this->resultRedirectFactory->create()->setPath('*/*/');
        }
    }
}
