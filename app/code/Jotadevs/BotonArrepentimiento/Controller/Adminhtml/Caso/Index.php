<?php

namespace Jotadevs\BotonArrepentimiento\Controller\Adminhtml\Caso;

use Jotadevs\BotonArrepentimiento\Controller\Adminhtml\Caso;
use Magento\Framework\Controller\ResultFactory;

class Index extends Caso
{
    public function execute()
    {
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->getConfig()->getTitle()->prepend(__("Gestion de Casos"));
        return $resultPage;
    }
}
