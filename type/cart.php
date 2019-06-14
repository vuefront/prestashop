<?php

use Youshido\GraphQL\Type\InputObject\InputObjectType;
use Youshido\GraphQL\Type\ListType\ListType;
use Youshido\GraphQL\Type\Object\ObjectType;
use Youshido\GraphQL\Type\Scalar\IntType;
use Youshido\GraphQL\Type\Scalar\StringType;

class cartVFType extends VFType
{
    private $codename = "d_vuefront";

    public function query()
    {
        return array(
            'cart' => array(
                'type' => $this->cartType(),
                'resolve' => function ($store, $args) {
                    return $this->load->resolver('cart/cart');
                },
            ),
        );
    }

    public function mutation()
    {
        return array(
            'addToCart' => array(
                'type' => $this->cartType(),
                'args' => array(
                    'id' => array(
                        'type' => new IntType(),
                    ),
                    'quantity' => array(
                        'type' => new IntType(),
                        'defaultValue' => 1,
                    ),
                    'options' => array(
                        'type' => new ListType($this->cartOptionType()),
                        'defaultValue' => array(),
                    ),
                ),
                'resolve' => function ($store, $args) {
                    return $this->load->resolver('cart/addToCart', $args);
                },
            ),
            'updateCart' => array(
                'type' => $this->cartType(),
                'args' => array(
                    'key' => array(
                        'type' => new StringType(),
                    ),
                    'quantity' => array(
                        'type' => new IntType(),
                        'defaultValue' => 1,
                    ),
                ),
                'resolve' => function ($store, $args) {
                    return $this->load->resolver('cart/updateCart', $args);
                },
            ),
            'removeCart' => array(
                'type' => $this->cartType(),
                'args' => array(
                    'key' => array(
                        'type' => new StringType(),
                    ),
                ),
                'resolve' => function ($store, $args) {
                    return $this->load->resolver('cart/removeCart', $args);
                },
            ),
        );
    }

    public function cartType()
    {
        return new ObjectType(
            array(
                'name' => 'Cart',
                'description' => 'Cart',
                'fields' => array(
                    'products' => new ListType($this->cartProductType()),
                ),
            )
        );
    }

    private function cartOptionType()
    {
        return new InputObjectType(
            array(
                'name' => 'CartOption',
                'description' => 'CartOption',
                'fields' => array(
                    'id' => new StringType(),
                    'value' => new StringType(),
                ),
            )
        );
    }

    public function cartProductType()
    {
        return new ObjectType(
            array(
                'name' => 'CartProduct',
                'description' => 'CartProduct',
                'fields' => array(
                    'key' => new StringType(),
                    'product' => $this->load->type('product')->productType(),
                    'quantity' => new IntType(),
                    'total' => new StringType(),
                ),
            )
        );
    }
}
