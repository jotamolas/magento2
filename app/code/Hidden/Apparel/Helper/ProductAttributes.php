<?php

namespace Hidden\Apparel\Helper;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\NoSuchEntityException;

class ProductAttributes extends AbstractHelper
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    public function __construct(
        Context $context,
        ProductRepositoryInterface $productRepository
    ) {
        $this->productRepository = $productRepository;
        parent::__construct($context);
    }

    public function getProductCodeBar($product_id)
    {
        try {
            $product = $this->productRepository->getById($product_id);
            return $product->getCustomAttribute('barcode');
        } catch (NoSuchEntityException $e) {
            return null;
        }
    }
}
