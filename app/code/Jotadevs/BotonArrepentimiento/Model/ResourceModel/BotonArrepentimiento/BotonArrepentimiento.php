<?php


namespace Jotadevs\BotonArrepentimiento\Model\ResourceModel\BotonArrepentimiento;


use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class BotonArrepentimiento extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init('Jotadevs\BotonArrepentimiento\Model\BotonArrepentimiento',
            'Jotadevs\BotonArrepentimiento\Model\ResourceModel\BotonArrepentimiento');
    }

}
