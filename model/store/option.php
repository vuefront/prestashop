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

class ModelStoreOption extends Model
{
    public function getOptionValues($id) {
        $sql = new DbQuery();
        $sql->select('*');
        $sql->from('attribute', 'a');
        $sql->leftJoin('attribute_shop', 'ags', 'ags.`id_attribute` = a.`id_attribute`');
        $sql->leftJoin('attribute_lang', 'al', 'al.`id_attribute` = a.`id_attribute`');
        $sql->where('al.`id_lang` = ' . (int) $this->context->language->id);
        $sql->where('a.`id_attribute_group` = ' . (int) $id);
        $sql->orderBy('a.`position` DESC');

        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);

        return $result;
    }

    public function getOptions($data) {
        $sort = 'al.`name`';
        if ($data['sort'] == 'id') {
            $sort = 'a.`id_attribute_group`';
        }

        $sql = new DbQuery();
        $sql->select('*');
        $sql->from('attribute_group', 'a');
        $sql->leftJoin('attribute_group_shop', 'ags', 'ags.`id_attribute_group` = a.`id_attribute_group`');
        $sql->leftJoin('attribute_group_lang', 'al', 'al.`id_attribute_group` = a.`id_attribute_group`');
        $sql->where('al.`id_lang` = ' . (int) $this->context->language->id);
        if (!empty($data['filter_name'])) {
            $sql->where("al.`name` LIKE '%" . pSQL($data['filter_name']). "%'");
        }

        $sql->orderBy($sort . ' ' . $data['order']);
        if (!empty($data['limit']) && $data['limit'] != -1) {
            $sql->limit($data['limit'], $data['start']);
        }

        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);

        return $result;
    }

    public function getOption($id) {
        $sql = new DbQuery();
        $sql->select('*');
        $sql->from('attribute_group', 'a');
        $sql->leftJoin('attribute_group_shop', 'ags', 'ags.`id_attribute_group` = a.`id_attribute_group`');
        $sql->leftJoin('attribute_group_lang', 'al', 'al.`id_attribute_group` = a.`id_attribute_group`');
        $sql->where('al.`id_lang` = ' . (int) $this->context->language->id);
        $sql->where('a.`id_attribute_group` = \''.$id.'\'');

        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);

        return !empty($result) ? $result[0]: null;
    }

    public function getTotalOptions($data) {
        $sql = new DbQuery();
        $sql->select('count(*)');
        $sql->from('attribute_group', 'a');
        $sql->leftJoin('attribute_group_shop', 'ags', 'ags.`id_attribute_group` = a.`id_attribute_group`');
        $sql->leftJoin('attribute_group_lang', 'al', 'al.`id_attribute_group` = a.`id_attribute_group`');
        $sql->where('al.`id_lang` = ' . (int) $this->context->language->id);

        if (!empty($data['filter_name'])) {
            $sql->where("al.`name` LIKE '%" . pSQL($data['filter_name']). "%'");
        }


        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql);
        return $result['count(*)'];
    }
}