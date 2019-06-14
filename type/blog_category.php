<?php

use Youshido\GraphQL\Type\ListType\ListType;
use Youshido\GraphQL\Type\Object\ObjectType;
use Youshido\GraphQL\Type\Scalar\IdType;
use Youshido\GraphQL\Type\Scalar\IntType;
use Youshido\GraphQL\Type\Scalar\StringType;

class blog_categoryVFType extends VFType
{
    private $codename = "d_vuefront";

    public function query()
    {

        $this->load->model($this->codename);

        return array(
            'categoryBlog' => array(
                'type' => $this->categoryType(),
                'args' => array(
                    'id' => array(
                        'type' => new IntType(),
                    ),
                ),
                'resolve' => function ($store, $args) {
                    return $this->load->resolver('blog_category/category', $args);
                },
            ),
            'categoriesBlogList' => array(
                'type' => $this->model_d_vuefront->getPagination($this->categoryType()),
                'args' => array(
                    'page' => array(
                        'type' => new IntType(),
                        'defaultValue' => 1,
                    ),
                    'size' => array(
                        'type' => new IntType(),
                        'defaultValue' => 10,
                    ),
                    'filter' => array(
                        'type' => new StringType(),
                        'defaultValue' => '',
                    ),
                    'parent' => array(
                        'type' => new IntType(),
                        'defaultValue' => -1,
                    ),
                    'sort' => array(
                        'type' => new StringType(),
                        'defaultValue' => "sort_order",
                    ),
                    'order' => array(
                        'type' => new StringType(),
                        'defaultValue' => 'ASC',
                    ),
                ),
                'resolve' => function ($store, $args) {
                    return $this->load->resolver('blog_category/categoryList', $args);
                },
            ),
        );
    }

    public function mutation()
    {
        return false;
    }

    private function categoryType($simple = false)
    {
        $fields = array();
        if (!$simple) {
            $fields = array(
                'categories' => array(
                    'type' => new ListType($this->categoryType(true)),
                    'args' => array(
                        'limit' => array(
                            'type' => new IntType(),
                            'defaultValue' => 3,
                        ),
                    ),
                    'resolve' => function ($parent, $args) {
                        return $this->load->resolver('blog_category/childCategories', array(
                            'parent' => $parent,
                            'args' => $args,
                        ));
                    },
                ),
            );
        }
        return new ObjectType(array(
            'name' => 'categoryBlog',
            'description' => 'Blog Category',
            'fields' => array_merge(
                $fields,
                array(
                    'id' => new IdType(),
                    'image' => new StringType(),
                    'imageLazy' => new StringType(),
                    'name' => new StringType(),
                    'description' => new StringType(),
                    'parent_id' => new StringType(),
                    'url' => array(
                        'type' => new StringType,
                        'args' => array(
                            'url' => array(
                                'type' => new StringType(),
                                'defaultValue' => '_id',
                            ),
                        ),
                        'resolve' => function ($parent, $args) {
                            return $this->load->resolver('blog_category/categoryUrl', array(
                                'parent' => $parent,
                                'args' => $args,
                            ));
                        },
                    ),

                )
            ),
        ));
    }
}
