<?php
namespace Jotadevs\OnzePlexConnector\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class PlexProduct extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('jotadevs_op_product', 'id');
    }
}
