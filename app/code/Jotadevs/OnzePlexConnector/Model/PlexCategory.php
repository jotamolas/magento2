<?php

namespace Jotadevs\OnzePlexConnector\Model;

class PlexCategory extends \Magento\Framework\Model\AbstractModel
{
    protected function _construct()
    {
        $this->_init(\Jotadevs\OnzePlexConnector\Model\ResourceModel\PlexCategory::class);
    }
}
