<?php

class ModelCommonStore extends Model
{
    public function getStores()
    {
        $sql = new DbQuery();
        $sql->select('*');
        $sql->from('store', 's');
        if (_PS_VERSION_ > '1.7.0.0') {
            $sql->leftJoin('store_lang', 'sl', 'sl.`id_store` = s.`id_store`');
            $sql->where('cl.`id_lang` = ' . (int) $this->context->language->id);
        }
        $sql->where('s.`active` = 1');

        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);

        return $result;
    }
}
