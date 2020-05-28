<?php
namespace Jotadevs\OnzePlexConnector\Model\ResourceModel\PlexProduct;

use Jotadevs\OnzePlexConnector\Model\PlexProduct;
use Jotadevs\OnzePlexConnector\Model\ResourceModel\PlexProduct as PlexProductResource;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected $_idFieldName = 'id';
    protected function _construct()
    {
        $this->_init(PlexProduct::class, PlexProductResource::class);
    }
}
