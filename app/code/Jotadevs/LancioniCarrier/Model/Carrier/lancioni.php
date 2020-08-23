<?php

namespace Jotadevs\LancioniCarrier\Model\Carrier;

use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Shipping\Model\Carrier\AbstractCarrier;
use Magento\Shipping\Model\Carrier\CarrierInterface;

class lancioni extends AbstractCarrier implements
    CarrierInterface
{
    protected $_code = 'lancioni';
    protected $_isFixed = true;
    protected $_rateResultFactory;
    protected $_rateMethodFactory;

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
        //Todo tomar la ciudad de buenos aires o provincia y devolver tarifa
        if ($request->getDestRegionCode() == 'CABA') {
            $shippingPrice = $this->getConfigData('price_caba');
        } elseif ($request->getDestRegionCode() == 'BA') {
            $shippingPrice = $this->getConfigData('price_bsas');
        } else {
            return false;
        }
        $result = $this->_rateResultFactory->create();
        $method = $this->_rateMethodFactory->create();
        $method->setCarrier('lancioni');
        $method->setCarrierTitle($this->getConfigData('title'));
        $method->setMethod('lancioni');
        $method->setMethodTitle($this->getConfigData('name'));
        $method->setPrice($shippingPrice);
        $method->setCost($shippingPrice);
        $result->append($method);

        return $result;
    }
}
