<?php

namespace Jotadevs\RedCarrier\Model\Carrier;

use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Shipping\Model\Carrier\AbstractCarrier;
use Magento\Shipping\Model\Carrier\CarrierInterface;

class redgcba extends AbstractCarrier implements
    CarrierInterface
{
    protected $_code = 'redgcba';
    protected $_isFixed = true;
    protected $_rateResultFactory;
    protected $_rateMethodFactory;
    protected $zipcode_grancba = [5151,5149,5105,5109,5111];
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
        //Solo para Gran Cordoba... no puedo delimitar el interior
        if (in_array($request->getDestPostcode(), $this->zipcode_grancba)) {
            $shippingPrice = $this->getConfigData('price');
            $result = $this->_rateResultFactory->create();
            $method = $this->_rateMethodFactory->create();
            $method->setCarrier('redgcba');
            $method->setCarrierTitle($this->getConfigData('title'));
            $method->setMethod('redgcba');
            $method->setMethodTitle($this->getConfigData('name'));
            $method->setPrice($shippingPrice);
            $method->setCost($shippingPrice);
            $result->append($method);
            return $result;
        } else {
            return false;
        }
    }
}
