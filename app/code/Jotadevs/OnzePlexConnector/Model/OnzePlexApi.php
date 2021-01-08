<?php

namespace Jotadevs\OnzePlexConnector\Model;

use Magento\Catalog\Api\CategoryLinkManagementInterface;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\Data\CategoryInterfaceFactory;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\Data\ProductInterfaceFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\StateException;
use Magento\Framework\HTTP\ZendClient;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\Validation\ValidationException;
use Magento\InventoryApi\Api\Data\SourceItemInterfaceFactory;
use Magento\InventoryApi\Api\SourceItemsSaveInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Psr\Log\LoggerInterface;

class OnzePlexApi
{
    protected $zendClient;
    protected $userProd = 'ecommercerp';
    protected $userDev = 'ecommerce';
    protected $passwordDev = 'e2020commerce';
    protected $passwordProd = 'e2020commercerp';
    protected $json;
    protected $plexproduct;
    protected $plexcategory;
    protected $plexoperation;
    protected $plexlaboratorio;
    protected $plex_mercadopago = 10;

    /** @var $plexorder PlexOrder */
    protected $plexorder;
    protected $logger;

    private $productFactory;
    private $productRepository;
    private $productResource;
    private $stockRegistry;

    /**
     * @var SourceItemsSaveInterface
     */
    protected $sourceItemsSave;

    /**
     * @var SourceItemInterfaceFactory
     */
    protected $sourceItemFactory;
    private $categoryFactory;
    private $categoryRespository;

    /* @var $_orderCollectionFactory \Magento\Sales\Model\ResourceModel\Order\CollectionFactory */
    private $orderCollectionFactory;

    /** @var CustomerRepositoryInterface */
    private $_customerRepository;
    /** @var OrderRepositoryInterface */
    private $_orderRepository;

    private $state;
    private $uriDev = 'http://170.0.92.97/onzews/';
    private $uriProd = 'http://gralpaz.plexonzecenter.com.ar:8081/onzews/';

    protected $categoryLinkManagement;
    private $timezone;

    public function __construct(
        PlexOperationFactory $plexoperation,
        PlexProductFactory $plexproduct,
        PlexCategoryFactory $plexcategory,
        PlexOrderFactory $plexorder,
        PlexLaboratorioFactory $plexlaboratorio,
        ZendClient $zendClient,
        Json $json,
        ProductInterfaceFactory $productFactory,
        ProductRepositoryInterface $productRepository,
        ProductResource $productResource,
        CategoryInterfaceFactory $categoryFactory,
        CategoryRepositoryInterface $categoryRepository,
        StockRegistryInterface $stockRegistry,
        CategoryLinkManagementInterface $categoryLinkManagement,
        \Magento\Framework\App\State $state,
        CollectionFactory $orderCollectionFactory,
        OrderRepositoryInterface $orderRepository,
        CustomerRepositoryInterface $customerRepository,
        SourceItemsSaveInterface $sourceItemsSave,
        SourceItemInterfaceFactory $sourceItemFactory,
        LoggerInterface $logger,
        TimezoneInterface $timezone
    ) {
        $this->zendClient = $zendClient;
        $this->json = $json;
        $this->plexproduct = $plexproduct;
        $this->plexcategory = $plexcategory;
        $this->plexoperation = $plexoperation;
        $this->plexorder = $plexorder;
        $this->plexlaboratorio = $plexlaboratorio;
        $this->productFactory = $productFactory;
        $this->productRepository = $productRepository;
        $this->productResource = $productResource;
        $this->categoryFactory = $categoryFactory;
        $this->categoryRespository = $categoryRepository;
        $this->stockRegistry = $stockRegistry;
        $this->categoryLinkManagement = $categoryLinkManagement;
        $this->state = $state;
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->_customerRepository = $customerRepository;
        $this->_orderRepository = $orderRepository;
        $this->logger = $logger;
        $this->sourceItemFactory = $sourceItemFactory;
        $this->sourceItemsSave = $sourceItemsSave;
        $this->timezone = $timezone;
    }

