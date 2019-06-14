<?php

use Youshido\GraphQL\Type\Object\ObjectType;
use Youshido\GraphQL\Type\Scalar\IdType;
use Youshido\GraphQL\Type\Scalar\IntType;
use Youshido\GraphQL\Type\Scalar\StringType;

class pageVFType extends VFType
{
    private $codename = "d_vuefront";

    public function query()
    {
        $this->load->model($this->codename);

        return array(
            'page' => array(
                'type' => $this->pageType(),
                'args' => array(
                    'id' => array(
                        'type' => new IntType(),
                    ),
                ),
                'resolve' => function ($store, $args) {
                    return $this->load->resolver('page/page', $args);
                },
            ),
            'pagesList' => array(
                'type' => $this->model_d_vuefront->getPagination($this->pageType()),
                'args' => array(
                    'page' => array(
                        'type' => new IntType(),
                        'defaultValue' => 1,
                    ),
                    'size' => array(
                        'type' => new IntType(),
                        'defaultValue' => 10,
                    ),
                    'search' => array(
                        'type' => new StringType(),
                        'defaultValue' => '',
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
                    return $this->load->resolver('page/pageList', $args);
                },
            ),
        );
    }

    public function mutation()
    {
        return false;
    }

    private function pageType()
    {
        return new ObjectType(array(
            'name' => 'Page',
            'description' => 'Page',
            'fields' => array(
                'id' => new IdType(),
                'title' => new StringType(),
                'description' => new StringType(),
                'sort_order' => new IntType(),
            ),
        ));
    }
}
