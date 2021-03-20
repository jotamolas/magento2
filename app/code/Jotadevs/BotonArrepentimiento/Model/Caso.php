<?php


namespace Jotadevs\BotonArrepentimiento\Model;

use Magento\Framework\Model\AbstractModel;

class Caso extends AbstractModel
{
    protected function _construct()
    {
        $this->_init(\Jotadevs\BotonArrepentimiento\Model\ResourceModel\Caso::class);
    }

}
