<?php

namespace Jotadevs\BotonArrepentimiento\Controller\Adminhtml\Caso;

use Jotadevs\BotonArrepentimiento\Model\CasoFactory;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Message\ManagerInterface as MessageManagerInterface;

class Edit extends Action
{
    /**
     * @var CasoFactory
     */
    protected $caso;

    /**
     * @var MessageManagerInterface
     */
    protected $messageManager;

    public function __construct(
        Context $context,
        CasoFactory $caso,
        MessageManagerInterface $messageManager
    ) {
        $this->caso = $caso;
        $this->messageManager = $messageManager;
        parent::__construct($context);
    }

    public function execute()
    {
        $casoId = $this->getRequest()->getParam('id');
        if ((bool)$casoId) {
            try {
                $caso = $this->caso->create()->load($casoId, 'id');
                $people_name = $caso->getNombre()." ".$caso->getApellido();
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
