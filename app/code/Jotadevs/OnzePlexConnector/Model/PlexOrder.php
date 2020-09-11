<?php

namespace Jotadevs\OnzePlexConnector\Model;

use Magento\Framework\Model\AbstractModel;

class PlexOrder extends AbstractModel
{
    protected function _construct()
    {
        $this->_init(\Jotadevs\OnzePlexConnector\Model\ResourceModel\PlexOrder::class);
    }

}
