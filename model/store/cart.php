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

class ModelStoreCart extends Model
{
    public function prepareCart()
    {
        $cart = array();
        $this->load->model('store/product');
        $cart['products'] = array();

        foreach ($this->context->cart->getProducts() as $value) {
            $options = array();
            if (!empty($value['id_product_attribute'])) {
                $sql = new DbQuery();
                $sql->select('*');
                $sql->from('product_attribute_combination', 'pac');
                $sql->where('pac.`id_product_attribute` = ' . (int) $value['id_product_attribute']);

                $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
                foreach ($result as $attribute) {
                    $attribute = new Attribute($attribute['id_attribute']);

                    $options[] = array(
                        'option_id' => $attribute->id_attribute_group,
                        'option_value_id' => $attribute->id
                    );
                }
            }
            $product = new Product($value['id_product']);

            $cart['products'][] = array(
                'key'      => $value['id_product'] . '-' . $value['id_product_attribute'],
                'product'  => array(
                    'product_id' => $value['id_product'],
                    'price' => Product::convertAndFormatPrice($product->getPriceWithoutReduct(true, null))
                ),
                'quantity' => $value['cart_quantity'],
                'option'   => $options,
                'total'    => Tools::displayPrice($value['total'])
            );
        }
        $cart['total'] = Tools::displayPrice($this->context->cart->getOrderTotal());
        return $cart;
    }
}
