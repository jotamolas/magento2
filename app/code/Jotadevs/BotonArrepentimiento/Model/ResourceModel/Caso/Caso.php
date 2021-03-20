<?php

namespace Jotadevs\BotonArrepentimiento\Model\ResourceModel\Caso;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Caso extends AbstractCollection
{
    protected $_idFieldName = 'id';
    protected function _construct()
    {
        $this->_init(
            'Jotadevs\BotonArrepentimiento\Model\Caso',
            'Jotadevs\BotonArrepentimiento\Model\ResourceModel\Caso'
        );
    }
}
