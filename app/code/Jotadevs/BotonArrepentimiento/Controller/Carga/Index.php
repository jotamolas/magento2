<?php
namespace Jotadevs\BotonArrepentimiento\Controller\Carga;

use Jotadevs\BotonArrepentimiento\Controller\Boton;
use Magento\Framework\Controller\ResultFactory;

class Index extends Boton
{
    public function execute()
    {
        $result = $this->resultFactory->create(
            ResultFactory::TYPE_PAGE
        );
        return $result;
    }
}
