<?php

namespace Jotadevs\BotonArrepentimiento\Controller\Adminhtml\Caso;

use Jotadevs\BotonArrepentimiento\Model\CasoFactory;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;

class Save extends Action
{
    /**
     * @var CasoFactory
     */
    private $casoFactory;
    public function __construct(
        Context $context,
        CasoFactory $casoFactory
    )
    {
        $this->casoFactory = $casoFactory;
        parent::__construct($context);
    }
    public function execute()
    {
        //TODO aca tengo que buscar el id del caso y darle un load luego setearle solo el campo status y observaciones
        $this->casoFactory->create()
            ->setData($this->getRequest()->getPostValue()['general'])
            ->save();
        return $this->resultRedirectFactory->create()->setPath('gestion/caso/index');
    }
}
