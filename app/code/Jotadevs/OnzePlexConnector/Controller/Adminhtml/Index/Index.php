<?php
namespace Jotadevs\OnzePlexConnector\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;
use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class Index
 */
class Index extends Action
{
    /** @var PageFactory $resultPageFactory  */
    protected $resultPageFactory;

    /** @var Page $resultPage */
    protected $resultPage;

    public function __construct(
        Action\Context $context,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    public function execute()
    {
        $this->resultPage = $this->resultPageFactory->create();
        $this->resultPage->setActiveMenu('Jotadevs_OnzePlexConnector::menu');
        $this->resultPage->getConfig()->getTitle()->set('Onze Plex Connector');
        return $this->resultPage;
        /*
        $result = $this->resultFactory->create(ResultFactory::TYPE_RAW);
        $result->setContents('Hello Loquito');
        return $result;
        */
    }
}
