<?php

namespace Jotadevs\RedCarrier\Model\Carrier;

use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Shipping\Model\Carrier\AbstractCarrier;
use Magento\Shipping\Model\Carrier\CarrierInterface;

class red extends AbstractCarrier implements
    CarrierInterface
{
    protected $_code = 'red';
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

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory,
        \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory,
        array $data = []
    ) {
        $this->_rateResultFactory = $rateResultFactory;
        $this->_rateMethodFactory = $rateMethodFactory;
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
        //After filtering, start forming final price
        //$shippingPrice = $this->getConfigData('price');
        //Solo para Cordoba Capital o Posadas o Resistencia
        $dest_city = strtolower(preg_replace('/\s+/', '', $request->getDestCity()));
        if (!in_array($dest_city, $this->ciudades)) {
            return false;
        }
        $shippingPrice = $this->getConfigData('price');
        $result = $this->_rateResultFactory->create();
        $method = $this->_rateMethodFactory->create();
        $method->setCarrier('red');
        $method->setCarrierTitle($this->getConfigData('title'). " a " . $request->getDestCity());
        $method->setMethod('red');
        $method->setMethodTitle($this->getConfigData('name'));
        $method->setPrice($shippingPrice);
        $method->setCost($shippingPrice);
        $result->append($method);

        return $result;
    }
}
