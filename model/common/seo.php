<?php

class ModelCommonSeo extends Model {
    public function addUrl($url, $type, $id) {
        $sql = new DbQuery();
        $sql->select('*');
        $sql->from('vuefront_url', 'v');
        $sql->where('url LIKE \''.$url.'\'');

        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
        if (!$result) {
            Db::getInstance(_PS_USE_SQL_SLAVE_)->execute('INSERT INTO `' . _DB_PREFIX_ . 'vuefront_url` SET url = \''.$url.'\',
            id = \''.$id.'\',
            type = \''.$type.'\'');
        }
    }

    public function searchKeyword($url) {
        $sql = new DbQuery();
        $sql->select('*');
        $sql->from('vuefront_url', 'v');
        $sql->where('url LIKE \''.$url.'\'');

        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
        if (!$result) {
            return array(
                'id' => '',
                'type' => '',
                'url' => $url
            );
        }

        return array(
            'id' => $result[0]['id'],
            'type' => $result[0]['type'],
            'url' => $url
        );
    }
}