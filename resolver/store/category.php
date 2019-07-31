<?php
/**
 * 2019 (c) VueFront
 *
 * MODULE VueFront
 *
 * @author    VueFront
 * @copyright Copyright (c) permanent, VueFront
 * @license   MIT
 *
 * @version   0.1.0
 */

class ResolverStoreCategory extends Resolver
{
    public function get($args)
    {
        $this->load->model('store/category');

        $category_info = $this->model_store_category->getCategory($args['id']);

        $that = $this;

        return array(
            'id' => $category_info['id'],
            'name' => $category_info['name'],
            'description' => $category_info['description'],
            'parent_id' => (string) $category_info['parent_id'],
            'image' => $category_info['image'],
            'imageLazy' => $category_info['imageLazy'],
            'meta' => array(
                'title' => $category_info['meta']['title'],
                'description' => $category_info['meta']['description'],
                'keyword' => $category_info['meta']['keyword'],
            ),
            'url' => function ($root, $args) use ($that) {
                return $that->url(array(
                    'parent' => $root,
                    'args' => $args,
                ));
            },
            'categories' => function ($root, $args) use ($that) {
                return $that->child(array(
                    'parent' => $root,
                    'args' => $args,
                ));
            },
            'keyword' => $category_info['keyword'],
        );
    }

    public function getList($args)
    {
        $this->load->model('store/category');
        $filter_data = array(
            'sort' => $args['sort'],
            'order' => $args['order'],
        );
        if ($args['parent'] != -1) {
            $filter_data['filter_parent_id'] = $args['parent'];
        }

        if ($args['size'] != -1) {
            $filter_data['start'] = ($args['page'] - 1) * $args['size'];
            $filter_data['limit'] = $args['size'];
        }

        $product_categories = $this->model_store_category->getCategories($filter_data);

        $category_total = $this->model_store_category->getTotalCategories($filter_data);

        $categories = array();

        foreach ($product_categories as $category) {
            $categories[] = $this->get(array('id' => $category['id_category']));
        }

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

    public function child($data)
    {
        $this->load->model('store/category');
        $category = $data['parent'];
        $filter_data = array(
            'filter_parent_id' => $category['id'],
            'sort' => 'category_id',
            'order' => 'ASC',
        );

        $product_categories = $this->model_store_category->getCategories($filter_data);

        $categories = array();

        foreach ($product_categories as $category) {
            $categories[] = $this->get(array('id' => $category['id_category']));
        }

        return $categories;
    }

    public function url($data)
    {
        $category_info = $data['parent'];
        $result = $data['args']['url'];

        $result = str_replace('_id', $category_info['id'], $result);
        $result = str_replace('_name', $category_info['name'], $result);

        if ($category_info['keyword'] != '') {
            $result = '/' . $category_info['keyword'];
        }

        return $result;
    }
}
