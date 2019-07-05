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

class ResolverStoreWishlist extends Resolver
{
    public function add($args)
    {
        $this->load->model('store/wishlist');

        $this->model_store_wishlist->addWishlist($args['id']);

        return $this->getList();
    }
    public function remove($args)
    {
        $this->load->model('store/wishlist');
        $this->model_store_wishlist->deleteWishlist($args['id']);

        return $this->getList();
    }
    public function getList()
    {
        $this->load->model('store/wishlist');
        $wishlist = array();
        $results = $this->model_store_wishlist->getWishlist();

        foreach ($results as $product_id) {
            $wishlist[] = $this->load->resolver('store/product/get', array('id' => $product_id));
        }

        return $wishlist;
    }
}
