<?php
namespace Jotadevs\OnzePlexConnector\Model\ResourceModel\PlexOperation;

use Jotadevs\OnzePlexConnector\Model\PlexLaboratorio;
use Jotadevs\OnzePlexConnector\Model\ResourceModel\PlexLaboratorio as PlexLaboratorioResource;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected $_idFieldName = 'id';
    protected function _construct()
    {
        $this->_init(PlexLaboratorio::class, PlexLaboratorioResource::class);
    }
}
