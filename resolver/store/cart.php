<?php

class ResolverStoreCart extends Resolver
{
    public function add($args)
    {
        global $cookie;

        $qty = $args['quantity'];
        $product_attribute_id = 0;

        $groups = array();

        foreach ($args['options'] as $value) {
            $groups[$value['id']] = $value['value'];
        }


        if (!empty($groups)) {
            $product_attribute_id = (int)Product::getIdProductAttributeByIdAttributes(
                $args['id'],
                $groups,
                true
            );
        }

        $producToAdd = new Product((int)($args['id']), true, (int)($cookie->id_lang));

        if ((!$producToAdd->id or !$producToAdd->active)) {
            throw new Exception("Failed");
        }

        if ($product_attribute_id > 0 and is_numeric($product_attribute_id)) {
            if (!$producToAdd->isAvailableWhenOutOfStock($producToAdd->out_of_stock) and !Attribute::checkAttributeQty((int)$product_attribute_id, (int)$qty)) {
                $qty = getAttributeQty($product_attribute_id);
            }
        } elseif (!$producToAdd->checkQty((int)$qty)) {
            $qty = $producToAdd->getQuantity($args['id']);
        }


        $this->context->cart->updateQty((int)($qty), (int)($args['id']), (int)($product_attribute_id), null, 'up');
        $this->context->cart->update();

        return $this->get($args);
    }

    public function update($args)
    {
        $id = explode('-', $args['key'])[0];
        $product_attribute_id = explode('-', $args['key'])[1];

        $product_quantity = $this->context->cart->getProductQuantity($id, $product_attribute_id);
        $diff = $args['quantity'] - $product_quantity['quantity'];
        if ($diff > 0) {
            $this->context->cart->updateQty((int)($diff), (int)($id), (int)($product_attribute_id), null, 'up');
        } elseif ($diff < 0) {
            $this->context->cart->updateQty((int)((-1)*$diff), (int)($id), (int)($product_attribute_id), null, 'down');
        }


        $this->context->cart->update();

        return $this->get($args);
    }

    public function remove($args)
    {
        $id = explode('-', $args['key'])[0];
        $product_attribute_id = explode('-', $args['key'])[1];

        $this->context->cart->deleteProduct((int)($id), (int)($product_attribute_id), null);

        $this->context->cart->update();

        return $this->get($args);
    }

    public function get($args)
    {
        $cartData = array(
            'products' => array(),
            'total' => Tools::displayPrice($this->context->cart->getOrderTotal())
        );

        $results = $this->context->cart->getProducts();

        foreach ($results as $value) {
            $options = array();

            if (!empty($value['attributes'])) {
                $attr = explode('-', $value['attributes']);

                foreach ($attr as $attrValue) {
                    $options[] = array(
                        'name' => trim(explode(':', $attrValue)[0]),
                        'type' => 'radio',
                        'value' => trim(explode(':', $attrValue)[1])
                    );
                }
            }


            $cartData['products'][] = array(
                'key' => $value['id_product'] . '-' . $value['id_product_attribute'],
                'product' => $this->load->resolver('store/product/get', array('id' => $value['id_product'])),
                'quantity' => $value['cart_quantity'],
                'option' => $options,
                'total' => Tools::displayPrice($value['total'])
            );
        }

        return $cartData;
    }
}
