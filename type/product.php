<?php

use Youshido\GraphQL\Type\ListType\ListType;
use Youshido\GraphQL\Type\Object\ObjectType;
use Youshido\GraphQL\Type\Scalar\BooleanType;
use Youshido\GraphQL\Type\Scalar\FloatType;
use Youshido\GraphQL\Type\Scalar\IdType;
use Youshido\GraphQL\Type\Scalar\IntType;
use Youshido\GraphQL\Type\Scalar\StringType;

class productVFType extends VFType
{
    private $codename = "d_vuefront";

    public function query()
    {
        return array(
            'productsList' => $this->productsType(),
            'product' => array(
                'type' => $this->productType(),
                'args' => array(
                    'id' => array(
                        'type' => new IntType(),
                    ),
                ),
                'resolve' => function ($store, $args) {
                    return $this->load->resolver('product/product', $args);
                },
            ),
        );
    }

    public function mutation()
    {
        return array(
            'addReview' => array(
                'type' => $this->productType(),
                'args' => array(
                    'id' => new IntType(),
                    'rating' => new FloatType(),
                    'author' => new StringType(),
                    'content' => new StringType(),
                ),
                'resolve' => function ($store, $args) {
                    return $this->load->resolver('product/addReview', $args);
                },
            ),
        );
    }

    public function productsType()
    {
        $this->load->model($this->codename);

        return [
            'type' => $this->model_d_vuefront->getPagination($this->productType()),
            'args' => [
                'page' => array(
                    'type' => new IntType(),
                    'defaultValue' => 1,
                ),
                'size' => array(
                    'type' => new IntType(),
                    'defaultValue' => 20,
                ),
                'filter' => array(
                    'type' => new StringType(),
                    'defaultValue' => '',
                ),
                'special' => array(
                    'type' => new BooleanType(),
                    'defaultValue' => false,
                ),
                'search' => array(
                    'type' => new StringType(),
                    'defaultValue' => '',
                ),
                'ids' => array(
                    'type' => new ListType(new IntType()),
                    'defaultValue' => array(),
                ),
                'category_id' => array(
                    'type' => new IntType(),
                    'defaultValue' => 0,
                ),
                'sort' => array(
                    'type' => new StringType(),
                    'defaultValue' => "sort_order",
                ),
                'order' => array(
                    'type' => new StringType(),
                    'defaultValue' => 'ASC',
                ),
            ],
            'resolve' => function ($store, $args) {
                return $this->load->resolver('product/products', $args);
            },

        ];
    }

    private function getOptionValueType()
    {
        return new ObjectType(
            array(
                'name' => 'OptionValue',
                'description' => 'OptionValue',
                'fields' => array(
                    'id' => new StringType(),
                    'name' => new StringType(),
                ),
            )
        );
    }

    public function productType($simple = false)
    {
        $fields = array();

        if (!$simple) {
            $fields = array(
                'products' => array(
                    'type' => new ListType($this->productType(true)),
                    'args' => array(
                        'limit' => array(
                            'type' => new IntType(),
                            'defaultValue' => 3,
                        ),
                    ),
                    'resolve' => function ($parent, $args) {
                        return $this->load->resolver('product/relatedProducts', array(
                            'parent' => $parent,
                            'args' => $args,
                        ));
                    },
                ),
            );
        }

        return new ObjectType(
            array(
                'name' => 'Product',
                'description' => 'Product',
                'fields' => array_merge(
                    $fields,
                    array(
                        'id' => new IdType(),
                        'image' => new StringType(),
                        'imageLazy' => new StringType(),
                        'name' => new StringType(),
                        'shortDescription' => new StringType(),
                        'description' => new StringType(),
                        'model' => new StringType(),
                        'price' => new StringType(),
                        'special' => new StringType(),
                        'tax' => new StringType(),
                        'minimum' => new IntType(),
                        'stock' => new BooleanType(),
                        'rating' => new FloatType(),
                        'attributes' => array(
                            'type' => new ListType(
                                new ObjectType(
                                    array(
                                        'name' => 'productAttribute',
                                        'fields' => array(
                                            'name' => new StringType(),
                                            'options' => new ListType(new StringType()),
                                        ),
                                    )
                                )
                            ),
                            'resolve' => function ($parent, $args) {
                                return $this->load->resolver('product/productAttribute', array(
                                    'parent' => $parent,
                                    'args' => $args,
                                ));
                            },
                        ),
                        'reviews' => array(
                            'type' => new ListType(
                                new ObjectType(
                                    array(
                                        'name' => 'productReview',
                                        'fields' => array(
                                            'author' => new StringType(),
                                            'author_email' => new StringType(),
                                            'content' => new StringType(),
                                            'created_at' => new StringType(),
                                            'rating' => new FloatType(),
                                        ),
                                    )
                                )
                            ),
                            'resolve' => function ($parent, $args) {
                                return $this->load->resolver('product/productReview', array(
                                    'parent' => $parent,
                                    'args' => $args,
                                ));
                            },
                        ),
                        'options' => array(
                            'type' => new ListType(
                                new ObjectType(
                                    array(
                                        'name' => 'productOption',
                                        'fields' => array(
                                            'id' => new StringType(),
                                            'name' => new StringType(),
                                            'values' => new ListType($this->getOptionValueType()),
                                        ),
                                    )
                                )
                            ),
                            'resolve' => function ($parent, $args) {
                                return $this->load->resolver('product/productOption', array(
                                    'parent' => $parent,
                                    'args' => $args,
                                ));
                            },
                        ),
                        'images' => array(
                            'type' => new ListType(
                                new ObjectType(
                                    array(
                                        'name' => 'productImage',
                                        'fields' => array(
                                            'image' => new StringType(),
                                            'imageLazy' => new StringType(),
                                        ),
                                    )
                                )
                            ),
                            'args' => array(
                                'limit' => array(
                                    'type' => new IntType(),
                                    'defaultValue' => 3,
                                ),
                            ),
                            'resolve' => function ($parent, $args) {
                                return $this->load->resolver('product/productImage', array(
                                    'parent' => $parent,
                                    'args' => $args,
                                ));
                            },
                        ),
                    )
                ),
            )
        );
    }
}
