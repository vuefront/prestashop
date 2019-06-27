<?php

class ModelCommonCountry extends Model
{
    public function getCountry($country_id)
    {
        $sql = "SELECT c.country_id, cc.name
            FROM `".$this->db->getTableName('directory_country')."` c
            left join `".$this->db->getTableName('msp_tfa_country_codes')."` cc on c.country_id = cc.code
            where c.country_id = '".$country_id."'";

        $results = $this->db->fetchOne($sql);

        return $results;
    }

    public function getCountries($data)
    {
        $sql = "SELECT c.country_id, cc.name
            FROM `".$this->db->getTableName('directory_country')."` c
            left join `".$this->db->getTableName('msp_tfa_country_codes')."` cc on c.country_id = cc.code";

        $implode = array();

        if (!empty($data['filter_name'])) {
            $implode[] = "cc.name LIKE '%".$data['filter_name']."%'";
        }

        if (count($implode) > 0) {
            $sql .= ' where ' . implode(' AND ', $implode);
        }

        $sql .= " GROUP BY c.country_id";

        $sort_data = array(
            'c.country_id',
            'cc.name'
        );

        if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
            $sql .= " ORDER BY " . $data['sort'];
        } else {
            $sql .= " ORDER BY c.country_id";
        }

        if (isset($data['order']) && ($data['order'] == 'DESC')) {
            $sql .= " DESC";
        } else {
            $sql .= " ASC";
        }

        if (isset($data['start']) || isset($data['limit'])) {
            if ($data['start'] < 0) {
                $data['start'] = 0;
            }

            if ($data['limit'] < 1) {
                $data['limit'] = 20;
            }

            $sql .= " LIMIT " . (int) $data['start'] . "," . (int) $data['limit'];
        }

        $results = $this->db->fetchAll($sql);

        return $results;
    }

    public function getTotalCountries($data)
    {
        $sql = "SELECT count(*) as total
            FROM `".$this->db->getTableName('directory_country')."` c
            left join `".$this->db->getTableName('msp_tfa_country_codes')."` cc on c.country_id = cc.code";


        $implode = array();

        if (!empty($data['filter_name'])) {
            $implode[] = "cc.name LIKE '%".$data['filter_name']."%'";
        }

        if (count($implode) > 0) {
            $sql .= ' where ' . implode(' AND ', $implode);
        }


        $results = $this->db->fetchOne($sql);

        return $results['total'];
    }
}
