<?php

namespace Jotadevs\OnzePlexConnector\Model;

class PlexProduct extends \Magento\Framework\Model\AbstractModel
{
    protected function _construct()
    {
        $this->_init(\Jotadevs\OnzePlexConnector\Model\ResourceModel\PlexProduct::class);
    }
}
