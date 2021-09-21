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

class ResolverCommonHome extends Resolver
{
    public function get()
    {
        $meta_info = Meta::getMetaByPage('index', $this->context->language->id);

        return array(
            'meta' => array(
                'title' => $meta_info['title'],
                'description' => $meta_info['description'],
                'keyword' => $meta_info['keywords'],
            ),
        );
    }

    public function searchUrl($args)
    {
        $this->load->model('common/seo');

        $result = $this->model_common_seo->searchKeyword($args['url']);

        return $result;
    }

    public function updateApp($args)
    {
        $this->load->model('common/vuefront');
        $this->model_common_vuefront->editApp($args['name'], $args['settings']);

        return $this->model_common_vuefront->getApp($args['name']);
    }

    public function authProxy($args)
    {
        $this->load->model('common/vuefront');

        if (!$this->context->cookie->isLogged()) {
            return;
        }
        $app_info = $this->model_common_vuefront->getApp($args['app']);

        $url = str_replace(':id', $this->context->cookie->id_customer, $app_info['authUrl']);
        $result = $this->model_common_vuefront->request($url, [
            'customer_id' => $this->context->cookie->id_customer,
        ], $app_info['jwt']);

        if (!$result) {
            return '';
        }

        return $result['token'];
    }
}
