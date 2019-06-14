<?php

class CartVFResolver extends VFResolver
{
    private $codename = "d_vuefront";

    public function cart($args)
    {
        // $cart = array();
        // $results = $this->cart->getProducts();

        // $cart['products'] = array();

        // foreach ($results as $product) {

        //     if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
        //         $unit_price = $this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax'));

        //         $price = $this->currency->format($unit_price, $this->session->data['currency']);
        //         $total = $this->currency->format($unit_price * $product['quantity'], $this->session->data['currency']);
        //     } else {
        //         $price = false;
        //         $total = false;
        //     }

        //     $product_info = $this->load->controller('extension/' . $this->codename . '/product/product', array('id' => $product['product_id']));
        //     $product_info['price'] = $price;

        //     $cart['products'][] = array(
        //         'key' => $product['cart_id'],
        //         'product' => $product_info,
        //         'quantity' => (int) $product['quantity'],
        //         'total' => $total,
        //     );
        // }

        // return $cart;
        return array();
    }

    public function addToCart($args)
    {
        // $this->request->post['product_id'] = $args['id'];
        // $this->request->post['quantity'] = $args['quantity'];
        // $this->request->post['option'] = array();

        // foreach ($args['options'] as $option) {
        //     $this->request->post['option'][$option['id']] = $option['value'];
        // }
        // $this->load->controller('checkout/cart/add');

        // return $this->cart(array());
        return array();
    }

    public function updateCart($args)
    {
        // $this->cart->update($args['key'], $args['quantity']);

        // return $this->cart(array());
        return array();
    }

    public function removeCart($args)
    {
        // $this->cart->remove($args['key']);

        // return $this->cart(array());
        return array();
    }
}
