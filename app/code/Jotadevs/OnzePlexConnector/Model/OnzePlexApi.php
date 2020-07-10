<?php

namespace Jotadevs\OnzePlexConnector\Model;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Api\Data\CategoryInterfaceFactory;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\Data\ProductInterfaceFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Framework\HTTP\ZendClient;
use Magento\Framework\Serialize\Serializer\Json;

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

    private $productFactory;
    private $productRepository;
    private $stockRegistry;

    private $categoryFactory;
    private $categoryRespository;
    private $state;
    private $uriDev = 'http://170.0.92.97/onzews/';
    private $uriProd = 'http://gralpaz.plexonzecenter.com.ar:8081/onzews/';

    public function __construct(
        PlexOperationFactory $plexoperation,
        PlexProductFactory $plexproduct,
        PlexCategoryFactory $plexcategory,
        ZendClient $zendClient,
        Json $json,
        ProductInterfaceFactory $productFactory,
        ProductRepositoryInterface $productRepository,
        CategoryInterfaceFactory $categoryFactory,
        CategoryRepositoryInterface $categoryRepository,
        StockRegistryInterface $stockRegistry,
        \Magento\Framework\App\State $state
    ) {
        $this->zendClient = $zendClient;
        $this->json = $json;
        $this->plexproduct = $plexproduct;
        $this->plexcategory = $plexcategory;
        $this->plexoperation = $plexoperation;
        $this->productFactory = $productFactory;
        $this->productRepository = $productRepository;
        $this->categoryFactory = $categoryFactory;
        $this->categoryRespository = $categoryRepository;
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
            $this->zendClient->setUri($this->uriProd . "ec_getproductos");
            $this->zendClient->setMethod(ZendClient::GET);
            $this->zendClient->setAuth($this->userProd, $this->passwordProd);
            $this->zendClient->setParameterGet($parameters);
            $this->zendClient->setHeaders(
                [
                    'Content-Type' => 'application/json',
                   // 'x-rapidapi-host' => '170.0.92.97'
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
        try {
            $this->zendClient->setUri($this->uriProd . "ec_getrubros");
            $this->zendClient->setMethod(ZendClient::GET);
            $this->zendClient->setAuth($this->userProd, $this->passwordProd);
            $this->zendClient->setHeaders(['Content-Type' => 'application/json']);
            $response = $this->zendClient->request();
            $response_array = $this->json->unserialize($response->getBody());
            //var_dump($response_array);
            //var_dump($response_array['response']['content']['productos']);
            //var_dump($response_array['response']['content']['totregistros']);
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
        //var_dump($parameters);
        try {
            $this->zendClient->setUri($this->uriProd . "ec_getsubrubros");
            $this->zendClient->setMethod(ZendClient::GET);
            $this->zendClient->setAuth($this->userProd, $this->passwordProd);
            $this->zendClient->setHeaders(['Content-Type' => 'application/json']);

            $response = $this->zendClient->request();
            $response_array = $this->json->unserialize($response->getBody());
            //var_dump($response_array);
            //var_dump($response_array['response']['content']['productos']);
            //var_dump($response_array['response']['content']['totregistros']);
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
    public function importRubrosFromPlex()
    {
        //llamamos a la RestApi del Erp y traemos TODOS los productos.
        $rubrosApi = $this->getRubrosOnexPlex();
        $operation = $this->plexoperation->create()
            ->setName("Obtener Rubros desde OnzePlex")
            ->setCode("GROP");
        //si hay resultados de rubros proseguimos
        if ($rubrosApi['state'] == 'success') {
            if (!empty($rubrosApi['result'])) {
                $op_rubros = [];
                //recorro el array de rubros para analizar si ya lo tengo en base
                foreach ($rubrosApi['result'] as $op_api_rubro) {
                    //verifico si no existe ya en la tabla de op
                    $op_category = $this->plexcategory->create()->load($op_api_rubro['idrubro'], 'id_plex');
                    if (empty($op_category->toArray())) {
                        //si no existe lo cargo de vuelta
                        foreach ($op_api_rubro as $key => $value) {
                            ($key == 'idrubro') ? $op_category->setIdPlex($value) : null;
                            ($key == 'rubro') ? $op_category->setName($value) : null;
                        }
                        $op_category->setIsObjectNew(true);
                        $op_category->save();
                        $op_rubros[] = $op_category;
                    }
                }
                $operation
                    ->setMessage(
                        "Estado de importacion: Success, Rubros recibidos:" .
                        count($rubrosApi['result']) . " Nuevos:" . count($op_rubros)
                    )->setLastId()
                    ->setIsObjetNew(true)
                    ->save();
                return [
                    'state' => 'success',
                    'received' => count($rubrosApi['result']),
                    'new' => count($op_rubros),
                    'message' => "Estado de importacion: Success, Rubros recibidos:" .
                        count($rubrosApi['result']) . " Nuevos:" . count($op_rubros)
                ];
            } else {
                $operation
                    ->setMessage("Estado de importacion: Success, Productos rubros y subrubros recibidos: 0 Nuevos: 0")
                    ->setIsObjetNew(true)
                    ->save();
                return[
                    'state' => 'success',
                    'received' => 0,
                    'new' => 0,
                    'message' => "Estado de importacion: Success, Productos rubros: 0 Nuevos: 0"
                ];
            }
        } else {
            $operation->setMessage("Estado de importacion: Error, Mensaje de Error:" . $rubrosApi['message']);
            $operation->setIsObjetNew(true);
            $operation->save();
            return [
                'state' => 'error',
                'received' => 0,
                'new' => 0,
                'message' => $rubrosApi['message']
            ];
        }
    }
    public function importSubRubrosFromPlex()
    {
        //llamamos a la RestApi del Erp y traemos TODOS los productos.
        $subrubrosApi = $this->getSubRubrosOnexPlex();
        $operation = $this->plexoperation->create()
            ->setName("Obtener Subrubros desde OnzePlex")
            ->setCode("GSOP");
        //si hay resultados de rubros proseguimos
        if ($subrubrosApi['state'] == 'success') {
            if (!empty($subrubrosApi['result'])) {
                $op_sub_rubros = [];
                //recorro el array de rubros para analizar si ya lo tengo en base
                foreach ($subrubrosApi['result'] as $op_api_sub_rubro) {
                    //verifico si no existe ya en la tabla de op
                    //VER ESTO
                    $op_category = $this->plexcategory->create()->load($op_api_sub_rubro['idsubrubro'], 'id_plex');
                    if (empty($op_category->toArray())) {
                        //si no existe lo cargo de vuelta
                        foreach ($op_api_sub_rubro as $key => $value) {
                            ($key == 'idsubrubro') ? $op_category->setIdPlex($value) : null;
                            ($key == 'idrubro') ? $op_category->setIdParent($value) : null;
                            ($key == 'subrubro') ? $op_category->setName($value) : null;
                        }
                        $op_category->setIsChild(true);
                        $op_category->setIsObjectNew(true);
                        $op_category->save();
                        $op_sub_rubros[] = $op_category;
                    }
                }
                $operation
                    ->setMessage(
                        "Estado de importacion: Success, Sub Rubros recibidos:" .
                        count($subrubrosApi['result']) . " Nuevos:" . count($op_sub_rubros)
                    )->setLastId()
                    ->setIsObjetNew(true)
                    ->save();
                return [
                    'state' => 'success',
                    'received' => count($subrubrosApi['result']),
                    'new' => count($op_sub_rubros)
                ];
            } else {
                $operation
                    ->setMessage("Estado de importacion: Success, Subrubros recibidos: 0 Nuevos: 0")
                    ->setIsObjetNew(true)
                    ->save();
                return[
                    'state' => 'success',
                    'received' => 0,
                    'new' => 0
                ];
            }
        } else {
            $operation->setMessage("Estado de importacion: Error, Mensaje de Error:" . $subrubrosApi['message']);
            $operation->setIsObjetNew(true);
            $operation->save();
            return [
                'state' => 'error',
                'message' => $subrubrosApi['message']
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
            //->addFieldToFilter('rubro', ['eq' => 'Accesorios'])
            //->setPageSize(20)
            //->setCurPage(1)
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
            ->setMessage("Estado de importacion: Success, Cantidad Convertida:" . count($new_op_products_collection))
            ->setCode('CNTM')
            ->setLastId($new_last_id);
        $operation->setIsObjetNew(true);
        $operation->save();
        return [
            'state' => 'success',
            'qty' => count($new_op_products_collection)
        ];
    }
    public function convertToMagentoCategory()
    {
        //seteo operacion
        $operation = $this->plexoperation->create()->setName("Convertir Categorias desde OnzePlex a Magento");
        //Busco todos los productos obtenidos en OnexPlex pero filtrando por los nos sincronizados
        $new_op_category_collection = $this->plexcategory->create()->getCollection();
        $new_op_category_collection
            ->addFieldToFilter('is_synchronized', ['eq' => false])
            ->load();
        //verifico que haya categorias importados y procedo sino devuelvo mensaje
        if (!empty($new_op_category_collection->getColumnValues('id'))) {
            //Busco como dato el ultimo id para actualizar
            $new_last_id = max($new_op_category_collection->getColumnValues('id'));

            //seteo area de ejecuccion como global front y backend
            $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_GLOBAL);
            /** los convierto a Categorias Magento
             *  recorro los nuevos productos obtenidos y por cada uno los inserto
             */
            foreach ($new_op_category_collection as $new_op_category) {
                /** @var CategoryInterface $mag_product */
                $mag_category = $this->categoryFactory->create();
                $mag_category->setName($new_op_category->getName())
                    ->setIsActive(true);
                if ($new_op_category->getIsChild()) {
                    $parent_plex_category = $this->plexcategory->create();
                    $parent_plex_category->load($new_op_category->getIdParent());
                    $mag_category
                        ->setParentId($parent_plex_category->getIdMagento())
                        ->setLevel(3);
                } else {
                    $mag_category->setLevel(2);
                }
                $mag_category = $this->categoryRespository->save($mag_category);
                $new_op_category->setIsSynchronized(true)
                    ->setIdMagento($mag_category->getId());
                $new_op_category->save();
            }
            $operation
                ->setMessage("Estado de importacion: Success, Cantidad Convertida:" . count($new_op_category_collection))
                ->setCode('CCTM')
                ->setLastId($new_last_id);
            $operation->setIsObjetNew(true);
            $operation->save();
            return [
                'state' => 'success',
                'qty' => count($new_op_category_collection),
                'message' => 'import success'
            ];
        } else {
            return [
                'state' => 'success',
                'qty' => 0,
                'message' => 'nothing for import'
            ];
        }
    }
}
