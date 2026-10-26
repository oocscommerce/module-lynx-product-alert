<?php
/**
 * Copyright © OOCS Commerce (https://www.oocscommerce.com). All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace OOCS\LynxProductAlert\Provider;

use Magento\Catalog\Model\Product;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\Registry;
use Magento\Framework\Url\EncoderInterface;
use Magento\Framework\UrlInterface;
use Magento\ProductAlert\Helper\Data as AlertHelper;
use OOCS\LynxComponent\Model\AbstractComponentProvider;

/**
 * The stock alert sign-up link of the product page, as Magento_ProductAlert puts it there.
 */
class StockAlert extends AbstractComponentProvider
{
    /**
     * Registry, product alert helper, url builder and encoder
     *
     * @param Registry $registry
     * @param AlertHelper $alertHelper
     * @param UrlInterface $urlBuilder
     * @param EncoderInterface $urlEncoder
     */
    public function __construct(
        private readonly Registry $registry,
        private readonly AlertHelper $alertHelper,
        private readonly UrlInterface $urlBuilder,
        private readonly EncoderInterface $urlEncoder,
    ) {
    }

    /**
     * Add the sign-up url, empty when the alert does not apply, and the link text
     *
     * @param array $props
     * @return array
     */
    public function resolve(array $props): array
    {
        /** @var Product|null $product */
        $product = $this->registry->registry('current_product');
        // As Block\Product\View\Stock: only a product that cannot be bought now
        $props['url'] = $product && $this->alertHelper->isStockAlertAllowed() && !$product->isAvailable()
            ? $this->urlBuilder->getUrl('productalert/add/stock', [
                'product_id' => $product->getId(),
                // Back to the product's own url: the current one carries the _lynx params of a page visit
                ActionInterface::PARAM_NAME_URL_ENCODED => $this->urlEncoder->encode($product->getProductUrl()),
            ])
            : '';
        $props['label'] = (string) __('Notify me when this product is in stock');

        return $props;
    }
}
