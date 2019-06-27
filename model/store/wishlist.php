<?php
class ModelStoreWishlist extends Model
{
    public function getWishlist()
    {
        global $cookie;

        $result = array();

        if (isset($cookie->vf_wishlist)) {
            $result = json_decode($cookie->vf_wishlist, true);
        }

        return $result;
    }

    public function addWishlist($product_id)
    {
        global $cookie;

        $wishlist = array();

        if (isset($cookie->vf_wishlist)) {
            $wishlist = json_decode($cookie->vf_wishlist, true);
        }


        if (!in_array($product_id, $wishlist)) {
            $wishlist[] = (int)$product_id;
        }

        $cookie->vf_wishlist = json_encode($wishlist);
    }

    public function deleteWishlist($product_id)
    {
        global $cookie;

        $wishlist = array();

        if (isset($cookie->vf_wishlist)) {
            $wishlist = json_decode($cookie->vf_wishlist, true);
        }
   
        if (!empty($wishlist)) {
            $key = array_search($product_id, $wishlist);

            if ($key !== false) {
                unset($wishlist[$key]);
            }
        }

        $cookie->vf_wishlist = json_encode($wishlist);
    }
}
