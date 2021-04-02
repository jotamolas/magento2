<?php

namespace Jotadevs\BotonArrepentimiento\Controller\Adminhtml;

use Jotadevs\BotonArrepentimiento\Model\CasoFactory;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\ForwardFactory;
use Magento\Framework\Message\ManagerInterface as MessageManagerInterface;
use Magento\Framework\View\Result\PageFactory;

abstract class Caso extends Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;
    protected $resultForwardFactory;
    protected $resultRedirectFactory;

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
        PageFactory $resultPageFactory,
        ForwardFactory $resultForwardFactory,
        CasoFactory $casoFactory,
        MessageManagerInterface $messageManager
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->resultForwardFactory = $resultForwardFactory;
        $this->resultRedirectFactory = $context->getResultRedirectFactory();
        $this->caso = $casoFactory;
        $this->messageManager = $messageManager;
        parent::__construct($context);
    }
}
