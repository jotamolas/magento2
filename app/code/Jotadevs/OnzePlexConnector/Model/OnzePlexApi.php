<?php

namespace Jotadevs\OnzePlexConnector\Model;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\Data\ProductInterfaceFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Framework\HTTP\ZendClient;
use Magento\Framework\Serialize\Serializer\Json;

class OnzePlexApi
{
    protected $zendClient;
    protected $user = 'ecommerce';
    protected $password = 'e2020commerce';
    protected $json;
    protected $plexproduct;
    protected $plexoperation;

    private $productFactory;
    private $productRepository;
    private $stockRegistry;
    private $state;

    public function __construct(
        PlexOperationFactory $plexoperation,
        PlexProductFactory $plexproduct,
        ZendClient $zendClient,
        Json $json,
        ProductInterfaceFactory $productFactory,
        ProductRepositoryInterface $productRepository,
        StockRegistryInterface $stockRegistry,
        \Magento\Framework\App\State $state
    ) {
        $this->zendClient = $zendClient;
        $this->json = $json;
        $this->plexproduct = $plexproduct;
        $this->plexoperation = $plexoperation;
        $this->productFactory = $productFactory;
        $this->productRepository = $productRepository;
        $this->stockRegistry = $stockRegistry;
        $this->state = $state;
    }
    /*
     * este metodo obtiene los productos desde la API Onze Plex
     * se le puede consultar por fecha de cambio o ids de productos
     * */
    public function getProductsOnexPlex(\DateTime $fechadecambio = null, array $ids = null)
    {
        $parameters = [];
        if ($fechadecambio) {
            $parameters = array_merge($parameters, ['fechacambio' => $fechadecambio->format('Ymd')]);
        }
        if ($ids) {
            $idstring = null;
            foreach ($ids as $key => $value) {
                if (array_key_last($ids) == $key) {
                    $idstring .= $value;
                } else {
                    $idstring .= $value . ',';
                }
            }
            $parameters = array_merge($parameters, ['idproducto' => $idstring]);
        }
        $this->zendClient->resetParameters();
        //var_dump($parameters);
        try {
            $this->zendClient->setUri("http://170.0.92.97/onzews/ec_getproductos");
            $this->zendClient->setMethod(ZendClient::GET);
            $this->zendClient->setAuth($this->user, $this->password);
            $this->zendClient->setParameterGet($parameters);
            $this->zendClient->setHeaders(
                [
                    'Content-Type' => 'application/json',
                    'x-rapidapi-host' => '170.0.92.97'
                ]
            );
            $response = $this->zendClient->request();
            $response_array = $this->json->unserialize($response->getBody());
            //var_dump($response);
            //var_dump($response_array['response']['content']['productos']);
            //var_dump($response_array['response']['content']['totregistros']);
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
        //var_dump($parameters);
        try {
            $this->zendClient->setUri("http://170.0.92.97/onzews/ec_getrubros");
            $this->zendClient->setMethod(ZendClient::GET);
            $this->zendClient->setAuth($this->user, $this->password);
            $this->zendClient->setHeaders(
                [
                    'Content-Type' => 'application/json',
                    'x-rapidapi-host' => '170.0.92.97'
                ]
            );
            $response = $this->zendClient->request();
            $response_array = $this->json->unserialize($response->getBody());
            //var_dump($response);
            //var_dump($response_array['response']['content']['productos']);
            //var_dump($response_array['response']['content']['totregistros']);
            return [
                'state' => 'success',
                'result' => $response_array
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
        //var_dump($parameters);
        try {
            $this->zendClient->setUri("http://170.0.92.97/onzews/ec_getsubrubros");
            $this->zendClient->setMethod(ZendClient::GET);
            $this->zendClient->setAuth($this->user, $this->password);
            $this->zendClient->setHeaders(
                [
                    'Content-Type' => 'application/json',
                    'x-rapidapi-host' => '170.0.92.97'
                ]
            );
            $response = $this->zendClient->request();
            $response_array = $this->json->unserialize($response->getBody());
            //var_dump($response);
            //var_dump($response_array['response']['content']['productos']);
            //var_dump($response_array['response']['content']['totregistros']);
            return [
                'state' => 'success',
                'result' => $response_array
            ];
        } catch (\Zend_Http_Client_Exception $e) {
            return [
                'state' => 'error',
                'code' => $e->getCode(),
                'message' => $e->getMessage()
            ];
        }
    }
    public function importFromPlex()
    {
        //llamamos a la RestApi del Erp y traemos TODOS los productos.
        $result = $this->getProductsOnexPlex();
        $operation = $this->plexoperation->create()
            ->setName("Obtener Productos desde OnzePlex")
            ->setCode("GPOP");
        //si hay resultados proseguimos
        if ($result['state'] == 'success') {
            if (!empty($result['result'])) {
                $op_products = [];
                //recorro el array de productos para analizar si ya lo tengo en base
                foreach ($result['result'] as $op_api_product) {
                    //verifico si no existe ya en la tabla de op
                    $op_product = $this->plexproduct->create()->load($op_api_product['codproducto'], 'codproduct');
                    if (empty($op_product->toArray())) {
                        //si no existe lo cargo de vuelta
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
                        }
                        $op_product->setIsObjectNew(true);
                        $op_product->save();
                        $op_products[] = $op_product;
                    }
                }
                $operation
                    ->setMessage(
                        "Estado de importacion: Success, Productos recibidos:" .
                        count($result['result']) . " Nuevos:" . count($op_products)
                    )->setLastId()
                     ->setIsObjetNew(true)
                     ->save();
                return [
                    'state' => 'success',
                    'received' => count($result['result']),
                    'new' => count($op_products)
                ];
            } else {
                $operation->setMessage("Estado de importacion: Success, Productos recibidos: 0 Nuevos: 0");
                $operation->setIsObjetNew(true);
                $operation->save();
                return[
                    'state' => 'success',
                    'received' => 0,
                    'new' => 0
                ];
            }
        } else {
            $operation->setMessage("Estado de importacion: Error, Mensaje de Error:" . $result['message']);
            $operation->setIsObjetNew(true);
            $operation->save();
            return [
                'state' => 'error',
                'message' => $result['message']
            ];
        }
    }
    public function convertToMagentoProduct()
    {
        //seteo operacion
        $operation = $this->plexoperation->create()->setName("Convertir Productos desde OnzePlex a Magento");
        //obtener ultimo id convertido
        $last_operation_collection = $this->plexoperation->create()->getCollection();
        $last_operation_collection
            ->addFieldToFilter('code', ['eq' => 'CNTM']);
        if (empty($last_operation_collection->getColumnValues('last_id'))) {
            $last_id = 0;
        } else {
            $last_id = max($last_operation_collection->getColumnValues('last_id'));
        }
        //var_dump($last_id);
        //Busco todos los productos obtenidos en OnexPlex pero filtrando desde el ultimo que se importó
        $new_op_products_collection = $this->plexproduct->create()->getCollection();
        $new_op_products_collection
            ->addFieldToFilter('id', ['gt' => $last_id])
            ->addFieldToFilter('rubro', ['eq' => 'Accesorios'])
            ->setPageSize(20)
            ->setCurPage(1)
            ->load();
        $new_last_id = max($new_op_products_collection->getColumnValues('id'));
        //seteo area de ejecuccion como global front y backend
        $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_GLOBAL);
        /** los convierto a productos magento
         *  recorro los nuevos productos obtenidos y por cada uno los inserto
        */
        foreach ($new_op_products_collection as $new_op_product) {
            /** @var ProductInterface $mag_product */
            $mag_product = $this->productFactory->create();
            $mag_product->setSku($new_op_product->getSku())
                ->setName($new_op_product->getProducto())
                ->setTypeId(\Magento\Catalog\Model\Product\Type::TYPE_SIMPLE)
                ->setVisibility(4)
                ->setAttributeSetId(4)
                ->setPrice($new_op_product->getPrecio())
                ->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED);
            $mag_product = $this->productRepository->save($mag_product);
            $stockItem = $this->stockRegistry->getStockItemBySku($mag_product->getSku());
            $stockItem->setIsInStock(true)->setQty($new_op_product->getStock());
        }
        $operation
            ->setMessage("Estado de importacion: Success, Cantidad Importada:" . count($new_op_products_collection))
            ->setCode('CNTM')
            ->setLastId($new_last_id);
        $operation->setIsObjetNew(true);
        $operation->save();
        return [
            'state' => 'success',
            'qty' => count($new_op_products_collection)
        ];
    }
    public function getCovidStatForAtgentina()
    {
        $this->zendClient->resetParameters();
        try {
            $this->zendClient->setUri("https://covid-19-data.p.rapidapi.com/report/country/name");
            $this->zendClient->setMethod(ZendClient::GET);
            $this->zendClient->setHeaders(
                [
                    'Content-Type' => 'application/json',
                    'x-rapidapi-host' => 'covid-19-data.p.rapidapi.com',
                    'x-rapidapi-key' => 'f9faf79bcemsha19fe12b7350825p11437ajsn7ab61dabbd6e'
                ]
            );
            $this->zendClient->setParameterGet([
                'date-format' => 'YYYY-MM-DD',
                'date' => '2020-06-13',
                'name' => 'Argentina'
            ]);
            $response = $this->zendClient->request();
            return $response;
        } catch (\Zend_Http_Client_Exception $e) {
            return "no se pudo conectar";
        }
    }
}
