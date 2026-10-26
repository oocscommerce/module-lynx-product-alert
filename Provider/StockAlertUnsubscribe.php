<?php
/**
 * Copyright © OOCS Commerce (https://www.oocscommerce.com). All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace OOCS\LynxProductAlert\Provider;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\UrlInterface;
use OOCS\LynxComponent\Api\SampleProviderInterface;
use OOCS\LynxComponent\Model\AbstractComponentProvider;

/**
 * What the stock alert's unsubscribe link posts on, as core's email/email.phtml form does.
 */
class StockAlertUnsubscribe extends AbstractComponentProvider implements SampleProviderInterface
{
    /**
     * Request and url builder
     *
     * @param RequestInterface $request
     * @param UrlInterface $urlBuilder
     */
    public function __construct(
        private readonly RequestInterface $request,
        private readonly UrlInterface $urlBuilder,
    ) {
    }

    /**
     * Add the endpoint and the product the email link names
     *
     * @param array $props
     * @return array
     */
    public function resolve(array $props): array
    {
        $props['action'] = $this->urlBuilder->getUrl('productalert/unsubscribe/stock');
        $props['product_id'] = (int) $this->request->getParam('product');
        $props['labels'] = [
            'loading' => (string) __('Loading...'),
        ];

        return $props;
    }

    /**
     * The same page posting nowhere
     *
     * @param array $resolved
     * @param array $props
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function resolveSample(array $resolved, array $props): array
    {
        $resolved['action'] = '#';

        return $resolved;
    }
}
