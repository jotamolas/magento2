<?php


namespace Jotadevs\RedCustoms\Model;


use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;

class RedConfigProvider implements ConfigProviderInterface
{

    protected $_storeManager;

    public function __construct(StoreManagerInterface $storeManager)
    {
        $this->_storeManager = $storeManager;
    }

    /**
     * @inheritDoc
     */
    public function getConfig()
    {
        $mediaUrl = $this->_storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);
        $config = [];
        $config ['mediaUrl'] = $mediaUrl;
        return $config;
    }
}
