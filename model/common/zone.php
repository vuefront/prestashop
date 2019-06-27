<?php

class ModelCommonZone extends Model
{
    public function getZone($zone_id)
    {
        $sql = "SELECT region_id as zone_id, country_id, code, default_name as name
            FROM `".$this->db->getTableName('directory_country_region')."`
            where region_id = '".$zone_id."'";

        $results = $this->db->fetchOne($sql);

        return $results;
    }

    public function getZones($data)
    {
        $sql = "SELECT region_id as zone_id, country_id, code, default_name as name
            FROM `".$this->db->getTableName('directory_country_region')."`";

        $implode = array();

        if(!empty($data['filter_name'])) {
            $implode[] = "default_name LIKE '%".$data['filter_name']."%'";
        }

        if(!empty($data['filter_country_id'])) {
            $implode[] = "country_id LIKE '%".$data['filter_country_id']."%'";
        }

        if (count($implode) > 0) {
            $sql .= ' where ' . implode(' AND ', $implode);
        }

        $sql .= " GROUP BY region_id";

        $sort_data = array(
            'zone_id',
            'name'
        );

        if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
            $sql .= " ORDER BY " . $data['sort'];
        } else {
            $sql .= " ORDER BY zone_id";
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

    public function getTotalZones($data)
    {
        $sql = "SELECT count(*) as total
            FROM `".$this->db->getTableName('directory_country_region')."`";

        $implode = array();

        if (!empty($data['filter_name'])) {
            $implode[] = "default_name LIKE '%".$data['filter_name']."%'";
        }
        
        if (!empty($data['filter_country_id'])) {
            $implode[] = "country_id LIKE '%".$data['filter_country_id']."%'";
        }


        if (count($implode) > 0) {
            $sql .= ' where ' . implode(' AND ', $implode);
        }


        $results = $this->db->fetchOne($sql);

        return $results['total'];
    }
}
