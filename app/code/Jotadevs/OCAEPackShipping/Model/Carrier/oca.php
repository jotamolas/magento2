<?php
namespace Jotadevs\OCAEPackShipping\Model\Carrier;

use Jotadevs\OCAEPackShipping\Model\Query\OcaApi;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Shipping\Model\Carrier\AbstractCarrier;
use Magento\Shipping\Model\Carrier\CarrierInterface;

class oca extends AbstractCarrier implements
    CarrierInterface
{
    protected $_code = 'oca';
    protected $_isFixed = true;
    protected $_rateResultFactory;
    protected $_rateMethodFactory;
    protected $ocaApi;
    protected $provincias_code = ['CABA', 'BA'];
    protected $ciudades = ['posadas', 'cordoba', 'córdoba', 'cba', 'capital', 'cordobacapital', 'córdobacapital'];
    protected $invalid_postalcode = [5151, 5149, 5105, 5109, 5111];

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory,
        \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory,
        OcaApi $ocaApi,
        array $data = []
    ) {
        $this->_rateResultFactory = $rateResultFactory;
        $this->_rateMethodFactory = $rateMethodFactory;
        $this->ocaApi = $ocaApi;
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
        $dest_post_code = $request->getDestPostcode();
        $dest_region_code = $request->getDestRegionCode();
        $dest_city = strtolower(preg_replace('/\s+/', '', $request->getDestCity()));

        if (in_array($dest_region_code, $this->provincias_code)
            or in_array($dest_city, $this->ciudades)
            or in_array($dest_post_code, $this->invalid_postalcode)) {
            return false;
        }

        if ($request->getAllItems()) {
            $cantidad = 0;
            foreach ($request->getAllItems() as $item) {
                /* @var $item \Magento\Quote\Model\Quote\Item */
                $cantidad = $cantidad + (int) $item->getQty();
            }
            $paquetes = (int) $cantidad  / (int) $this->getConfigData('oca_q_art_estandar');

            $response = $this->ocaApi->tarifarEnvio(
                $dest_post_code,
                $this->getConfigData('oca_operativa'),
                $this->getConfigData('oca_cuit'),
                $this->getConfigData('oca_cpostal'),
                $this->getConfigData('oca_peso'),
                $this->getConfigData('oca_volumen_estandar'),
                $paquetes
            );

            if ($response['state'] == 'ok') {
                $sucursal = $this->ocaApi->GetCentrosImposicionPorCP($dest_post_code);
                $shippingPrice = $response['Precio'] * 1.21;
                if ($this->getConfigData('oca_recargo')) {
                    $shippingPrice = $shippingPrice * (1 + ($this->getConfigData('oca_recargo') / 100));
                }
                $result = $this->_rateResultFactory->create();
                $method = $this->_rateMethodFactory->create();
                $method->setCarrier('oca');
                $method->setCarrierTitle(
                    $this->getConfigData('title')
                    . ' ' . $sucursal['Calle']
                    . ' ' . $sucursal['Numero']
                    . ' ' . $sucursal['Descripcion']
                    . ' Telefono: ' . $sucursal['Telefono']
                );
                $method->setMethod('oca');
                $method->setMethodTitle($this->getConfigData('name'));
                $method->setPrice($shippingPrice);
                $method->setCost($shippingPrice);
                $result->append($method);
                return $result;
            } else {
                return false;
            }
        }
        return false;
    }
}
