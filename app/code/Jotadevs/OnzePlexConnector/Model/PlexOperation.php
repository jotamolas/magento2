<?php

namespace Jotadevs\OnzePlexConnector\Model;

class PlexOperation extends \Magento\Framework\Model\AbstractModel
{
    protected function _construct()
    {
        $this->_init(\Jotadevs\OnzePlexConnector\Model\ResourceModel\PlexOperation::class);
    }
}
