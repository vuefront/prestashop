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

class ResolverStoreManufacturer extends Resolver
{
    public function get($args)
    {
        if (empty($args['id'])) {
            return array();
        }
        $this->load->model('store/manufacturer');
        $manufacturer_info = $this->model_store_manufacturer->getManufacturer($args['id']);

        if (empty($manufacturer_info)) {
            return array();
        }

        $that = $this;

        return array(
            'id'          => $manufacturer_info['id'],
            'name'        => html_entity_decode($manufacturer_info['name'], ENT_QUOTES, 'UTF-8'),
            'image'       => $manufacturer_info['image'],
            'imageLazy'   => $manufacturer_info['imageLazy'],
            'imageBig'     => $manufacturer_info['imageBig'],
            'url'         => function($root, $args) use ($that) {
                return $that->url(array(
                    'parent' => $root,
                    'args' => $args
                ));
            },
            'keyword'     => $manufacturer_info['keyword'],
            'sort_order'  => 0
        );
    }

    public function getList($args)
    {
        $this->load->model('store/manufacturer');

        $filter_data = array(
            'sort' => $args['sort'],
            'order'   => $args['order']
        );

        if ($args['size'] !== -1) {
            $filter_data['start'] = ($args['page'] - 1) * $args['size'];
            $filter_data['limit'] = $args['size'];
        }

        if (!empty($args['search'])) {
            $filter_data['filter_name'] = $args['search'];
        }

        $results = $this->model_store_manufacturer->getManufacturers($filter_data);

        $manufacturer_total = $this->model_store_manufacturer->getTotalManufacturers($filter_data);

        $manufacturers = array();

        foreach ($results as $result) {
            $manufacturers[] = $this->get(array('id' => $result['id_manufacturer']));
        }

        return array(
            'content' => $manufacturers,
            'first' => $args['page'] === 1,
            'last' => $args['page'] === ceil($manufacturer_total / $args['size']),
            'number' => (int)$args['page'],
            'numberOfElements' => count($manufacturers),
            'size' => (int)$args['size'],
            'totalPages' => (int)ceil($manufacturer_total / $args['size']),
            'totalElements' => (int)$manufacturer_total,
        );
    }


    public function url($data)
    {
        $category_info = $data['parent'];
        $result = $data['args']['url'];

        $result = str_replace("_id", $category_info['id'], $result);
        $result = str_replace("_name", $category_info['name'], $result);


        if ($category_info['keyword']) {
            $result = '/'.$category_info['keyword'];
            $this->load->model('common/seo');
            $this->model_common_seo->addUrl($result, 'manufacturer', $category_info['id']);
        }


        return $result;
    }
}