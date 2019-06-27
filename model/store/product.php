<?php

class ModelStoreProduct extends Model
{
    //prestashop doesn't have related products, so we pull 4 related products from the same category
    public function getProductRelated($product_id, $limit = 4)
    {
        $product = $this->getProduct($product_id);
        return $this->getProducts(array('filter_category_id' => $product->id_category_default, 'limit' => $limit, 'sort' => '', 'order' => '', 'start' => 0));
    }

    public function getProductImages($product_id)
    {
        $product = new Product($product_id, true, $this->context->language->id, $this->context->shop->id);

        $images = Db::getInstance()->ExecuteS('SELECT `id_image` FROM `' . _DB_PREFIX_ . 'image` WHERE `id_product` = ' . (int) ($product_id));
        foreach ($images as $key => $image_id) {
            $images[$key]['image'] = $this->context->link->getImageLink($product->link_rewrite, $image_id['id_image'], ImageType::getFormatedName("small"));
            $images[$key]['imageLazy'] = $this->context->link->getImageLink($product->link_rewrite, $image_id['id_image'], ImageType::getFormatedName("large"));
            $images[$key]['imageBig'] = $this->context->link->getImageLink($product->link_rewrite, $image_id['id_image'], ImageType::getFormatedName("large"));
        }

        return $images;
    }

    public function getProductOptions($product_id)
    {
        $result = Product::getAttributesInformationsByProduct($product_id);

        $attributes = array();
        if ($result) {
            foreach ($result as $item) {
                $attributes[$item['id_attribute_group']]['id'] = $item['id_attribute_group'];
                $attributes[$item['id_attribute_group']]['name'] = $item['group'];
                $attributes[$item['id_attribute_group']]['type'] = 'radio';
                $attributes[$item['id_attribute_group']]['values'][] = array(
                    'id' => $item['id_attribute'],
                    'name' => $item['attribute'],
                );
            }
        }

        return $attributes;
    }

    //PrestaShop does not have attributes like OpenCart. PrestaShop Attributes are OpenCart Options. SO will just use options reduced.
    public function getProductAttributes($product_id)
    {
        $result = Product::getAttributesInformationsByProduct($product_id);

        $attributes = array();
        if ($result) {
            foreach ($result as $item) {
                $attributes[$item['id_attribute_group']]['name'] = $item['group'];
                $attributes[$item['id_attribute_group']]['options'][] = $item['attribute'];
            }
        }

        return $attributes;
    }

    public function getProduct($product_id)
    {
        $product = new Product($product_id, true, $this->context->language->id, $this->context->shop->id);
        return $product;
    }

    public function getProducts($data = array())
    {
        $sort = 'p.`id_product`';
        if ($data['sort'] == 'sort_order') {
            $sort = 'p.`id_product`';
        }

        if ($data['sort'] == 'model') {
            $sort = 'p.`reference`';
        }

        if ($data['sort'] == 'quantity') {
            $sort = 'p.`quantity`';
        }

        if ($data['sort'] == 'date_added') {
            $sort = 'p.`date_add`';
        }

        if ($data['sort'] == 'name') {
            $sort = 'pl.`name`';
        }

        $sql = new DbQuery();
        $sql->select('*');
        $sql->from('product', 'p');
        $sql->leftJoin('product_shop', 'ps', 'ps.`id_product` = p.`id_product`');
        $sql->leftJoin('product_lang', 'pl', 'pl.`id_product` = p.`id_product`');
        $sql->where('p.`active` = 1');
        $sql->where('pl.`id_lang` = ' . (int) $this->context->language->id);

        if (!empty($data['filter_category_id']) && $data['filter_category_id'] > 0) {
            $sql->where('p.`id_category_default` = ' . (int) $data['filter_category_id']);
        }

        if (!empty($data['filter_ids'])) {
            $sql->where('p.`id_product` IN ' . "('" . implode("','", $data['filter_ids']) . "')");
        }

        if (!empty($data['filter_special'])) {
            $sql->where('p.`on_sale` = 1');
        }

        if (!empty($data['filter_search'])) {
            $sql->where("pl.`name` = '%" . $data['filter_search'] . "%' OR pl.description = '%" . $data['filter_search'] . "%' OR pl.description_short = '%" . $data['filter_search'] . "%'");
        }

        $sql->orderBy($sort . ' ' . $data['order']);
        if (!empty($data['limit']) && $data['limit'] != -1) {
            $sql->limit($data['limit'], $data['start']);
        }

        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);

        return $result;
    }

    public function getTotalProducts($data = array())
    {
        $sql = new DbQuery();
        $sql->select('count(*)');
        $sql->from('product', 'p');
        $sql->leftJoin('product_shop', 'ps', 'ps.`id_product` = p.`id_product`');
        $sql->leftJoin('product_lang', 'pl', 'pl.`id_product` = p.`id_product`');
        $sql->where('p.`active` = 1');
        $sql->where('pl.`id_lang` = ' . (int) $this->context->language->id);

        if (!empty($data['filter_category_id']) && $data['filter_category_id'] > 0) {
            $sql->where('p.`id_category_default` = ' . (int) $data['filter_category_id']);
        }
        if (!empty($data['filter_product_ids'])) {
            $sql->where('p.`id_product` IN ' . "('" . implode("','", $data['filter_product_ids']) . "')");
        }

        if (!empty($data['filter_special'])) {
            $sql->where('p.`on_sale` = 1');
        }

        if (!empty($data['filter_name'])) {
            $sql->where("pl.`name` = '%" . $data['filter_name'] . "%'");
        }

        if (!empty($data['filter_search'])) {
            $sql->where("pl.`name` = '%" . $data['filter_search'] . "%' OR pl.description = '%" . $data['filter_search'] . "%' OR pl.description_short = '%" . $data['filter_search'] . "%'");
        }

        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql);
        return $result['count(*)'];
    }
}
