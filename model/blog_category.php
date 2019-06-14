<?php
/*
 * supported: PRESTABLOG
 *
 * Thanks to the team from PrestaBlog for providing the codebase
 * and assisting with the integration of VueFrongt with PrestaBlog.
 *
 * Since prestaShop does not have a blog by default, we have implemented
 * support for one of the most popular Blog modules - PrestaBlog
 *
 * If you have another blog, you can use this model to modify it to
 * add support for your current blog
 *
 * You can always contact our support via https://vuefront.com/support
 * for assitance in integrating your blog module with our CMS Connect App.
 */

include_once _PS_MODULE_DIR_ . 'prestablog/class/categories.class.php';

class Blog_CategoryVFModel extends VFModel
{
    public function getCategory($category_id)
    {
        $category = new CategoriesClass((int) $category_id, (int) $this->context->language->id, 1);

        return array(
            'id' => $category->id,
            'name' => $category->title,
            'description' => html_entity_decode($category->description, ENT_QUOTES, 'UTF-8'),
            'parent_id' => $category->parent,
            'image' => $this->getImage($category->id),
            'imageLazy' => $this->getImageLazy($category->id),
        );
    }

    public function getImage($category_id)
    {
        $uri = __PS_BASE_URI__ . 'modules/prestablog/views/img/' . PrestaBlog::getT() . '/up-img/c/' . $category_id . '.jpg';
        return $this->context->link->protocol_content . Tools::getMediaServer($uri) . $uri;
    }

    public function getImageLazy($category_id)
    {
        $uri = __PS_BASE_URI__ . 'modules/prestablog/views/img/' . PrestaBlog::getT() . '/up-img/c/thumb_' . $category_id . '.jpg';
        return $this->context->link->protocol_content . Tools::getMediaServer($uri) . $uri;
    }

    public function getCategories($filter_data)
    {
        $parent_id = false;
        if ($filter_data['parent'] !== -1) {
            $parent_id = $filter_data['parent'];
        }

        $sort = 'c.`id_parent`';
        if ($filter_data['sort'] == 'sort_order') {
            $sort = 'c.`id_parent`, c.`position`';
        }
        if ($filter_data['sort'] == 'name') {
            $sort = 'cl.`name`';
        }

        $language_id = $this->context->language->id;
        $order = $filter_data['order'];
        $start = $filter_data['start'];
        $limit = $filter_data['limit'];

        $sql = new DbQuery();
        $sql->select('*');
        $sql->from('prestablog_categorie', 'c');
        $sql->leftJoin('prestablog_categorie_lang', 'cl', 'cl.`id_prestablog_categorie` = c.`id_prestablog_categorie`');
        $sql->where('c.`actif` = 1');
        $sql->where('cl.`id_lang` = ' . (int) $language_id);

        if ($parent_id) {
            $sql->where('c.`parent` = ' . $parent_id);
        }

        $sql->orderBy($sort . ' ' . $order);
        $sql->limit($limit, $start);

        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
        $categories = array();
        if ($result) {
            foreach ($result as $item) {
                $categories[] = $this->getCategory($item['id_prestablog_categorie']);
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
        $sql->from('prestablog_categorie', 'c');
        $sql->leftJoin('prestablog_categorie_lang', 'cl', 'cl.`id_prestablog_categorie` = c.`id_prestablog_categorie`');
        $sql->where('c.`actif` = 1');
        $sql->where('cl.`id_lang` = ' . (int) $language_id);

        if ($parent_id) {
            $sql->where('c.`id_parent` = ' . $parent_id);
        }

        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql);
        return $result['count(*)'];
    }
}
