<?php

class ModelCommonAddress extends Model
{
    public function addAddress($customer_id, $data)
    {
        $region_info = $this->load->resolver('common/zone/get', array(
                'id' => $data['zoneId']
        ));

        $region = !empty($region_info) ? $region_info['name'] : '';

        $sql = "INSERT INTO `".$this->db->getTableName('customer_address_entity')."` SET 
            parent_id = '".$customer_id."',
            city = '".$data['city']."', 
            company = '".$data['company']."', 
            country_id = '".$data['countryId']."', 
            firstname = '".$data['firstName']."', 
            lastname = '".$data['lastName']."',
            postcode = '".$data['zipcode']."', 
            region = '".$region."', 
            region_id = '".$data['zoneId']."', 
            street = '".$data['address1'].' '.$data['address2']."'";

        $this->db->query($sql);

        return $this->db->getLastId();
    }

    public function editAddress($address_id, $data)
    {
        $region_info = $this->load->resolver('common/zone/get', array(
                'id' => $data['zoneId']
        ));

        $region = !empty($region_info) ? $region_info['name'] : '';

        $sql = "UPDATE `".$this->db->getTableName('customer_address_entity')."` SET 
            city = '".$data['city']."', 
            company = '".$data['company']."', 
            country_id = '".$data['countryId']."', 
            firstname = '".$data['firstName']."', 
            lastname = '".$data['lastName']."',
            postcode = '".$data['zipcode']."', 
            region = '".$region."', 
            region_id = '".$data['zoneId']."', 
            street = '".$data['address1'].' '.$data['address2']."'
            where entity_id = '".$address_id."'";

        $this->db->query($sql);
    }

    public function getAddress($address_id)
    {
        $sql = "SELECT entity_id as address_id,city, company, country_id, firstname, lastname,postcode, region_id, street
            FROM `".$this->db->getTableName('customer_address_entity')."`
            where entity_id = '".$address_id."'";

        $results = $this->db->fetchOne($sql);

        return $results;
    }

    public function getAddresses($customer_id, $data = array())
    {
        $sql = "SELECT entity_id as address_id,city, company, country_id, firstname, lastname,postcode, region_id, street
            FROM `".$this->db->getTableName('customer_address_entity')."` where parent_id = '".$customer_id."'";

        $sort_data = array(
            'entity_id',
            'street'
        );

        if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
            $sql .= " ORDER BY " . $data['sort'];
        } else {
            $sql .= " ORDER BY entity_id";
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

    public function getTotalAddresses($customer_id, $data)
    {
        $sql = "SELECT count(*) as total
            FROM `".$this->db->getTableName('customer_address_entity')."` where parent_id = '".$customer_id."'";

        $results = $this->db->fetchOne($sql);

        return $results['total'];
    }
}
