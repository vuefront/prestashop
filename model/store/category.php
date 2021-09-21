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

use PrestaShop\PrestaShop\Adapter\Image\ImageRetriever;

class ModelStoreCategory extends Model
{
    public function getCategory($category_id)
    {
        $category = new Category((int) $category_id, (int) $this->context->language->id, 1);
        if (_PS_VERSION_ >= '1.7.0.0') {
            $retriever = new ImageRetriever(
                $this->context->link
            );
            $image = $retriever->getImage(
                $category,
                $category->id_image
            );
            $thumb = $image['large']['url'];
            $thumbLazy = $image['small']['url'];
        } else {
            $thumb = $this->context->link->getCatImageLink($category->link_rewrite, $category->id_image);
            $thumbLazy = $this->context->link->getCatImageLink($category->link_rewrite, $category->id_image);
        }
        $dispatcher = Dispatcher::getInstance();

        $params = array();

        $params['id'] = $category->id;
        $params['rewrite'] = $category->link_rewrite;

        if ($dispatcher->hasKeyword(
            'category_rule',
            $this->context->cookie->id_lang,
            'meta_keywords',
            $this->context->cookie->id_shop
        )) {
            $params['meta_keywords'] = Tools::str2url($category->getFieldByLang('meta_keywords'));
        }
        if ($dispatcher->hasKeyword(
            'category_rule',
            $this->context->cookie->id_lang,
            'meta_title',
            $this->context->cookie->id_shop
        )) {
            $params['meta_title'] = Tools::str2url($category->getFieldByLang('meta_title'));
        }

        $url = Dispatcher::getInstance()->createUrl(
            'category_rule',
            $this->context->cookie->id_lang,
            $params,
            true,
            '',
            $this->context->cookie->id_shop
        );

        return array(
            'id' => $category->id,
            'name' => $category->name,
            'description' => html_entity_decode($category->description, ENT_QUOTES, 'UTF-8'),
            'parent_id' => $category->id_parent,
            'image' => $thumb,
            'imageLazy' => $thumbLazy,
            'keyword' => $url,
            'meta' => array(
                'title' => $category->meta_title,
                'description' => $category->meta_description,
                'keyword' => $category->meta_keywords,
            ),
        );
    }

    public function getCategories($data = array())
    {
        $parent_id = false;
        if (isset($data['filter_parent_id']) && $data['filter_parent_id'] !== -1) {
            if ($data['filter_parent_id'] == 0) {
                $parent_id = 2;
            } else {
                $parent_id = $data['filter_parent_id'];
            }
        }

        $sort = 'c.`id_parent`';
        if ($data['sort'] == 'sort_order') {
            $sort = 'c.`id_parent`, c.`position`';
        }
        if ($data['sort'] == 'name') {
            $sort = 'cl.`name`';
        }

        $sql = new DbQuery();
        $sql->select('*');
        $sql->from('category', 'c');
        $sql->leftJoin('category_shop', 'cs', 'cs.`id_category` = c.`id_category`');
        $sql->leftJoin('category_lang', 'cl', 'cl.`id_category` = c.`id_category`');
        $sql->where('c.`active` = 1');
        $sql->where('cl.`id_lang` = ' . (int) $this->context->language->id);

        if ($parent_id) {
            $sql->where('c.`id_parent` = ' . (int)$parent_id);
        }
        if (!empty($data['filter_name'])) {
            $sql->where('cl.`name` LIKE \'%' . pSQL($data['filter_name'])
            .'%\' OR cl.`description` LIKE \'%' . pSQL($data['filter_name']).'%\'');
        }

        $sql->orderBy($sort . ' ' . $data['order']);
        if (!empty($data['limit']) && $data['limit'] != -1) {
            $sql->limit($data['limit'], $data['start']);
        }

        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);

        return $result;
    }

    public function getTotalCategories($data = array())
    {
        $parent_id = false;
        if (isset($data['filter_parent_id']) && $data['filter_parent_id'] !== -1) {
            if ($data['filter_parent_id'] == 0) {
                $parent_id = 2;
            } else {
                $parent_id = $data['filter_parent_id'];
            }
        }

        $language_id = $this->context->language->id;

        $sql = new DbQuery();
        $sql->select('count(*)');
        $sql->from('category', 'c');
        $sql->leftJoin('category_shop', 'cs', 'cs.`id_category` = c.`id_category`');
        $sql->leftJoin('category_lang', 'cl', 'cl.`id_category` = c.`id_category`');
        $sql->where('c.`active` = 1');
        $sql->where('cl.`id_lang` = ' . (int) $language_id);

        if ($parent_id) {
            $sql->where('c.`id_parent` = ' . (int)$parent_id);
        }

        if (!empty($data['filter_name'])) {
            $sql->where('cl.`name` LIKE \'%' . pSQL($data['filter_name'])
            .'%\' OR cl.`description` LIKE \'%' . pSQL($data['filter_name']).'%\'');
        }

        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql);

        return $result['count(*)'];
    }
}
