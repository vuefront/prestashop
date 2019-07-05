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

class ModelStoreCompare extends Model
{
    public function getCompare()
    {
        $result = array();

        if (isset($this->context->cookie->vf_compare)) {
            $result = json_decode($this->context->cookie->vf_compare, true);
        }

        return $result;
    }

    public function addCompare($product_id)
    {
        $compare = array();

        if (isset($this->context->cookie->vf_compare)) {
            $compare = json_decode($this->context->cookie->vf_compare, true);
        }


        if (!in_array($product_id, $compare)) {
            $compare[] = (int)$product_id;
        }

        $this->context->cookie->vf_compare = json_encode($compare);
    }

    public function deleteCompare($product_id)
    {
        $compare = array();

        if (isset($this->context->cookie->vf_compare)) {
            $compare = json_decode($this->context->cookie->vf_compare, true);
        }
   
        if (!empty($compare)) {
            $key = array_search($product_id, $compare);

            if ($key !== false) {
                unset($compare[$key]);
            }
        }

        $this->context->cookie->vf_compare = json_encode($compare);
    }
}
