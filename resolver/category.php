<?php

class CategoryVFResolver extends VFResolver
{
    private $codename = "d_vuefront";
    private $config_file = '';
    private $setting = array();

    public function __construct($registry)
    {
        parent::__construct($registry);
        $this->load->model('category');
    }

    public function category($args)
    {

        $category = $this->model_category->getCategory($args['id']);

        return array(
            'id' => $category['id'],
            'name' => $category['name'],
            'description' => $category['description'],
            'parent_id' => $category['parent_id'],
            'image' => $category['image'],
            'imageLazy' => $category['imageLazy'],
        );
    }

    public function categoryList($args)
    {
        $filter_data = array(
            'parent' => $args['parent'],
            'limit' => $args['size'],
            'start' => ($args['page'] - 1) * $args['size'],
            'sort' => $args['sort'],
            'order' => $args['order'],
        );

        $categories = $this->model_category->getCategories($filter_data);
        $category_total = $this->model_category->getTotalCategories($filter_data);

        return array(
            'content' => $categories,
            'first' => $args['page'] === 1,
            'last' => $args['page'] === ceil($category_total / $args['size']),
            'number' => (int) $args['page'],
            'numberOfElements' => count($categories),
            'size' => (int) $args['size'],
            'totalPages' => (int) ceil($category_total / $args['size']),
            'totalElements' => (int) $category_total,
        );

    }

    public function childCategories($data)
    {
        $category_info = $data['parent'];
        $categories = $this->model_category->getCategories(array('parent' => $category_info['id']));

        return $categories;
    }

    public function categoryUrl($data)
    {
        $category_info = $data['parent'];
        $result = $data['args']['url'];

        $result = str_replace("_id", $category_info['id'], $result);
        $result = str_replace("_name", $category_info['name'], $result);

        return $result;
    }
}
