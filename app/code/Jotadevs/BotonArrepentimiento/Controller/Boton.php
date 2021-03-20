<?php

namespace Jotadevs\BotonArrepentimiento\Controller;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;

abstract class Boton extends Action
{
    public function __construct(Context $context)
    {
        parent::__construct($context);
    }

    public function dispatch(RequestInterface $request)
    {
        return parent::dispatch($request);
    }
}
