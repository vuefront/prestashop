<?php

class ResolverCommonZone extends Resolver
{
    private $codename = "d_vuefront";

    public function get($args)
    {
        $this->load->model('common/zone');

        $zone_info = $this->model_common_zone->getZone($args['id']);

        if (!$zone_info) {
            return array();
        }


        return array(
           'id' => $args['id'],
           'name' => $zone_info['name'],
           'countryId' => $zone_info['country_id']
        );
    }

    public function getList($args)
    {
        $this->load->model('common/zone');
        $zones = [];

        $filter_data = array(
            'sort' => $args['sort'],
            'order'   => $args['order']
        );

        if ($args['size'] != - 1) {
            $filter_data['start'] = ($args['page'] - 1) * $args['size'];
            $filter_data['limit'] = $args['size'];
        }

        if (!empty($args['search'])) {
            $filter_data['filter_name'] = $args['search'];
        }
        if (!empty($args['country_id'])) {
            $filter_data['filter_country_id'] = $args['country_id'];
        }


        $results = $this->model_common_zone->getZones($filter_data);
        $zone_total = $this->model_common_zone->getTotalZones($filter_data);

        foreach ($results as $value) {
            $zones[] = $this->get(array( 'id' => $value['zone_id'] ));
        }

        return array(
            'content'          => $zones,
            'first'            => $args['page'] === 1,
            'last'             => $args['page'] === ceil($zone_total / $args['size']),
            'number'           => (int) $args['page'],
            'numberOfElements' => count($zones),
            'size'             => (int) $args['size'],
            'totalPages'       => (int) ceil($zone_total / $args['size']),
            'totalElements'    => (int) $zone_total,
        );
    }
}
