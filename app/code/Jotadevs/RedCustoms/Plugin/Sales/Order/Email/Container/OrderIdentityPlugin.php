<?php

namespace Jotadevs\RedCustoms\Plugin\Sales\Order\Email\Container;

use Magento\Checkout\Model\Session;
use Magento\Sales\Model\Order\Email\Container\OrderIdentity;

class OrderIdentityPlugin
{
    /**
     * @var Session $checkoutSession
     */
    protected $checkoutSession;

    /**
     * @param Session $checkoutSession
     *
     * @codeCoverageIgnore
     */
    public function __construct(
        Session $checkoutSession
    ) {
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * @param OrderIdentity $subject
     * @param callable $proceed
     * @return bool
     */
    public function aroundIsEnabled(OrderIdentity $subject, callable $proceed)
    {
        $returnValue = $proceed();

        $forceOrderMailSentOnSuccess = $this->checkoutSession->getForceOrderMailSentOnSuccess();
        if (isset($forceOrderMailSentOnSuccess) && $forceOrderMailSentOnSuccess) {
            if ($returnValue) {
                $returnValue = false;
            } else {
                $returnValue = true;
            }

            $this->checkoutSession->unsForceOrderMailSentOnSuccess();
        }

        return $returnValue;
    }
}
