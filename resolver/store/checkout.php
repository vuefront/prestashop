<?php

class ResolverStoreCheckout extends Resolver
{
    public function link() {
        return array(
            'link' => $this->context->link->getPageLink('order')
        );
    }
}