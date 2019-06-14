<?php

class PageVFResolver extends VFResolver
{
    private $codename = "d_vuefront";

    public function page($args)
    {
        $this->load->model('page');
        $page = $this->model_page->getPage($args['id']);

        return array(
            'id' => $page['id'],
            'title' => $page['title'],
            'description' => $page['description'],
            'sort_order' => $page['sort_order'],
        );
    }

    public function pageList($args)
    {
        $this->load->model('page');

        // if (in_array($args['sort'], array('sort_order', 'title'))) {
        //     $args['sort'] = 'i.' . $args['sort'];
        // } elseif (in_array($args['sort'], array('name'))) {
        //     $args['sort'] = 'id.' . $args['sort'];
        // }

        $posts = array();

        $filter_data = array(
            'sort' => $args['sort'],
            'order' => $args['order'],
            'start' => ($args['page'] - 1) * $args['size'],
            'limit' => $args['size'],
        );

        if (!empty($args['search'])) {
            $filter_data['filter_title'] = $args['search'];
            $filter_data['filter_description'] = $args['search'];
        }

        $page_total = $this->model_page->getTotalPages($filter_data);

        $pages = $this->model_page->getPages($filter_data);

        return array(
            'content' => $pages,
            'first' => $args['page'] === 1,
            'last' => $args['page'] === ceil($page_total / $args['size']),
            'number' => (int) $args['page'],
            'numberOfElements' => count($pages),
            'size' => (int) $args['size'],
            'totalPages' => (int) ceil($page_total / $args['size']),
            'totalElements' => (int) $page_total,
        );
    }
}
