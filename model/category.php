<?php
use PrestaShop\PrestaShop\Adapter\Image\ImageRetriever;

class CategoryVFModel extends VFModel
{
    public function getCategory($category_id)
    {
        $category = new Category((int) $category_id, (int) $this->context->language->id, 1);

        $retriever = new ImageRetriever(
            $this->context->link
        );

        $category->image = $retriever->getImage(
            $category,
            $category->id_image);

        return array(
            'id' => $category->id,
            'name' => $category->name,
            'description' => html_entity_decode($category->description, ENT_QUOTES, 'UTF-8'),
            'parent_id' => $category->id_parent,
            'image' => $category->image['large']['url'],
            'imageLazy' => $category->image['small']['url'],
        );
    }

    public function getCategories($filter_data)
    {
        $parent_id = 2;
        if ($filter_data['parent'] !== -1) {
            if ($filter_data['parent'] == 0) {
                $parent_id = 2;
            } else {
                $parent_id = $filter_data['parent'];
            }
        }

        $sort = 'c.`id_parent`';
        if ($filter_data['sort'] == 'sort_order') {
            $sort = 'c.`id_parent`, c.`position`';
        }
        if ($filter_data['sort'] == 'name') {
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

        $sql->orderBy($sort . ' ' . $filter_data['order']);
        if ($filter_data['limit'] != -1) {
            $sql->limit($filter_data['limit'], $filter_data['start']);
        }

        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);

        $categories = array();
        if ($result) {
            foreach ($result as $item) {
                $categories[] = $this->getCategory($item['id_category']);
            }
        }
        return $categories;
    }

    public function getTotalCategories($filter_data)
    {
        $parent_id = false;
        if ($filter_data['parent'] !== -1) {
            $parent_id = $filter_data['parent'];
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
