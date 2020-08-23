<?php

namespace Jotadevs\OnzePlexConnector\Model;

use Magento\Framework\Model\AbstractModel;

class PlexProduct extends AbstractModel
{
    protected function _construct()
    {
        $this->_init(\Jotadevs\OnzePlexConnector\Model\ResourceModel\PlexProduct::class);
    }
}
