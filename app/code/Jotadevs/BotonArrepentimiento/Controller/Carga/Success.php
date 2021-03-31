<?php

namespace Jotadevs\BotonArrepentimiento\Controller\Carga;

use Jotadevs\BotonArrepentimiento\Controller\Boton;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\View\Element\Template;

class Success extends Boton
{
    public function execute()
    {
        $page = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $params = $this->getRequest()->getParams();
        if (empty($params)) {
            return  $this->resultRedirectFactory->create()->setRefererUrl();
        }

        /** @var Template $block */
        $block = $page->getLayout()->getBlock('arrepentimiento.carga.success');
        $block->setData('id_caso', $params['id']);
        return $page;
    }
}
