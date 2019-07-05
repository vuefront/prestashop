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

class ModelStoreCurrency extends Model
{
    public function getCurrencies()
    {
        $sql = new DbQuery();
        $sql->select('*');
        $sql->from('currency', 'c');
        $sql->leftJoin('currency_shop', 'cs', 'cs.`id_currency` = c.`id_currency`');

        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);

        return $result;
    }
}
