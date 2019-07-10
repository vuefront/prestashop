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

class ModelCommonPage extends Model
{
    public function getPage($page_id)
    {
        $page = new CMS($page_id, $this->context->language->id, $this->context->shop->id);

        $dispatcher = Dispatcher::getInstance();
        $params = array();
        $params['id'] = $page->id;
        $params['rewrite'] = $page->link_rewrite;
        $params['meta_keywords'] = Tools::str2url($page->meta_keywords);
        $params['meta_title'] = Tools::str2url($page->meta_title);

        $url = $dispatcher->createUrl(
            'cms_category_rule',
            $this->context->cookie->id_lang,
            $params,
            true,
            '',
            $this->context->cookie->id_shop
        );

        return array(
            'id' => $page->id,
            'title' => $page->meta_title,
            'description' => html_entity_decode($page->content, ENT_QUOTES, 'UTF-8'),
            'sort_order' => (int) $page->position,
            'keyword' => $url
        );
    }

    public function getPages($data = array())
    {
        $sort = '';
        if ($data['sort'] == 'sort_order') {
            $sort = 'c.`id_cms`, c.`position`';
        }

        if ($data['sort'] == 'title') {
            $sort = 'cl.`meta_title`';
        }

        if ($data['sort'] == 'name') {
            $sort = 'cl.`meta_title`';
        }

        $sql = new DbQuery();
        $sql->select('*');
        $sql->from('cms', 'c');
        $sql->leftJoin('cms_lang', 'cl', 'cl.`id_cms` = c.`id_cms`');
        $sql->where('c.`active` = 1');
        $sql->where('cl.`id_lang` = ' . (int) $this->context->language->id);

        if (!empty($data['filter_title']) && !empty($data['filter_description'])) {
            $sql->where("cl.`meta_title` = '%" . $data['filter_title'] .
            "%' OR cl.content = '%" . $data['filter_description'] .
            "%' OR cl.meta_description = '%" . $data['filter_description'] . "%'");
        }

        $sql->orderBy($sort . ' ' . $data['order']);
        if (!empty($data['limit']) && $data['limit'] != -1) {
            $sql->limit($data['limit'], $data['start']);
        }

        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);

        return $result;
    }

    public function getTotalPages($data = array())
    {
        $sql = new DbQuery();
        $sql->select('count(*)');
        $sql->from('cms', 'c');
        $sql->leftJoin('cms_lang', 'cl', 'cl.`id_cms` = c.`id_cms`');
        $sql->where('c.`active` = 1');
        $sql->where('cl.`id_lang` = ' . (int) $this->context->language->id);

        if (!empty($data['filter_title']) && !empty($data['filter_description'])) {
            $sql->where("cl.`meta_title` = '%" .
            $data['filter_title'] . "%' OR cl.content = '%" .
             $data['filter_description'] . "%' OR cl.meta_description = '%" .
             $data['filter_description'] . "%'");
        }

        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql);
        return $result['count(*)'];
    }
}
