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

class ModelCommonCountry extends Model
{
    public function getCountry($country_id)
    {
        $sql = new DbQuery();
        $sql->select('*');
        $sql->from('country', 'c');
        $sql->leftJoin('country_shop', 'cs', 'cs.`id_country` = c.`id_country`');
        $sql->leftJoin('country_lang', 'cl', 'cl.`id_country` = c.`id_country`');
        $sql->where('cl.`id_lang` = ' . (int) $this->context->language->id);
        $sql->where('c.`id_country` = ' . (int) $country_id);

        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);

        return !empty($result) ? $result[0] : false;
    }

    public function getCountries($data)
    {
        $sort = 'cl.`name`';
        if ($data['sort'] == 'id') {
            $sort = 'c.`id_country`';
        }

        $sql = new DbQuery();
        $sql->select('*');
        $sql->from('country', 'c');
        $sql->leftJoin('country_shop', 'cs', 'cs.`id_country` = c.`id_country`');
        $sql->leftJoin('country_lang', 'cl', 'cl.`id_country` = c.`id_country`');
        $sql->where('cl.`id_lang` = ' . (int) $this->context->language->id);
        $sql->where('c.`active` = 1');
        if (!empty($data['filter_name'])) {
            $sql->where("cl.`name` LIKE '%" . pSQL($data['filter_name']). "%'");
        }

        $sql->orderBy($sort . ' ' . $data['order']);
        if (!empty($data['limit']) && $data['limit'] != -1) {
            $sql->limit($data['limit'], $data['start']);
        }

        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);

        return $result;
    }

    public function getTotalCountries($data)
    {
        $sql = new DbQuery();
        $sql->select('count(*)');
        $sql->from('country', 'c');
        $sql->leftJoin('country_shop', 'cs', 'cs.`id_country` = c.`id_country`');
        $sql->leftJoin('country_lang', 'cl', 'cl.`id_country` = c.`id_country`');
        $sql->where('cl.`id_lang` = ' . (int) $this->context->language->id);
        $sql->where('c.`active` = 1');
        if (!empty($data['filter_name'])) {
            $sql->where("cl.`name` LIKE '%" . pSQL($data['filter_name']). "%'");
        }


        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql);
        return $result['count(*)'];
    }
}
