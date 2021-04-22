<?php

namespace Jotadevs\RedCustoms\Observer;

use Magento\Checkout\Model\Session;
use Magento\Framework\Event\Observer;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;
use Magento\Sales\Model\OrderFactory;

class SendMailOnOrderSuccess implements \Magento\Framework\Event\ObserverInterface
{

    /**
     * @var OrderFactory $orderModel
     */
    protected $orderModel;

    /**
     * @var OrderSender $orderSender
     */
    protected $orderSender;

    /**
     * @var Session $checkoutSession
     */
    protected $checkoutSession;

    public function __construct(
        OrderFactory $orderModel,
        OrderSender $orderSender,
        Session $checkoutSession
    ) {
        $this->orderModel = $orderModel;
        $this->orderSender = $orderSender;
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $orderIds = $observer->getEvent()->getOrderIds();
        if (count($orderIds)) {
            $this->checkoutSession->setForceOrderMailSentOnSuccess(true);
            $order = $this->orderModel->create()->load($orderIds[0]);
            $this->orderSender->send($order, true);
        }
    }
}
