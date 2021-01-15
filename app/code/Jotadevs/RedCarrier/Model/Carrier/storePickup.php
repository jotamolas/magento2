<?php

namespace Jotadevs\RedCarrier\Model\Carrier;

use Jotadevs\OnzePlexConnector\Model\OnzePlexApi;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory;
use Magento\Quote\Model\Quote\Address\RateResult\MethodFactory;
use Magento\Shipping\Model\Carrier\AbstractCarrier;
use Magento\Shipping\Model\Carrier\CarrierInterface;
use Magento\Shipping\Model\Rate\ResultFactory;
use Psr\Log\LoggerInterface;

class storePickup extends AbstractCarrier implements
    CarrierInterface
{
    protected $_code = 'storepickup';
    protected $_isFixed = true;
    protected $_rateResultFactory;
    protected $_rateMethodFactory;
    protected $ciudades = ['posadas','cordoba','córdoba','cba','cordobacapital','córdobacapital', 'resistencia'];
    protected $plexApi;
    protected $stores = [
        [
            'name' => 'Red - Nuevocentro',
            'address' => 'Duarte Quiros 1400',
            'postalcode' => '5000',
            'city' => ['cordoba','córdoba','cba','cordobacapital','córdobacapital']
        ],
        [
            'name' => 'Red - Urca',
            'address' => 'Emilio Lamarca 4136',
            'postalcode' => '5000',
            'city' => ['cordoba','córdoba','cba','cordobacapital','córdobacapital']
        ],
        [
            'name' => 'Red - Urbana',
            'address' => 'Av. Pedro Laplace 5890',
            'postalcode' => '5000',
            'city' => ['cordoba','córdoba','cba','cordobacapital','córdobacapital']
        ],
        [
            'name' => 'Red - Colon',
            'address' => 'Av. Colon 5034. Local 8',
            'postalcode' => '5000',
            'city' => ['cordoba','córdoba','cba','cordobacapital','córdobacapital']
        ],
        [
            'name' => 'Red - Cerro',
            'address' => 'Rafael Nunez 3686.',
            'postalcode' => '5000',
            'city' => ['cordoba','córdoba','cba','cordobacapital','córdobacapital']
        ],
        [
            'name' => 'Red - Villa Belgrano',
            'address' => 'Av. Recta Martinolli 6137',
            'postalcode' => '5000',
            'city' => ['cordoba','córdoba','cba','cordobacapital','córdobacapital']
        ],
        [
            'name' => 'Red - Martinolli',
            'address' => 'Av. Recta Martinolli 8853',
            'postalcode' => '5000',
            'city' => ['cordoba','córdoba','cba','cordobacapital','córdobacapital']
        ],
        [
            'name' => 'Red - Resistencia',
            'address' => 'Santa Fe 124.',
            'postalcode' => '3500',
            'city' => ['resistencia']
        ],
        [
            'name' => 'Red - Posadas',
            'address' => 'San Martin 2275',
            'postalcode' => '3300',
            'city' => ['posadas']
        ],
        [
            'name' => 'Red - Posadas II',
            'address' => 'Felix Azara y Entre Rios',
            'postalcode' => '3300',
            'city' => ['posadas']
        ],

    ];
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ErrorFactory $rateErrorFactory,
        LoggerInterface $logger,
        ResultFactory $rateResultFactory,
        MethodFactory $rateMethodFactory,
        OnzePlexApi $plexApi,
        array $data = []
    ) {
        $this->_rateResultFactory = $rateResultFactory;
        $this->_rateMethodFactory = $rateMethodFactory;
        $this->plexApi = $plexApi;
        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);
    }

    public function getAllowedMethods()
    {
        return [$this->_code => $this->getConfigData('name')];
    }

    public function collectRates(RateRequest $request)
    {
        if (!$this->getConfigFlag('active')) {
            return false;
        }
        $dest_city = strtolower(preg_replace('/\s+/', '', $request->getDestCity()));
        if (!in_array($dest_city, $this->ciudades)) {
            return false;
        }
        $shippingPrice = $this->getConfigData('price');
        $result = $this->_rateResultFactory->create();

        foreach ($this->stores as $sucursal) {
            //$store_city = strtolower(preg_replace('/\s+/', '', $sucursal['city']));
            if (in_array($dest_city, $sucursal['city'])) {
                $method = $this->_rateMethodFactory->create();
                $method->setCarrier('storepickup ');
                $method->setMethod('storepickup');
                $method->setCarrierTitle('Retiro en Sucursal ' . $sucursal['name'] . ' : ' . $sucursal['address'] . ' .');
                $method->setMethodTitle('Retiro en Sucursal ' . $sucursal['name'] . ' : ' . $sucursal['address'] . ' .');
                $method->setPrice($shippingPrice);
                $method->setCost($shippingPrice);
                $result->append($method);
            }
        }
        return $result;
    }
}
