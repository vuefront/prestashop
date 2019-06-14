<?php

class ProductVFModel extends VFModel
{
    public function getProduct($product_id)
    {
        $product = new Product($product_id, true, $this->context->language->id, $this->context->shop->id);

        $price = Product::convertAndFormatPrice($product->getPriceWithoutReduct(true, null));
        $special = Product::convertAndFormatPrice($product->getPrice(false, null, 6));

        $images = Product::getCover($product_id);
        $image = $this->context->link->getImageLink($product->link_rewrite, $images['id_image'], ImageType::getFormatedName("large"));
        $imageLazy = $this->context->link->getImageLink($product->link_rewrite, $images['id_image'], ImageType::getFormatedName("small"));

        $rating = 5;

        return array(
            'id' => $product->id,
            'category_id' => $product->id_category_default,
            'name' => html_entity_decode($product->name, ENT_QUOTES, 'UTF-8'),
            'description' => html_entity_decode($product->description, ENT_QUOTES, 'UTF-8'),
            'shortDescription' => html_entity_decode($product->description_short, ENT_QUOTES, 'UTF-8'),
            'price' => $price,
            'special' => $special,
            'model' => $product->reference,
            'image' => $image,
            'imageLazy' => $imageLazy,
            'quantity' => $product->quantity,
            'rating' => (float) $rating,
        );

    }

    public function getProducts($filter_data)
    {
        $sort = 'p.`id_product`';
        if ($filter_data['sort'] == 'sort_order') {
            $sort = 'p.`id_product`';
        }

        if ($filter_data['sort'] == 'model') {
            $sort = 'p.`reference`';
        }

        if ($filter_data['sort'] == 'quantity') {
            $sort = 'p.`quantity`';
        }

        if ($filter_data['sort'] == 'date_added') {
            $sort = 'p.`date_add`';
        }

        if ($filter_data['sort'] == 'name') {
            $sort = 'pl.`name`';
        }

        $sql = new DbQuery();
        $sql->select('*');
        $sql->from('product', 'p');
        $sql->leftJoin('product_shop', 'ps', 'ps.`id_product` = p.`id_product`');
        $sql->leftJoin('product_lang', 'pl', 'pl.`id_product` = p.`id_product`');
        $sql->where('p.`active` = 1');
        $sql->where('pl.`id_lang` = ' . (int) $this->context->language->id);

        if (!empty($filter_data['filter_category_id']) && $filter_data['filter_category_id'] > 0) {
            $sql->where('p.`id_category_default` = ' . (int) $filter_data['filter_category_id']);
        }

        if (!empty($filter_data['filter_product_ids'])) {
            $sql->where('p.`id_product` IN ' . "('" . implode("','", $filter_data['filter_product_ids']) . "')");
        }

        if (!empty($filter_data['filter_special'])) {
            $sql->where('p.`on_sale` = 1');
        }

        if (!empty($filter_data['filter_description']) && !empty($filter_data['filter_name'])) {
            $sql->where("pl.`name` = '%" . $filter_data['filter_name'] . "%' OR pl.description = '%" . $filter_data['filter_description'] . "%' OR pl.description_short = '%" . $filter_data['filter_description'] . "%'");
        }

        //tags are not yet implemented
        $sql->orderBy($sort . ' ' . $filter_data['order']);
        $sql->limit($filter_data['limit'], $filter_data['start']);

        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
        $products = array();
        if ($result) {
            foreach ($result as $item) {
                $products[] = $this->getProduct($item['id_product']);
            }
        }

        return $products;
    }

    public function getTotalProducts($filter_data)
    {
        $sql = new DbQuery();
        $sql->select('count(*)');
        $sql->from('product', 'p');
        $sql->leftJoin('product_shop', 'ps', 'ps.`id_product` = p.`id_product`');
        $sql->leftJoin('product_lang', 'pl', 'pl.`id_product` = p.`id_product`');
        $sql->where('p.`active` = 1');
        $sql->where('pl.`id_lang` = ' . (int) $this->context->language->id);

        if (!empty($filter_data['filter_category_id']) && $filter_data['filter_category_id'] > 0) {
            $sql->where('p.`id_category_default` = ' . (int) $filter_data['filter_category_id']);
        }
        if (!empty($filter_data['filter_product_ids'])) {
            $sql->where('p.`id_product` IN ' . "('" . implode("','", $filter_data['filter_product_ids']) . "')");
        }

        if (!empty($filter_data['filter_special'])) {
            $sql->where('p.`on_sale` = 1');
        }

        if (!empty($filter_data['filter_name'])) {
            $sql->where("pl.`name` = '%" . $filter_data['filter_name'] . "%'");
        }

        if (!empty($filter_data['filter_description'])) {
            $sql->where("pl.description = '%" . $filter_data['filter_description'] . "%'");
            $sql->where("pl.description_short = '%" . $filter_data['filter_description'] . "%'");
        }

        //tags are not yet implemented

        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql);
        return $result['count(*)'];
    }

    //prestashop doesn't have related products, so we pull 4 related products from the same category
    public function getProductRelated($product_id)
    {
        $product = $this->getProduct($product_id);
        return $this->getProducts(array('filter_category_id' => $product['category_id'], 'limit' => 4, 'sort' => '', 'order' => '', 'start' => 0));
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

    public function getProductOptions($product_id)
    {
        $result = Product::getAttributesInformationsByProduct($product_id);

        $attributes = array();
        if ($result) {
            foreach ($result as $item) {
                $attributes[$item['id_attribute_group']]['id'] = $item['id_attribute_group'];
                $attributes[$item['id_attribute_group']]['name'] = $item['group'];
                $attributes[$item['id_attribute_group']]['values'][] = array(
                    'id' => $item['id_attribute'],
                    'name' => $item['attribute'],
                );
            }
        }

        return $attributes;
    }

    public function getProductImages($product_id)
    {
        $product = new Product($product_id, true, $this->context->language->id, $this->context->shop->id);

        $images = Db::getInstance()->ExecuteS('SELECT `id_image` FROM `' . _DB_PREFIX_ . 'image` WHERE `id_product` = ' . (int) ($product_id));
        foreach ($images as $key => $image_id) {
            $images[$key]['image'] = $this->context->link->getImageLink($product->link_rewrite, $image_id['id_image'], ImageType::getFormatedName("small"));
            $images[$key]['imageLazy'] = $this->context->link->getImageLink($product->link_rewrite, $image_id['id_image'], ImageType::getFormatedName("large"));
        }

        return $images;
    }
}
