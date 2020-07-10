<?php

namespace Jotadevs\OnzePlexConnector\Model\ResourceModel;

class PlexCategory extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init('jotadevs_op_rubros', 'id');
    }
}
