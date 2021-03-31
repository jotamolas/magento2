<?php

namespace Jotadevs\BotonArrepentimiento\Block\Carga;

use Jotadevs\BotonArrepentimiento\Model\CasoFactory;
use Magento\Backend\Block\Template;

class Success extends Template
{
    /**
     * @var CasoFactory
     */
    protected $casoFactory;
    public function __construct(
        CasoFactory $casoFactory,
        Template\Context $context,
        array $data = []
    ) {
        $this->casoFactory = $casoFactory;
        parent::__construct($context, $data);
    }

    public function getCasoData()
    {
        $caso_id = $this->getData('id_caso');
        $caso = $this->casoFactory->create()->load($caso_id);
        if ($caso->isEmpty()) {
            return [
                'status' => 'fail'
            ];
        } else {
            return [
                'status' => 'success',
                'id' => $caso->getId(),
                'fecha' => $caso->getFecha(),
                'id_compra' => $caso->getIdentificadorCompra(),
                'cliente' => $caso->getApellido() . " " . $caso->getNombre()
            ];
        }
    }
}
