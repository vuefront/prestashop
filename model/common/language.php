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

class ModelCommonLanguage extends Model
{
    public function getLanguages()
    {
        $sql = new DbQuery();
        $sql->select('*');
        $sql->from('lang', 'l');
        $sql->leftJoin('lang_shop', 'ls', 'ls.`id_lang` = l.`id_lang`');

        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);

        return $result;
    }

    public function getLanguageByLocale($locale)
    {
        $sql = new DbQuery();
        $sql->select('*');
        $sql->from('lang', 'l');
        $sql->leftJoin('lang_shop', 'ls', 'ls.`id_lang` = l.`id_lang`');
        if (_PS_VERSION_ > '1.7.0.0') {
            $sql->where("LOWER(l.locale) LIKE '%".pSQL($locale)."%'");
        } else {
            $sql->where("LOWER(l.language_code) LIKE '%".pSQL($locale)."%'");
        }
        

        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
        return !empty($result) ? $result[0] : false;
    }
}
