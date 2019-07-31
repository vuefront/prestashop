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

class ResolverBlogCategory extends Resolver
{
    private $status = false;

    public function __construct($registry)
    {
        parent::__construct($registry);
        $this->status = Module::isInstalled('prestablog');
    }

    public function get($data)
    {
        if ($this->status) {
            $this->load->model('blog/category');
            $category = $this->model_blog_category->getCategory($data['id']);
            $that = $this;

            return array(
                'id' => $category['id'],
                'name' => $category['name'],
                'description' => $category['description'],
                'parent_id' => (string) $category['parent_id'],
                'image' => $category['image'],
                'imageLazy' => $category['imageLazy'],
                'keyword' => $category['keyword'],
                'meta' => array(
                    'title' => $category['meta']['title'],
                    'description' => $category['meta']['description'],
                    'keyword' => $category['meta']['keyword'],
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
            );
        } else {
            return array();
        }
    }

    public function getList($args)
    {
        if ($this->status) {
            $this->load->model('blog/category');
            $filter_data = array(
                'limit' => $args['size'],
                'start' => ($args['page'] - 1) * $args['size'],
                'sort' => $args['sort'],
                'order' => $args['order'],
            );

            if ($args['parent'] !== -1) {
                $filter_data['filter_parent_id'] = $args['parent'];
            }

            $product_categories = $this->model_blog_category->getCategories($filter_data);

            $category_total = $this->model_blog_category->getTotalCategories($filter_data);

            $categories = array();

            foreach ($product_categories as $category) {
                $categories[] = $this->get(array('id' => $category['id_prestablog_categorie']));
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
        } else {
            return array(
                'content' => array(),
            );
        }
    }

    public function child($data)
    {
        $this->load->model('blog/category');
        $category = $data['parent'];
        $filter_data = array(
            'filter_parent_id' => $category['id'],
        );

        $blog_categories = $this->model_blog_category->getCategories($filter_data);

        $categories = array();

        foreach ($blog_categories as $category) {
            $categories[] = $this->get(array('id' => $category['id_prestablog_categorie']));
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
