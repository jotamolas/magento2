<?php

namespace Jotadevs\BotonArrepentimiento\Controller\Adminhtml\Caso;

use Jotadevs\BotonArrepentimiento\Controller\Adminhtml\Caso;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\NoSuchEntityException;

class Edit extends Caso
{
    public function execute()
    {
        $casoId = $this->getRequest()->getParam('id');
        if ((bool)$casoId) {
            try {
                $caso = $this->caso->create()->load($casoId, 'id');
                $people_name = $caso->getNombre() . " " . $caso->getApellido();
            } catch (NoSuchEntityException $e) {
                $this->messageManager->addException($e, __('Algo salio mal mientras editabamos este Caso'));
                $resultRedirect = $this->resultRedirectFactory->create();
                $resultRedirect->setPath('caso/*/index');
                return $resultRedirect;
            }
        }
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->getConfig()->getTitle()->prepend(__("Editando Caso: " . $people_name));
        return $resultPage;
    }
}
