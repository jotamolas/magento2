<?php

namespace Jotadevs\OnzePlexConnector\Model\ResourceModel\PlexCategory;

use Jotadevs\OnzePlexConnector\Model\PlexCategory;
use Jotadevs\OnzePlexConnector\Model\ResourceModel\PlexCategory as PlexCategoryResource;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected $_idFieldName = 'id';
    protected function _construct()
    {
        $this->_init(PlexCategory::class, PlexCategoryResource::class);
    }
}