    public function getProductsOnexPlex(\DateTime $fechadecambio = null, array $ids = null)
    {
        $parameters = [];
        $fechadecambio ?
            $parameters = array_merge($parameters, ['fechacambio' => $fechadecambio->format('Ymd')])
            :
            null;
        $ids ? $parameters = array_merge($parameters, ['idproducto' => implode(',', $ids)]) : null;
        //var_dump($parameters);
        $this->zendClient->resetParameters();
        try {
            $this->zendClient->setUri($this->uriProd . "ec_getproductos");
            $this->zendClient->setMethod(ZendClient::GET);
            $this->zendClient->setAuth($this->userProd, $this->passwordProd);
            $this->zendClient->setParameterGet($parameters);
            $this->zendClient->setHeaders(
                [
                    'Content-Type' => 'application/json'
                ]
            );
            $response = $this->zendClient->request();
            $response_array = $this->json->unserialize($response->getBody());
            return [
                'state' => 'success',
                'result' => $response_array['response']['content']['productos']
            ];
        } catch (\Zend_Http_Client_Exception $e) {
            return [
                'state' => 'error',
                'code' => $e->getCode(),
                'message' => $e->getMessage()
            ];
        }
    }
    public function getRubrosOnexPlex()
    {
        $this->zendClient->resetParameters();
        try {
            $this->zendClient->setUri($this->uriProd . "ec_getrubros");
            $this->zendClient->setMethod(ZendClient::GET);
            $this->zendClient->setAuth($this->userProd, $this->passwordProd);
            $this->zendClient->setHeaders(['Content-Type' => 'application/json']);
            $response = $this->zendClient->request();
            $response_array = $this->json->unserialize($response->getBody());
            return [
               'state' => 'success',
               'result' => $response_array['response']['content']['rubros']
            ];
        } catch (\Zend_Http_Client_Exception $e) {
            return [
               'state' => 'error',
               'code' => $e->getCode(),
               'message' => $e->getMessage()
            ];
        }
    }
    public function getSubRubrosOnexPlex()
    {
        $this->zendClient->resetParameters();
        try {
            $this->zendClient->setUri($this->uriProd . "ec_getsubrubros");
            $this->zendClient->setMethod(ZendClient::GET);
            $this->zendClient->setAuth($this->userProd, $this->passwordProd);
            $this->zendClient->setHeaders(['Content-Type' => 'application/json']);

            $response = $this->zendClient->request();
            $response_array = $this->json->unserialize($response->getBody());
            return [
                'state' => 'success',
                'result' => $response_array['response']['content']['subrubros']
            ];
        } catch (\Zend_Http_Client_Exception $e) {
            return [
                'state' => 'error',
                'code' => $e->getCode(),
                'message' => $e->getMessage()
            ];
        }
    }
    public function getGruposPlex()
    {
        $this->zendClient->resetParameters();
        try {
            $this->zendClient->setUri($this->uriProd . "ec_getgrupos");
            $this->zendClient->setMethod(ZendClient::GET);
            $this->zendClient->setAuth($this->userProd, $this->passwordProd);
            $this->zendClient->setHeaders(['Content-Type' => 'application/json']);

            $response = $this->zendClient->request();
            $response_array = $this->json->unserialize($response->getBody());
            return [
                'state' => 'success',
                'result' => $response_array['response']['content']['grupos']
            ];
        } catch (\Zend_Http_Client_Exception $e) {
            return [
                'state' => 'error',
                'code' => $e->getCode(),
                'message' => $e->getMessage()
            ];
        }
    }
    public function getLaboratoriosFromPlex()
    {
        $this->zendClient->resetParameters();
        try {
            $this->zendClient->setUri($this->uriProd . "ec_getlaboratorios");
            $this->zendClient->setMethod(ZendClient::GET);
            $this->zendClient->setAuth($this->userProd, $this->passwordProd);
            $this->zendClient->setHeaders(['Content-Type' => 'application/json']);
            $response = $this->zendClient->request();
            $response_array = $this->json->unserialize($response->getBody());
            return [
                'state' => 'success',
                'result' => $response_array['response']['content']['laboratorios']
            ];
        } catch (\Zend_Http_Client_Exception $e) {
            return [
                'state' => 'error',
                'code' => $e->getCode(),
                'message' => $e->getMessage()
            ];
        }
    }
    public function importProductsFromPlex()
    {

        //veo ultima fecha de producto creado para tomar desde esa fehcha productos nuevos
        $op_last_product = $this->plexproduct->create()->getCollection()->getLastItem();
        //llamamos a la RestApi del Erp y traemos TODOS los productos.
        $op_last_product->getCreateAt() ?
            $result = $this->getProductsOnexPlex(
                \DateTime::createFromFormat(
                    'Y-m-d H:i:s',
                    $op_last_product->getCreateAt()
                )
            ) :
            $result = $this->getProductsOnexPlex();

        //si hay resultados proseguimos
        if ($result['state'] == 'success') {
            if (!empty($result['result'])) {
                $op_products = [];
                //recorro el array de productos para analizar si ya lo tengo en base
                foreach ($result['result'] as $op_api_product) {
                    //verifico si no existe ya en la tabla de op por id y por nombre por que Plex maneja productos duplicados
                    $op_product_by_id = $this->plexproduct->create()->load(
                        $op_api_product['codproducto'],
                        'codproduct'
                    );
                    $op_product_by_name = $this->plexproduct->create()->load(
                        $op_api_product['producto'],
                        'producto'
                    );
                    if ($op_product_by_id->isEmpty()) {
                        if ($op_product_by_name->isEmpty()) {
                            $op_product = $this->plexproduct->create();
                            $this->logger->debug(" Creando Producto: " . $op_api_product['codproducto']);
                            foreach ($op_api_product as $key => $value) {
                                if ($key == 'codproducto') {
                                    $op_product->setSku($value);
                                    $op_product->setCodproduct($value);
                                }
                                ($key == 'producto') ? $op_product->setProducto($value) : null;
                                ($key == 'precio') ? $op_product->setPrecio($value) : null;
                                ($key == 'rubro') ? $op_product->setRubro($value) : null;
                                ($key == 'subrubro') ? $op_product->setSubrubro($value) : null;
                                ($key == 'idrubro') ? $op_product->setIdrubro($value) : null;
                                ($key == 'idSubro') ? $op_product->setIdrubro($value) : null;
                                ($key == 'stock') ? $op_product->setStock($value) : null;
                                ($key == 'idlaboratorio') ? $op_product->setIdLaboratorio($value) : null;
                                if ($key == 'grupos') {
                                    foreach ($value as $gr) {
                                        foreach ($gr as $key_gr => $value_gr) {
                                            ($key_gr == 'idgrupo') ? $op_product->setIdgrupo($value_gr) : null;
                                            ($key_gr == 'grupo') ? $op_product->setGrupo($value_gr) : null;
                                        }
                                    }
                                }
                            }
                            $op_product->setIsObjectNew(true)
                            ->setIsOpEnabled(true)
                            ->setObservations("Producto importado el " . date('Y-m-d H:i:s'));
                            $op_product->save();
                            $op_products[] = $op_product;
                        } else {
                            $this->logger
                                ->error("Productos Duplicados  - " .
                                    $op_api_product['codproducto'] . " - " .
                                    $op_api_product['producto']);
                        }
                    }
                }
                return [
                    'state' => 'success',
                    'received' => count($result['result']),
                    'new' => count($op_products)
                ];
            } else {
                return[
                    'state' => 'success',
                    'received' => 0,
                    'new' => 0
                ];
            }
        } else {
            return [
                'state' => 'error',
                'message' => $result['message']
            ];
        }
    }

    public function importLaboratoriosFromPlex()
    {
        //llamamos a la RestApi del Erp y traemos TODOS los laboratorios.
        $result = $this->getLaboratoriosFromPlex();
        //verificar conexion
        if ($result['state'] == 'success') {
            //verificar resultados
            if (!empty($result['result'])) {
                $op_laboratorios = [];
                //recorro el array de productos para analizar si ya lo tengo en base
                foreach ($result['result'] as $op_api_laboratorio) {
                    //verifico si no existe ya en la tabla de op
                    /** @var $op_laboratorio PlexLaboratorio */
                    $op_laboratorio = $this->plexlaboratorio->create()
                        ->load($op_api_laboratorio['idlaboratorio'], 'id_plex');
                    if (empty($op_laboratorio->toArray())) {
                        /*si no existe lo cargo TODO VER $op_product... */
                        foreach ($op_api_laboratorio as $key => $value) {
                            ($key == 'idlaboratorio') ? $op_laboratorio->setIdPlex($value) : null;
                            ($key == 'laboratorio') ? $op_laboratorio->setName($value) : null;
                        }
                        $op_laboratorio
                            ->setIsSynchronized(true)
                            ->setIsObjectNew(true);
                        $op_laboratorio->save();
                        $op_laboratorios[] = $op_laboratorio;
                    }
                }
                return [
                    'state' => 'success',
                    'received' => count($result['result']),
                    'new' => count($op_laboratorios),
                    'message' => "Se recibieron desde Plex " .
                        count($result['result']) . "
                        registros 'Laboratorio', Nuevos ingresados " . count($op_laboratorios)
                ];
            } else {
                return[
                    'state' => 'success',
                    'received' => 0,
                    'new' => 0,
                    'message' => 'Se recibieron desde Plex 0 registros de Laboratorio'
                ];
            }
        } else {
            return [
                'state' => 'error',
                'message' => "Error al importar desde Plex registros Laboratorios, mensaje " . $result['message']
            ];
        }
    }

