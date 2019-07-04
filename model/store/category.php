<?php
use PrestaShop\PrestaShop\Adapter\Image\ImageRetriever;

class ModelStoreCategory extends Model
{
    public function getCategory($category_id)
    {
        $category = new Category((int) $category_id, (int) $this->context->language->id, 1);
        if (_PS_VERSION_ >= '1.7.0.0') {
            $retriever = new ImageRetriever(
                $this->context->link
            );
            $image = $retriever->getImage(
                $category,
                $category->id_image
            );
            $thumb = $image['large']['url'];
            $thumbLazy = $image['small']['url'];
        } else {
            $thumb = $this->context->link->getCatImageLink($category->link_rewrite, $category->id_image);
            $thumbLazy = $this->context->link->getCatImageLink($category->link_rewrite, $category->id_image);
        }

        return array(
            'id' => $category->id,
            'name' => $category->name,
            'description' => html_entity_decode($category->description, ENT_QUOTES, 'UTF-8'),
            'parent_id' => $category->id_parent,
            'image' => $thumb,
            'imageLazy' => $thumbLazy,
            'keyword' => $category->link_rewrite
        );
    }

    public function getCategories($data = array())
    {
        $parent_id = false;
        if (isset($data['filter_parent_id']) && $data['filter_parent_id'] !== -1) {
            if ($data['filter_parent_id'] == 0) {
                $parent_id = 2;
            } else {
                $parent_id = $data['filter_parent_id'];
            }
        }

        $sort = 'c.`id_parent`';
        if ($data['sort'] == 'sort_order') {
            $sort = 'c.`id_parent`, c.`position`';
        }
        if ($data['sort'] == 'name') {
            $sort = 'cl.`name`';
        }

        $sql = new DbQuery();
        $sql->select('*');
        $sql->from('category', 'c');
        $sql->leftJoin('category_shop', 'cs', 'cs.`id_category` = c.`id_category`');
        $sql->leftJoin('category_lang', 'cl', 'cl.`id_category` = c.`id_category`');
        $sql->where('c.`active` = 1');
        $sql->where('cl.`id_lang` = ' . (int) $this->context->language->id);

        if ($parent_id) {
            $sql->where('c.`id_parent` = ' . $parent_id);
        }

        $sql->orderBy($sort . ' ' . $data['order']);
        if (!empty($data['limit']) && $data['limit'] != -1) {
            $sql->limit($data['limit'], $data['start']);
        }

        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);

        return $result;
    }

    public function getTotalCategories($data = array())
    {
        $parent_id = false;
        if (isset($data['filter_parent_id']) && $data['filter_parent_id'] !== -1) {
            if ($data['filter_parent_id'] == 0) {
                $parent_id = 2;
            } else {
                $parent_id = $data['filter_parent_id'];
            }
        }

        $language_id = $this->context->language->id;

        $sql = new DbQuery();
        $sql->select('count(*)');
        $sql->from('category', 'c');
        $sql->leftJoin('category_shop', 'cs', 'cs.`id_category` = c.`id_category`');
        $sql->leftJoin('category_lang', 'cl', 'cl.`id_category` = c.`id_category`');
        $sql->where('c.`active` = 1');
        $sql->where('cl.`id_lang` = ' . (int) $language_id);

        if ($parent_id) {
            $sql->where('c.`id_parent` = ' . $parent_id);
        }

        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql);
        return $result['count(*)'];
    }
}
