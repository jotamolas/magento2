<?php


namespace Jotadevs\BotonArrepentimiento\Model\ResourceModel;


use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Caso extends AbstractDb
{
    protected function _construct()
    {
         $this->_init('jotadevs_arrepentimiento_caso','id');
    }

}
