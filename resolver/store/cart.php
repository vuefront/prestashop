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

class ResolverStoreCart extends Resolver
{
    public function add($args)
    {
        $qty = $args['quantity'];
        $product_attribute_id = 0;

        $groups = array();

        foreach ($args['options'] as $value) {
            $groups[$value['id']] = $value['value'];
        }

        $producToAdd = new Product((int)($args['id']), true, (int)($this->context->cookie->id_lang));
        if (!empty($groups)) {
            if (_PS_VERSION_ > '1.7.0.0') {
                $product_attribute_id = (int)Product::getIdProductAttributeByIdAttributes(
                    $args['id'],
                    $groups,
                    true
                );
            } else {
                $product_attribute_id = (int)$this->getIdProductAttributeByIdAttributes(
                    $args['id'],
                    $groups,
                    true
                );
            }
        }


        if ((!$producToAdd->id or !$producToAdd->active)) {
            throw new Exception("Failed");
        }
        
        if (!$producToAdd->checkQty((int)$qty)) {
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

        if (_PS_VERSION_ > '1.7.0.0') {
            $product_quantity = $this->context->cart->getProductQuantity($id, $product_attribute_id);
        } else {
            $products = $this->context->cart->getProducts(true, $id);
            foreach ($products as $value) {
                if ($value['id_product_attribute'] == $product_attribute_id) {
                    $product_quantity = $value;
                }
            }
        }

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

    public function get()
    {
        $cartData = array(
            'products' => array(),
            'total' => Tools::displayPrice($this->context->cart->getOrderTotal())
        );

        $results = $this->context->cart->getProducts();

        foreach ($results as $value) {
            $options = array();

            if (!empty($value['attributes'])) {
                if (_PS_VERSION_ > '1.7.0.0') {
                    $attr = explode('-', $value['attributes']);
                } else {
                    $attr = explode(',', $value['attributes']);
                }
                
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

    /**
     * Get an id_product_attribute by an id_product and one or more
     * id_attribute.
     *
     * e.g: id_product 8 with id_attribute 4 (size medium) and
     * id_attribute 5 (color blue) returns id_product_attribute 9 which
     * is the dress size medium and color blue.
     *
     * @param int $idProduct
     * @param int|int[] $idAttributes
     * @param bool $findBest
     *
     * @return int
     *
     * @throws PrestaShopException
     */
    public static function getIdProductAttributeByIdAttributes($idProduct, $idAttributes, $findBest = false)
    {
        $idProduct = (int) $idProduct;

        if (!is_array($idAttributes) && is_numeric($idAttributes)) {
            $idAttributes = array((int) $idAttributes);
        }

        if (!is_array($idAttributes) || empty($idAttributes)) {
            throw new PrestaShopException(
                sprintf(
                    'Invalid parameter $idAttributes with value: "%s"',
                    print_r($idAttributes, true)
                )
            );
        }

        $idAttributesImploded = implode(',', array_map('intval', $idAttributes));
        $idProductAttribute = Db::getInstance()->getValue(
            '
            SELECT
                pac.`id_product_attribute`
            FROM
                `' . _DB_PREFIX_ . 'product_attribute_combination` pac
                INNER JOIN `' . _DB_PREFIX_ . 'product_attribute` pa 
                ON pa.id_product_attribute = pac.id_product_attribute
            WHERE
                pa.id_product = ' . $idProduct . '
                AND pac.id_attribute IN (' . $idAttributesImploded . ')
            GROUP BY
                pac.`id_product_attribute`
            HAVING
                COUNT(pa.id_product) = ' . count($idAttributes)
        );

        if ($idProductAttribute === false && $findBest) {
            //find the best possible combination
            //first we order $idAttributes by the group position
            $orderred = array();
            $result = Db::getInstance()->executeS(
                '
                SELECT
                    a.`id_attribute`
                FROM
                    `' . _DB_PREFIX_ . 'attribute` a
                    INNER JOIN `' . _DB_PREFIX_ . 'attribute_group` g 
                    ON a.`id_attribute_group` = g.`id_attribute_group`
                WHERE
                    a.`id_attribute` IN (' . $idAttributesImploded . ')
                ORDER BY
                    g.`position` ASC'
            );

            foreach ($result as $row) {
                $orderred[] = $row['id_attribute'];
            }

            while ($idProductAttribute === false && count($orderred) > 0) {
                array_pop($orderred);
                $idProductAttribute = Db::getInstance()->getValue(
                    '
                    SELECT
                        pac.`id_product_attribute`
                    FROM
                        `' . _DB_PREFIX_ . 'product_attribute_combination` pac
                        INNER JOIN `' . _DB_PREFIX_ . 'product_attribute` pa 
                        ON pa.id_product_attribute = pac.id_product_attribute
                    WHERE
                        pa.id_product = ' . (int) $idProduct . '
                        AND pac.id_attribute IN (' . implode(',', array_map('intval', $orderred)) . ')
                    GROUP BY
                        pac.id_product_attribute
                    HAVING
                        COUNT(pa.id_product) = ' . count($orderred)
                );
            }
        }

        if (empty($idProductAttribute)) {
            throw new PrestaShopObjectNotFoundException('Can not retrieve the id_product_attribute');
        }

        return $idProductAttribute;
    }
}
