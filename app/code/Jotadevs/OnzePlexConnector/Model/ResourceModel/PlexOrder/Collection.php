<?php


namespace Jotadevs\OnzePlexConnector\Model\ResourceModel\PlexOrder;

use Jotadevs\OnzePlexConnector\Model\PlexOrder;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Jotadevs\OnzePlexConnector\Model\ResourceModel\PlexOrder as PlexOrderResource;
class Collection extends AbstractCollection
{
    protected $_idFieldName = 'id';
    protected function _construct()
    {
        $this->_init(PlexOrder::class, PlexOrderResource::class);
    }
}
