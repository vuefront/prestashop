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

class ModelStoreWishlist extends Model
{
    public function getWishlist()
    {
        $result = array();

        if (isset($this->context->cookie->vf_wishlist)) {
            $result = json_decode($this->context->cookie->vf_wishlist, true);
        }

        return $result;
    }

    public function addWishlist($product_id)
    {
        $wishlist = array();

        if (isset($this->context->cookie->vf_wishlist)) {
            $wishlist = json_decode($this->context->cookie->vf_wishlist, true);
        }


        if (!in_array($product_id, $wishlist)) {
            $wishlist[] = (int)$product_id;
        }

        $this->context->cookie->vf_wishlist = json_encode($wishlist);
    }

    public function deleteWishlist($product_id)
    {
        $wishlist = array();

        if (isset($this->context->cookie->vf_wishlist)) {
            $wishlist = json_decode($this->context->cookie->vf_wishlist, true);
        }
   
        if (!empty($wishlist)) {
            $key = array_search($product_id, $wishlist);

            if ($key !== false) {
                unset($wishlist[$key]);
            }
        }

        $this->context->cookie->vf_wishlist = json_encode($wishlist);
    }
}
