<?php
/**
 * 2019 (c) VueFront
 *
 * MODULE VueFront
 *
 * @author    VueFront
 * @copyright Copyright (c) permanent, VueFront
 * @license   MIT
 * @version   0.1.0
 */

class ResolverStoreCheckout extends Resolver
{
    public function link()
    {
        return array(
            'link' => $this->context->link->getPageLink('order')
        );
    }
}
