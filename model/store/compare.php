<?php
class ModelStoreCompare extends Model
{
    public function getCompare()
    {
        global $cookie;

         $result = array();

        if (isset($cookie->vf_compare)) {
            $result = json_decode($cookie->vf_compare, true);
        }

        return $result;
    }

    public function addCompare($product_id)
    {
        global $cookie;

        $compare = array();

        if (isset($cookie->vf_compare)) {
            $compare = json_decode($cookie->vf_compare, true);
        }


        if (!in_array($product_id, $compare)) {
            $compare[] = (int)$product_id;
        }

        $cookie->vf_compare = json_encode($compare);
    }

    public function deleteCompare($product_id)
    {
        global $cookie;

        $compare = array();

        if (isset($cookie->vf_compare)) {
            $compare = json_decode($cookie->vf_compare, true);
        }
   
        if (!empty($compare)) {
            $key = array_search($product_id, $compare);

            if ($key !== false) {
                unset($compare[$key]);
            }
        }

        $cookie->vf_compare = json_encode($compare);

    }
}