    public function convertToMagentoProduct()
    {
        //Busco todos los productos obtenidos en OnexPlex filtrando por los no sincronizados
        $new_op_products_collection = $this->plexproduct->create()->getCollection();
        $new_op_products_collection
            ->addFieldToFilter('is_synchronized', ['eq' => false])
            ->load();
        $this->logger->debug("Se encontraron "
            . count($new_op_products_collection->getAllIds()) . " productos para sincronizar con Magento");
        //Verifico que existan productos Plex en la tabla
        if (!empty($new_op_products_collection->getColumnValues('id'))) {
            //seteo area de ejecucion como global front y backend
            //$this->state->setAreaCode(\Magento\Framework\App\Area::AREA_GLOBAL); lo saco de aca y lo paso al cron
            /** los convierto a productos magento
             *  recorro los nuevos productos obtenidos y por cada uno los inserto
             */
            foreach ($new_op_products_collection as $new_op_product) {
                $this->logger->debug(
                    " Convirtiendo producto Plex Id: " . $new_op_product->getId()
                    . " SKU: " . $new_op_product->getSku()
                    . " Producto: " . $new_op_product->getProducto()
                );
                /** @var ProductInterface $mag_product */
                $mag_product = $this->productFactory->create();
                $mag_product->setSku($new_op_product->getSku())
                        ->setName($new_op_product->getProducto())
                        ->setTypeId(\Magento\Catalog\Model\Product\Type::TYPE_SIMPLE)
                        ->setVisibility(4)
                        ->setAttributeSetId(4)
                        ->setPrice($new_op_product->getPrecio())
                        ->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED);
                $plex_laboratorio = $this->plexlaboratorio->create()
                    ->load($new_op_product->getIdLaboratorio(), 'id_plex');
                $mag_product
                    ->setCustomAttribute('laboratorio', $plex_laboratorio->getName())
                    ->setCustomAttribute('rubro_plex', $new_op_product->getRubro())
                    ->setCustomAttribute('subrubro_plex', $new_op_product->getSubrubro())
                    ->setCustomAttribute('grupo_plex', $new_op_product->getGrupo());
                try {
                    $mag_product = $this->productRepository->save($mag_product);
                } catch (CouldNotSaveException $e) {
                    $this->logger->error("No se Pudo Convertir el producto Plex: " .
                        $new_op_product->getSku() . "Error: " . $e->getMessage());
                    continue;
                }

                $stockItem = $this->stockRegistry->getStockItemBySku($mag_product->getSku());
                $stockItem->setIsInStock(true)->setQty($new_op_product->getStock());
                $stockItem->save();
                $new_op_product->setIdMagento($mag_product->getId())
                        ->setIsSynchronized(true)
                        ->save();
            }
            return [
                'state' => 'success',
                'qty' => count($new_op_products_collection),
                'message' => "Something are converted"
            ];
        } else {
            return [
                'state' => 'success',
                'qty' => count($new_op_products_collection),
                'message' => 'Nothing for convert'
            ];
        }
    }
    /**
     * Area de Pedidos...
     * 1- preparedOrderToSync -- Tomo todos los pedidos completados en estado "pending"  y los grabo en la tabla intermedia prepareOrderToSync
     * 2- Busco en tabla intermerdia sin sincronizar y sincronizo con Plex  (post para crear el pedido e informar el pago)
     *      2.1 Busco todas las ordenes en plexOrder sin estar sincronizadas y traigo desde magento los datos necesarios para sincronizar --> getMagentoOrdersToSync.
     *      2.2 Envio de a una Post al ws de Plex.     *
     */
    /**
     * @param Order|null $order
     * @return array
     * @throws \Exception
     */
    public function prepareOrderToSync(Order $order = null)
    {
        $orders_syncs = [];
        if (!$order) {
            $OrderCollection = $this->orderCollectionFactory->create()
                ->addFieldToSelect('*')
                ->addFieldToFilter('status', ['eq' => 'pending']);

            foreach ($OrderCollection as $order) {
                $plexOrder = $this->plexorder->create()->load($order->getId(), 'id_magento');
                $newPlexOrder = $this->checkAndSavePlexOrder($plexOrder, $order->getId());
                $newPlexOrder ? $orders_syncs[] = $newPlexOrder : null;
            }
        } else {
            $plexOrder = $this->plexorder->create()->load($order->getId(), 'id_magento');
            $newPlexOrder = $this->checkAndSavePlexOrder($plexOrder, $order->getId());
            $newPlexOrder ? $orders_syncs[] = $newPlexOrder : null;
        }
        return[
            'status' => 'ok',
            'qty' => count($orders_syncs)
        ];
    }

    public function checkAndSavePlexOrder(PlexOrder $plexOrder, $id_magento)
    {
        if ($plexOrder->isEmpty()) {
            $newPlexOrder = $this->plexorder->create();
            $newPlexOrder->setIdMagento($id_magento)
                ->setIsSynchronized(false);
            $newPlexOrder->setIsObjectNew(true);
            $newPlexOrder->save();
            return $newPlexOrder;
        }
        return null;
    }

    /**
     * @param null $order_id
     * @return array
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getMagentoOrdersToSync($order_id = null)
    {
        if (!$order_id) {
            $plexOrderCollection = $this->plexorder->create()->getCollection()
                ->addFieldToFilter('is_synchronized', ['eq' => false])
                //->addFieldToFilter('id_magento', ['eq' => '16'])
                ->load();
            $plexOrderToSync_Ids = $plexOrderCollection->getColumnValues('id_magento');
        } else {
            // TODO verificar si la orden ya esta sincronizada
            $plexOrderCollection = $this->plexorder->create()->getCollection()
                ->addFieldToFilter('is_synchronized', ['eq' => false])
                ->addFieldToFilter('id', ['eq' => $order_id])
                ->load();
            $plexOrderToSync_Ids = $plexOrderCollection->getColumnValues('id_magento');
        }
        //var_dump($plexOrderToSync_Ids);
        if (!empty($plexOrderToSync_Ids)) {
            $magOrders = [];
            $magOrderCollection = $this->orderCollectionFactory->create()
                ->addAttributeToSelect("*")
                ->addFieldToFilter('entity_id', ['in' => $plexOrderToSync_Ids]);
            // var_dump($magOrderCollection->toArray());
            $rs_order = [];
            /** @var Order $magOrder */
            foreach ($magOrderCollection as $magOrder) {
                $customer = $this->_customerRepository->getById($magOrder->getCustomerId());
                /** @var \Magento\Sales\Api\Data\OrderAddressInterface $shippingAddress */
                $shippingAddress = $magOrder->getShippingAddress();
                $line = [];
                /** @var \Magento\Sales\Model\Order\Item $item */
                foreach ($magOrder->getAllVisibleItems() as $item) {
                    /** @var \Magento\Catalog\Model\Product $product */
                    $product = $item->getProduct();
                    if ($product) {
                        $line [] = [
                        'line_amount' => $item->getRowTotalInclTax(),
                        'line_unit_amount' => $item->getPriceInclTax(),
                        'line_total_descuento' => 0,//$item->getDiscountAmount(), // TO VIEW SACO EL DTO PORQUE PLEX NO DEJA DESCUENTOS NATIVOS MAGENTO POR LINEA
                        'prod_qty' => $item->getQtyOrdered(),
                        'qty_ordered' => $item->getOrderId(),
                        'product_id' => $product->getId(),
                        'product_sku' => $product->getSku(),
                        'product_name' => $product->getName()
                        ];
                    } else {
                        var_dump($item->getName());
                    }
                }
                ($magOrder->getShippingMethod() == 'storepickup_')
                    ? $tipo_entrega = 'R'
                    : $tipo_entrega = 'E';
                ($magOrder->getShippingMethod() == 'storepickup_')
                    ? $observacion = $magOrder->getShippingDescription()
                    : $observacion = null;
                //TODO ver el id de sucursal
                $rs_order = [
                    'order_id' => $magOrder->getId(),
                    'order_cliente_nombre' => $magOrder->getCustomerName(),
                    'order_cliente_mail' => $magOrder->getCustomerEmail(),
                    'order_cliente_tdoc' => 'DNI',
                    'order_cliente_doc' => $customer->getCustomAttribute('doc')->getValue(),
                    'order_cliente_domicilio' => $shippingAddress->getStreet(),
                    'order_cliente_codpostal' => $shippingAddress->getPostcode(),
                    'order_cliente_ciudad' => $shippingAddress->getCity(),
                    'order_cliente_provincia' => $shippingAddress->getRegion(),
                    'order_cliente_telefono' => $shippingAddress->getTelephone(),
                    'order_observacion' => $observacion,
                    'order_tipo_entrega' => $tipo_entrega,
                    'order_tipo_pago' => 'L',
                    'order_costo_envio' => $magOrder->getShippingAmount(),
                    'order_cupon_dto_codigo' => $magOrder->getDiscountDescription(),
                    'order_cupon_dto_importe' => $magOrder->getDiscountAmount(),
                    'order_amount_total' => $magOrder->getGrandTotal(),
                    'order_total_items' => $magOrder->getTotalItemCount(),
                    'lineas' => $line
                ];
                $magOrders[] = $rs_order;
            }
            return  [
                'status' => 'ok',
                'orders_to_sync' => $magOrders,
                'qty_to_sync' => count($magOrders)
            ];
        }
        return [
            'status' => 'ok',
            'qty_to_sync' => 0
        ];
    }
    public function postOrderToPlex($magOrder)
    {
        $lineas = [];

        foreach ($magOrder['lineas'] as $linea) {
            $lineas[] = [
            'codproducto' => $linea['product_sku'],
            'producto' => $linea['product_name'],
            'cantidad' => (int)$linea['prod_qty'],
            'precio' => $linea['line_unit_amount'],
            'idpromo' => "",
            'promo' => "",
            'totaldescuento' => $linea['line_total_descuento'],
            ];
        }
        $parameters = [
            'cli_mail' => $magOrder['order_cliente_mail'],
            'cli_tdoc' => 'DNI',
            'cli_doc' => $magOrder['order_cliente_doc'],
            'cli_nombre' => $magOrder['order_cliente_nombre'],
            'cli_domicilio' => $magOrder['order_cliente_domicilio'][0],
            'cli_codpostal' => $magOrder['order_cliente_codpostal'],
            'cli_localidad' => $magOrder['order_cliente_ciudad'],
            'cli_provincia' => $magOrder['order_cliente_provincia'],
            'cli_telefono' => $magOrder['order_cliente_telefono'],
            'tipoentrega' => $magOrder['order_tipo_entrega'],
            'tipopago' => $magOrder['order_tipo_pago'],
            'observacion' => $magOrder['order_observacion'],
            'idsucursal' => "2", //TODO se hardcorea a id 2 de acuedo a mail de Galbo
            'external_id' => $magOrder['order_id'],
            'external_sw' => "MAGENTO",
            'costo_envio' => $magOrder['order_costo_envio'],
            'cupondto_codigo' => $magOrder['order_cupon_dto_codigo'],
            'cupondto_importe' => $magOrder['order_cupon_dto_importe'],
            'productos' => $lineas
            ];

        //return json_encode($parameters);
        $this->zendClient->resetParameters();

        try {
            $this->zendClient->setUri('http://gralpaz.plexonzecenter.com.ar:8081/onzews');
            $this->zendClient->setMethod(ZendClient::POST);
            $this->zendClient->setAuth($this->userProd, $this->passwordProd);

            $data = [
                'request' => [
                    'type' => 'EC_CREARPEDIDO',
                    'content' => $parameters
                ]
            ];
            $this->zendClient->setHeaders(
                [
                     'Content-Type' => 'application/json',
                 ]
            );
            $this->zendClient->setRawData(json_encode($data));
            $response = $this->zendClient->request();
            $response_array = $this->json->unserialize($response->getBody());

            if ($response_array['response']['respcode'] == '0') {
                $mag_order = $this->_orderRepository->get($magOrder['order_id']);
                $plex_order = $this->plexorder->create()->load($magOrder['order_id'], 'id_magento');
                $plex_order
                   ->setIdPlex($response_array['response']['content']['idpedido'])
                   ->setIsSynchronized(true);
                $plex_order->save();
                $mag_order->setStatus('sync_plex')->setState('procesing');
                $this->_orderRepository->save($mag_order);
                return [
                    'state' => 'success',
                    'message' => $response_array['response']['content']['idpedido']
                ];
            } else {
                return [
                   'state' => 'error',
                   'message' => $response_array['response']['respmsg']
                ];
            }
        } catch (\Zend_Http_Client_Exception $e) {
            return [
               'state' => 'error',
               'code' => $e->getCode(),
               'message' => $e->getMessage()
            ];
        }
    }

    /**
     * @param null $magento_order_id
     * @return array|string[]
     * @throws \Exception
     */
    public function informPaymentToPlex($magento_order_id = null)
    {
        /**
         * Este metodo en esta version solo trabaja con MercadoPago
         * No puedo buscar los medios de pagos... no sirve
         * tengo que harcodear *** TODO despues metodo de descargar y vincular con mercado pago
           1. Busco todas las ordenes magento que tienen el estado sync_plex
         * 2. Recorro las ordenes obtenidas
         *      2.1 verifico si tiene pago realizado
         *      2.1 si lo tiene informo pago a plex y actualizo plex y orden magenot a estado completo.
         */

        $mag_orders_collection = $this->orderCollectionFactory->create()
            ->addAttributeToSelect("*")
            ->addFieldToFilter('status', ['eq' => 'sync_plex']);
        $magento_order_id ?
            $mag_orders_collection->addFieldToFilter('entity_id', ['eq' => $magento_order_id]) :
            null;

        if (!empty($mag_orders_collection)) {
            $order_posted = [];
            $order_not_posted = [];
            /** @var Order $mag_order */
            foreach ($mag_orders_collection as $mag_order) {
                $pagos = [];
                /** Verifico que el pago este realizado por Mercado Pago,
                 * verifico que este aprovado y acreditado asi abanzo
                 */
                if (
                    in_array(
                        $mag_order->getPayment()->getMethod(),
                        ['mercadopago_custom','mercadopago_customticket']
                    ) and
                    $mag_order->getPayment()
                        ->getAdditionalInformation()['paymentResponse']['status'] == 'approved' and
                    $mag_order->getPayment()
                        ->getAdditionalInformation()['paymentResponse']['status_detail'] == 'accredited'
                ) {
                    $plex_order = $this->plexorder->create()->load($mag_order->getId(), 'id_magento');
                    $pagos [] = [
                        'idmediodepago' => $this->plex_mercadopago,
                        'idtarjeta' => "",
                        'importetotal' => $mag_order->getPayment()->getAdditionalInformation()['total_amount'],
                            //- $mag_order->getDiscountAmount()
                            //+ $mag_order->getShippingAmount(),
                        //'importedto' =>  $mag_order->getDiscountAmount(), //TODO CON MERCADO PAGO NO
                        //'motivodto' => '', // TODO Con mercado pago no se informa esto
                        'codoperacion' => $mag_order->getPayment()->getAdditionalInformation()['paymentResponse']['id'],
                        //'pagadoamcdopago' => $mag_order->getPayment()->getAdditionalInformation()['total_amount'],
                        //'descuento' => $mag_order->getDiscountAmount(),
                        'envio' => $mag_order->getShippingAmount(),
                        //'aditionalinfo' => $mag_order->getPayment()->getAdditionalInformation(),
                        //'additionaldata' =>$mag_order->getPayment()->getAdditionalData()
                    ];
                    $request = [
                        'idpedido' => $plex_order->getIdPlex(),
                        'pagos' => $pagos
                    ];
                    $data = [
                        'request' => [
                            'type' => 'EC_INFORMARPAGO',
                            'content' => $request
                        ]
                    ];
                    //var_dump(json_encode($data));
                    $rs = $this->postPaymentToOnexPlex($data);
                    if ($rs['state'] == 'ok') {
                        if ($rs['rta']['response']['respcode'] == '0') {
                            $plex_order->setIsPaymentInformed(true);
                            $plex_order->save();
                            $mag_order->setStatus('sync_plex_completed')->setState('complete');
                            $this->_orderRepository->save($mag_order);
                            $order_posted [] = [
                            'magento_id' => $mag_order->getId(),
                            'reason' => $rs['rta']['response']['respmsg']
                            ];
                        } else {
                            $order_not_posted [] =
                            [
                            'magento_id' => $mag_order->getId(),
                            'reason' => $rs['rta']['response']['respmsg']
                            ];
                        }
                    } else {
                        $order_not_posted [] = [
                        'magento_id' => $mag_order->getId(),
                        'reason' => $rs['message']
                        ];
                    }
                } else {
                    $order_not_posted [] = [
                        'magento_id' => $mag_order->getId(),
                        'reason' => 'Pago no acreditado o aprobado por Mercadopago'
                    ];
                }
            }
            return [
                'status' => 'ok',

                    'q_order_posted_to_plex' => count($order_posted),
                    'q_order_not_posted_to_plex' => count($order_not_posted),
                    'order_posted_to_plex' => $order_posted,
                    'order_not_posted_to_plex' => $order_not_posted

            ];
        } else {
            return [
                'status' => 'fail',
                'msg' => "Nothing to send to Plex"
            ];
        }
    }
    public function postPaymentToOnexPlex(array $data)
    {
        $this->zendClient->resetParameters();
        try {
            $this->zendClient->setUri('http://gralpaz.plexonzecenter.com.ar:8081/onzews');
            $this->zendClient->setMethod(ZendClient::POST);
            $this->zendClient->setAuth($this->userProd, $this->passwordProd);
            $this->zendClient->setHeaders(
                [
                    'Content-Type' => 'application/json',
                ]
            );
            $this->zendClient->setRawData(json_encode($data));
            $response = $this->zendClient->request();
            $response_array = $this->json->unserialize($response->getBody());
            return [
                'state' => 'ok',
                'rta' => $response_array
            ];
        } catch (\Zend_Http_Client_Exception $e) {
            return [
                'state' => 'error',
                'code' => $e->getCode(),
                'message' => $e->getMessage()
            ];
        }
    }
    /**
     * Actualizacion de Stock
     *  1. Get Stock From Plex va a obtener el stock de los productos desde la Api de Plex
     *  2. processStock from plex toma como entrada el resultset de una consulta de stock
     *  3. update stock toma arrat de productos plex (q devuelve process stock)
     */
    public function getStockFromPlex(array $ids)
    {
        $parameters = [];
        $this->zendClient->resetParameters();
        $idstring = null;
        foreach ($ids as $key => $value) {
            if (array_key_last($ids) == $key) {
                $idstring .= $value;
            } else {
                $idstring .= $value . ',';
            }
        }
        $parameters = array_merge($parameters, ['idproducto' => $idstring, 'idsucursal' => 2]);
        try {
            $this->zendClient->setUri($this->uriProd . "ec_getstock");
            $this->zendClient->setMethod(ZendClient::GET);
            $this->zendClient->setAuth($this->userProd, $this->passwordProd);
            $this->zendClient->setParameterGet($parameters);
            $this->zendClient->setHeaders(
                [
                    'Content-Type' => 'application/json'
                ]
            );
            $response = $this->zendClient->request();
            $response_array = $this->json->unserialize($response->getBody());
            return [
                'state' => 'success',
                'result' => $response_array['response']['content']['productos']
            ];
        } catch (\Zend_Http_Client_Exception $e) {
            return [
                'state' => 'error',
                'code' => $e->getCode(),
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * @param array $plex_stock_rs
     * @return array|bool
     * @throws \Exception
     */
    public function processStockFromPlex(array $plex_stock_rs)
    {
        if ($plex_stock_rs['state'] == 'success') {
            $rs = $plex_stock_rs['result'];
            //El rs es un array de resultado con el contenido del producto y el stock por sucursal.
            $productos = [];
            foreach ($rs as $producto) {
                $prod = [];
                $producto_plex = $this->plexproduct->create()->load($producto['codproducto'], 'codproduct');
                $prod['id_magento'] = $producto_plex->getIdMagento();
                foreach ($producto as $key => $value) {
                    if ($key == 'stock') {
                        foreach ($value as $stock) {
                            $prod['cantidad'] = $stock['cantidad'];
                            $producto_plex->setStock($stock['cantidad']);
                        }
                    } else {
                        $prod[$key] = $value;
                    }
                }
                $producto_plex->save();
                $productos[] = $prod;
            }
            return $productos;
        } else {
            return false;
        }
    }

    /**
     * @param $productos
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function updateStockItem($productos)
    {
        $sourceItems = [];
        foreach ($productos as $producto) {
            $sourceItem = $this->sourceItemFactory->create();
            $sourceItem->setSourceCode('default');
            $sourceItem->setSku($producto['codproducto']);
            if ($producto['cantidad'] > 0) {
                $sourceItem->setQuantity($producto['cantidad']);
                $sourceItem->setStatus(1);
            } else {
                $sourceItem->setQuantity(0);
                $sourceItem->setStatus(0);
            }
            $sourceItems[] = $sourceItem;
        }
        try {
            $this->sourceItemsSave->execute($sourceItems);
        } catch (CouldNotSaveException $e) {
        } catch (InputException $e) {
        } catch (ValidationException $e) {
        }
        return [
            'state' => 'success',
            'qty_product_stock_update' => count($sourceItems)
        ];
    }

    /**
     * Update Info of products
     */
    /*public function updateProductsFromPlex()
    {
        $messages = [];
        for ($i = 1; $i <= $pages; $i++) {
            $products_to_update = $this->plexproduct->create()->getCollection()
                ->addFieldToFilter('is_synchronized', ['eq' => true])
                ->setPageSize(400);
            $this->logger->info(" || Jotadevs Update Product || Comenzando con Página nro.: " . $i);
            $products_to_update->setCurPage($i);
            $this->logger->info(" || Jotadevs Update Product || Corroboro Página nro.: "
                . $products_to_update->getCurPage());
            $this->logger->info(" || Jotadevs Update Product || Cantidad de Productos a consultar: "
                . count($products_to_update->getColumnValues('codproduct')));
            //llamamos a la RestApi del Erp y traeos los productos.
            $result = $this->getProductsOnexPlex(
                null,
                $products_to_update->getColumnValues('codproduct')
            );
            //Verifico que la conexion con la API
            if ($result['state'] == 'success') {
                //Verifico que vuelvan resultados
                if (!empty($result['result'])) {
                    ///aaacaaa
                        //Actualización en el Producto Magento
                        /** @var ProductInterface $mag_product */
    /*

                    }
                    // Verifico los productos que cambiaron el estado de publicados en Plex. Como?
                    // Comparando los que envíe y los qye recibí
                    $products_to_disabled = array_diff(
      $products_to_update->getColumnValues('codproduct'),
      $op_products_codes
                    );
                    if (count($products_to_disabled) > 0) {
      foreach ($products_to_disabled as $product_to_disable) {
          //cambio estado deshabilitado en Modelo Plex
          $op_product_to_disabled = $this->plexproduct->create()
              ->load($product_to_disable, 'codproduct');
          if ($op_product_to_disabled->getIsOpEnabled()) {
              $op_product_to_disabled
                  ->setIsOpEnabled(false)
                  ->setObservations(" Producto desactivado desde Plex el " . date('Y-m-d H:i:s'))
                  ->save();

              //actualizo en Magento el status
              /** @var ProductInterface $mag_product_to_disabled */
    /*$mag_product_to_disabled = $this->productRepository->get(
        $op_product_to_disabled->getSku(),
        true,
        0,
        true
    );
    $mag_product_to_disabled->setStatus(Product\Attribute\Source\Status::STATUS_DISABLED);
    $this->logger
        ->info(" || Jotadevs Update Product || Deshabilitando en Magento producto: "
        . $mag_product_to_disabled->getId() . " - >>"
        . $mag_product_to_disabled->getStatus());
    $this->productRepository->save($mag_product_to_disabled);
                            }
                        }
                    }

                    $message = " Products Sended: " . count($products_to_update->getColumnValues('codproduct')) .
                               " Products Received: " . count($result['result']) .
                               " Products Updated: " . count($op_products) .
                               " Products Disabled: " . count(
       array_diff(
           $products_to_update->getColumnValues('codproduct'),
           $op_products_codes
       )
                               );
                    $this->logger->info(" || Jotadevs Update Product || " . $message);
                    $messages [$i] = [
                        'page ' . $i => [
                            'state' => 'success',
                            'received' => count($result['result']),
                            'new' => count($op_products),
                            'message' => $messages
                        ]
                    ];
                } else {
                    $message = "Product Received: 0  Products Updated: 0";
                    $this->logger->info(" || Jotadevs Update Product || " . $message);
                    $messages[$i] = [
                        'page ' . $i => [
                            'state' => 'success',
                            'received' => 0,
                            'new' => 0,
                            'message' => $message
                        ]
                    ];
                }
            } else {
                $this->logger->info(" || Jotadevs Update Product || " . $result['message']);
                $messages[$i] = [
                    'page ' . $i => [
                        'state' => 'error',
                        'message' => $result['message']
                    ]
                ];
            }
        }
        return $messages;
    }*/
    /**
     * @param bool $evaulate_price_option
     * @return array
     * @throws \Exception
     */
    public function updateProductsOrchestor(bool $evaulate_price_option = null)
    {
        $total_products_disabled = [];
        $total_products_disabled_new = [];
        $total_products_disabled_for_price = [];
        $total_products_updated = [];
        $total_products_requested = [];
        $messages = [];
        $total_time = $this->timezone->date();
        //Envio un prd dummy para testear ws
        $ws_plex_status = $this->getProductsOnexPlex(null, [1101]);
        if ($ws_plex_status['state'] == 'success') {
            //busco los productos en base intermedia.
            $products_to_update = $this->plexproduct->create()->getCollection()
                ->addFieldToFilter('is_synchronized', ['eq' => true])
                ->setPageSize(400);
            $pages = $products_to_update->getLastPageNumber();
            for ($i = 1; $i <= $pages; $i++) {
                $products_to_update = $this->plexproduct->create()->getCollection()
                    ->addFieldToFilter('is_synchronized', ['eq' => true])
                    ->setPageSize(400);
                $this->logger->info(" || Jotadevs Update Product || Comenzando con Página nro.: " . $i);
                $products_to_update->setCurPage($i);
                //llamamos a la RestApi del Erp y traeos los productos.
                $result = $this->getProductsOnexPlex(
                    null,
                    $products_to_update->getColumnValues('codproduct')
                );

                if ($result['state'] == 'success') {
                    $rs_process = $this->processUpdateProductsFromPlex($result['result'], $evaulate_price_option);
                    //Analizar los que no recibi, es decir que fueron deshabilitados en Plex
                    $products_to_disabled = array_diff(
                        $products_to_update->getColumnValues('codproduct'),
                        $rs_process['op_products']
                    );

                    //Deshabilito lo que no me devuelva el WS de plex y ya tengo en la base
                    $new_op_product_disabled = $this->disabledProductFromPlex($products_to_disabled);

                    $total_products_disabled = array_merge($total_products_disabled, $products_to_disabled);

                    $total_products_disabled_new = array_merge($total_products_disabled_new, $new_op_product_disabled);

                    $total_products_disabled_for_price = array_merge(
                        $total_products_disabled_for_price,
                        $rs_process['product_disabled_for_price']
                    );
                    $total_products_updated = array_merge(
                        $total_products_updated,
                        $rs_process['product_updated_and_enabled_codes']
                    );
                    $total_products_requested = array_merge(
                        $total_products_requested,
                        $products_to_update->getColumnValues('codproduct')
                    );

                    $this->logger->debug(" || Jotadevs Update Product || Qty productos consultados: " .
                        count($products_to_update->getColumnValues('codproduct')));
                    $this->logger->debug(" || Jotadevs Update Product || Qty Productos a deshabilitar : " .
                        count($products_to_disabled));
                    $this->logger->debug(" || Jotadevs Update Product || Qty productos nuevos dehabilitado: " .
                        count($new_op_product_disabled));

                    $this->logger->debug(" || Jotadevs Update Product || Qty Productos a deshabilitar x pcio : " .
                        count($rs_process['product_disabled_for_price']));
                    $this->logger->debug(" || Jotadevs Update Product || Qty Productos a actualizar : " .
                        count($rs_process['product_updated_and_enabled']));

                    //Actualizo los productos validados para actualizar en Magento
                    try {
                        $products_mag_disabled = $this->updateMagentoProduct(
                            [
                                'products' => $products_to_disabled,
                                'type' => 'products_disabled_from_plex'
                            ]
                        );
                        $products_mag_updated = $this->updateMagentoProduct(
                            [
                                'products' => $rs_process['product_updated_and_enabled'],
                                'type' => 'products_to_update'
                            ]
                        );
                        $products_mag_disabled_for_price_variation = $this->updateMagentoProduct(
                            [
                                'products' => $rs_process['product_disabled_for_price'],
                                'type' => 'products_to_disable_for_price'
                            ]
                        );
                    } catch (CouldNotSaveException $e) {
                    } catch (InputException $e) {
                    } catch (StateException $e) {
                    } catch (\Exception $e) {
                    }
                } else {
                    $this->logger->info(" || Jotadevs Update Product || " . $result['message']);
                    $messages = array_merge($messages, [
                        'page ' . $i => [
                            'state' => 'error',
                            'message' => $result['message']
                        ]
                    ]);
                }
            }
            $total_time_message = "Tiempo total de ejecución : " .
                date_diff($total_time, $this->timezone->date())->format("%i:%s");
            $this->logger->info($total_time_message);
            return [
                'products_processed' => count($products_to_update->getAllIds()),
                'products_updated' => count($total_products_updated),
                'products_disabled' => count($total_products_disabled),
                'total_products_disabled_new' => count($total_products_disabled_new),
                'products_disabled_for_price' => count($total_products_disabled_for_price),
                'products_requested' => count($total_products_requested),
                'error_messages' => $messages,
                'total_time' => $total_time_message
            ];
        } else {
            $total_time_message = " || Jotadevs Update Stock Product || Tiempo total de ejecución : "
                . date_diff($total_time, $this->timezone->date())->format("%i:%s");
            $error_msg = " No se pudo conectar con el WS de Plex. Error: " .
            $ws_plex_status['message'] . " Mensaje: " . $ws_plex_status['message'];
            $this->logger->debug($error_msg);
            $this->logger->info($total_time_message);
            throw new LocalizedException(__('%1', $error_msg));
        }
    }

    /**
     * @param array $productos
     * @param bool $evaluate_price_option
     * @return array[]
     * @throws \Exception
     */
    public function processUpdateProductsFromPlex(array $productos, bool $evaluate_price_option = false)
    {
        $op_products = [];
        $op_products_codes_enabled = [];
        $op_products_enabled = [];
        $op_products_codes_price_disabled = [];
        //recorro el array de productos para actualizar los datos
        foreach ($productos as $op_api_product) {
            $op_product = $this->plexproduct->create()->load($op_api_product['codproducto'], 'codproduct');
            array_push($op_products, $op_product->getSku());
            foreach ($op_api_product as $key => $value) {
                ($key == 'rubro') ? $op_product->setRubro($value) : null;
                ($key == 'subrubro') ? $op_product->setSubrubro($value) : null;
                ($key == 'idrubro') ? $op_product->setIdrubro($value) : null;
                ($key == 'idSubro') ? $op_product->setIdrubro($value) : null;
                ($key == 'idlaboratorio') ? $op_product->setIdLaboratorio($value) : null;
                if ($key == 'grupos') {
                    foreach ($value as $gr) {
                        foreach ($gr as $key_gr => $value_gr) {
                            ($key_gr == 'idgrupo') ? $op_product->setIdgrupo($value_gr) : null;
                            ($key_gr == 'grupo') ? $op_product->setGrupo($value_gr) : null;
                        }
                    }
                }
                //Anilisis de Variacion de Precio.
                if ($key == 'precio') {
                    if ($evaluate_price_option) {
                        $rs = $this->evaluatePriceVariation(
                            floatval(str_replace(',', '.', str_replace('.', '', $value))),
                            $op_product->getPrecio()
                        );
                        if ($rs['price_status'] === 'price_not_valid') {
                            array_push($op_products_codes_price_disabled, $op_product->getSku());
                            $op_product->setIsOpEnabled(false);
                            $op_product->setObservations(
                                "Producto deshabilitado luego del analisis de precio " . $rs['message']
                            );
                        } else {
                            array_push($op_products_codes_enabled, $op_product->getSku());
                            $op_products_enabled [] = $op_product;
                            $op_product->setIsOpEnabled(true);
                            $op_product->setPrecio($value);
                            $op_product->setObservations(
                                "Producto habilitado y actualizado el " . date('Y-m-d H:i:s')
                            );
                        }
                    } else {
                        array_push($op_products_codes_enabled, $op_product->getSku());
                        $op_products_enabled [] = $op_product;
                        $op_product->setIsOpEnabled(true);
                        $op_product->setPrecio($value);
                        $op_product->setObservations(
                            "Producto habilitado y actualizado el " . date('Y-m-d H:i:s')
                        );
                    }
                }
            }
            $op_product->save();
        }

        return [
            'op_products' => $op_products,
            'product_updated_and_enabled_codes' => $op_products_codes_enabled,
            'product_updated_and_enabled' => $op_products_enabled,
            'product_disabled_for_price' => $op_products_codes_price_disabled,
            ];
    }

    public function disabledProductFromPlex(array $products_codes)
    {
        $new_op_products_disabled = [];
        foreach ($products_codes as $code) {
            //cambio estado deshabilitado en Modelo Plex
            $op_product_to_disabled = $this->plexproduct->create()
                    ->load($code, 'codproduct');
            if ($op_product_to_disabled->getIsOpEnabled()) {
                array_push($new_op_products_disabled, $code);
                $op_product_to_disabled
                        ->setIsOpEnabled(false)
                        ->setObservations(" Producto desactivado desde Plex el " . date('Y-m-d H:i:s'))
                        ->save();
                $this->logger->debug(" Deshabilitando Producto en Middleware OP " . $code);
            } else {
                $this->logger->debug(" Producto ya deshabilitado en Middleware OP " . $code);
            }
        }
        return $new_op_products_disabled;
    }

    /**
     * @param $precio
     * @param $precio_actual
     * @return string[]
     */
    public function evaluatePriceVariation($precio, $precio_actual)
    {
        if ($precio > $precio_actual) {
            $this->logger->debug(" El precio nuevo es mayor ");
            $variation = ((($precio - $precio_actual) / $precio_actual) * 100);
            if ($variation > 15) {
                $msg = "La variacion es mayor al 15%: " . $variation . " Precio nuevo: "
                    . $precio . " Precio actual: " . $precio_actual;
                $this->logger->debug($msg);
                $status = 'price_not_valid';
            } else {
                $msg = " La variacion es menor al 15% " . $variation . " Precio nuevo: "
                    . $precio . " Precio actual: " . $precio_actual;
                $this->logger->debug($msg);
                $status = 'price_valid';
            }
        } elseif ($precio < $precio_actual) {
            $msg = " Precio rebajado deshabilitar producto, rebajado en: " .
                ($precio_actual - $precio) . " Precio nuevo: "
                . $precio . " Precio actual: " . $precio_actual;
            $this->logger->debug($msg);
            $status = 'price_not_valid';
        } else {
            $msg = " Sin Variacion de precio ";
            $this->logger->debug($msg);
            $status = 'price_valid';
        }
        return [
            'message' => $msg,
            'price_status' => $status
        ];
    }

    /**
     * @param array $op_products
     * @return int|void
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Exception
     */
    public function updateMagentoProduct(array $op_products)
    {
        if ($op_products['type'] == 'products_to_update') {
            foreach ($op_products['products'] as $op_product) {
                $mag_product = $this->productRepository->get($op_product->getSku());
                $mag_product->getSku() == '3003947764' ?
                    $this->logger->debug("Actualizando el producto 3003947764") : null;
                $plex_laboratorio = $this->plexlaboratorio->create()
                    ->load($op_product->getIdLaboratorio(), 'id_plex');
                $mag_product
                    ->setPrice($op_product->getPrecio())
                    ->setStatus(Product\Attribute\Source\Status::STATUS_ENABLED)
                    ->setLaboratorio($plex_laboratorio->getName())
                    ->setRubroPlex($op_product->getRubro())
                    ->setSubrubroPlex($op_product->getSubrubro())
                    ->setGrupoPlex($op_product->getGrupo())
                    ->setObservacionesPlex("Producto actualizado el: " . date('Y-m-d H:i:s'));
                $this->productResource
                    ->saveAttribute($mag_product, 'price')
                    ->saveAttribute($mag_product, 'laboratorio')
                    ->saveAttribute($mag_product, 'rubro_plex')
                    ->saveAttribute($mag_product, 'subrubro_plex')
                    ->saveAttribute($mag_product, 'grupo_plex')
                    ->saveAttribute($mag_product, 'status')
                    ->saveAttribute($mag_product, 'observaciones_plex');

                //$this->productRepository->save($mag_product);
            }
        } else {
            foreach ($op_products['products'] as $code) {
                $mag_product = $this->productRepository->get($code);
                $mag_product->setStatus(Product\Attribute\Source\Status::STATUS_DISABLED);
                $op_products['type'] == 'products_disabled_from_plex' ?
                    $mag_product->setCustomAttribute(
                        'observaciones_plex',
                        "Producto Deshabilitado desde Plex"
                    ) :
                    $mag_product->setCustomAttribute(
                        'observaciones_plex',
                        "Producto Deshabilitado por Variacion de Precio"
                    );
                $this->productResource
                    ->saveAttribute($mag_product, 'status')
                    ->saveAttribute($mag_product, 'observaciones_plex');
            }
        }
        return count($op_products);
    }

    public function getAllOpProducts()
    {
        $products_to_update = $this->plexproduct->create()->getCollection()
           // ->addFieldToFilter('is_synchronized', ['eq' => true])
        ;
        return [
            //'query' => $products_to_update->getSelectSql(),
            'cantidad' => count($products_to_update->getAllIds())

        ];
    }
}
