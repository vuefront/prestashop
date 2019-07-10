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

class ModelCommonStore extends Model
{
    public function getStores()
    {
        $sql = new DbQuery();
        $sql->select('*');
        $sql->from('store', 's');
        if (_PS_VERSION_ > '1.7.0.0') {
            $sql->leftJoin('store_lang', 'sl', 'sl.`id_store` = s.`id_store`');
            $sql->where('sl.`id_lang` = ' . (int) $this->context->language->id);
        }
        $sql->where('s.`active` = 1');

        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);

        return $result;
    }
}
