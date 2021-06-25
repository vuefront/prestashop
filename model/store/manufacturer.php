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

class ModelStoreManufacturer extends Model
{

    public function getManufacturer($id)
    {
        $manufacturer = new Manufacturer((int) $id, (int) $this->context->language->id, 1);

        $dispatcher = Dispatcher::getInstance();

        $params = array();

        $params['id'] = $manufacturer->id;
        $params['rewrite'] = $manufacturer->link_rewrite;

        if ($dispatcher->hasKeyword(
            'manufacturer_rule',
            $this->context->cookie->id_lang,
            'meta_keywords',
            $this->context->cookie->id_shop
        )) {
            $params['meta_keywords'] = Tools::str2url($manufacturer->getFieldByLang('meta_keywords'));
        }
        if ($dispatcher->hasKeyword(
            'manufacturer_rule',
            $this->context->cookie->id_lang,
            'meta_title',
            $this->context->cookie->id_shop
        )) {
            $params['meta_title'] = Tools::str2url($manufacturer->getFieldByLang('meta_title'));
        }

        $url = Dispatcher::getInstance()->createUrl(
            'manufacturer_rule',
            $this->context->cookie->id_lang,
            $params,
            true,
            '',
            $this->context->cookie->id_shop
        );

        return array(
            'id' => $manufacturer->id,
            'name' => $manufacturer->name,
            'description' => html_entity_decode($manufacturer->description, ENT_QUOTES, 'UTF-8'),
            'image' => $this->context->link->getManufacturerImageLink($manufacturer->id, ImageType::getFormatedName("medium")),
            'imageBig' => $this->context->link->getManufacturerImageLink($manufacturer->id, ImageType::getFormatedName("large")),
            'imageLazy' => $this->context->link->getManufacturerImageLink($manufacturer->id, ImageType::getFormatedName("small")),
            'keyword' => $url,
            'meta' => array(
                'title' => $manufacturer->meta_title,
                'description' => $manufacturer->meta_description,
                'keyword' => $manufacturer->meta_keywords,
            ),
        );
    }

    public function getManufacturers($data)
    {
        $sort = 'm.`name`';
        if ($data['sort'] == 'id') {
            $sort = 'm.`id_manufacturer`';
        }

        $sql = new DbQuery();
        $sql->select('*');
        $sql->from('manufacturer', 'm');
        $sql->leftJoin('manufacturer_shop', 'ms', 'ms.`id_manufacturer` = m.`id_manufacturer`');
        $sql->leftJoin('manufacturer_lang', 'ml', 'ms.`id_manufacturer` = m.`id_manufacturer`');
        $sql->where('ml.`id_lang` = ' . (int) $this->context->language->id);
        $sql->where('m.`active` = 1');
        if (!empty($data['filter_name'])) {
            $sql->where("m.`name` LIKE '%" . pSQL($data['filter_name']). "%'");
        }

        $sql->orderBy($sort . ' ' . $data['order']);
        if (!empty($data['limit']) && $data['limit'] != -1) {
            $sql->limit($data['limit'], $data['start']);
        }

        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);

        return $result;
    }

    public function getTotalManufacturers($data)
    {
        $sql = new DbQuery();
        $sql->select('count(*)');
        $sql->from('manufacturer', 'm');
        $sql->leftJoin('manufacturer_shop', 'ms', 'ms.`id_manufacturer` = m.`id_manufacturer`');
        $sql->leftJoin('manufacturer_lang', 'ml', 'ms.`id_manufacturer` = m.`id_manufacturer`');
        $sql->where('ml.`id_lang` = ' . (int) $this->context->language->id);
        $sql->where('m.`active` = 1');

        if (!empty($data['filter_name'])) {
            $sql->where("m.`name` LIKE '%" . pSQL($data['filter_name']). "%'");
        }


        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql);
        return $result['count(*)'];
    }
}