<?php
namespace Jotadevs\OnzePlexConnector\Model\ResourceModel\PlexOperation;

use Jotadevs\OnzePlexConnector\Model\PlexOperation;
use Jotadevs\OnzePlexConnector\Model\ResourceModel\PlexOperation as PlexOperationResource;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected $_idFieldName = 'id';
    protected function _construct()
    {
        $this->_init(PlexOperation::class, PlexOperationResource::class);
    }
}
