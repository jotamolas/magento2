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
    protected $ciudades = ['posadas',
        'cordoba',
        'córdoba',
        'cba',
        'capital',
        'cordobacapital',
        'córdobacapital',
        'resistencia'];
    protected $plexApi;

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
        $method = $this->_rateMethodFactory->create();
        $stores = $this->plexApi->getSucursalesPlex();
        if ($stores['state'] != 'error') {
            foreach ($stores['result'] as $sucursal) {
                $store_city = strtolower(preg_replace('/\s+/', '', $sucursal['localidad']));
                if ($store_city === $dest_city) {
                    $method->setCarrier('storepickup');
                    $method->setCarrierTitle('Retiro en Sucursal ' . $sucursal['nombre'] . ' : ' . $sucursal['domicilio'] . ' Telefono: ' . $sucursal['telefono']);
                    $method->setMethod('storepickup');
                    $method->setMethodTitle('Retiro en Sucursal ' . $sucursal['nombre'] . ' : ' . $sucursal['domicilio'] . ' Telefono: ' . $sucursal['telefono']);
                    $method->setPrice($shippingPrice);
                    $method->setCost($shippingPrice);
                    $result->append($method);
                }
            }
        } else {
            return false;
        }
        return $result;
    }
}
