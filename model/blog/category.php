<?php
/**
 *
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
 * 2019 (c) VueFront
 *
 * MODULE VueFront
 *
 * @author    VueFront
 * @copyright Copyright (c) permanent, VueFront
 * @license   MIT
 * @version   0.1.0
 */

include_once _PS_MODULE_DIR_ . 'prestablog/class/categories.class.php';

class ModelBlogCategory extends Model
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
            'keyword' => $category->link_rewrite
        );
    }

    public function getImage($category_id)
    {
        $uri = __PS_BASE_URI__ . 'modules/prestablog/views/img/' .
         Configuration::get('prestablog_theme') . '/up-img/c/' . $category_id . '.jpg';
        return $this->context->link->protocol_content . Tools::getMediaServer($uri) . $uri;
    }

    public function getImageLazy($category_id)
    {
        $uri = __PS_BASE_URI__ . 'modules/prestablog/views/img/' .
         Configuration::get('prestablog_theme') . '/up-img/c/thumb_' . $category_id . '.jpg';
        return $this->context->link->protocol_content . Tools::getMediaServer($uri) . $uri;
    }


    public function getCategories($data = array())
    {
        $sort = 'c.`id_prestablog_categorie`';
        if (!empty($data['sort'])) {
            if ($data['sort'] == 'sort_order') {
                $sort = 'c.`id_prestablog_categorie`, c.`position`';
            }
            if ($data['sort'] == 'name') {
                $sort = 'cl.`name`';
            }
        }

        $language_id = $this->context->language->id;
        $order = !empty($data['order'])? $data['order']: 'ASC';

        $sql = new DbQuery();
        $sql->select('*');
        $sql->from('prestablog_categorie', 'c');
        $sql->leftJoin('prestablog_categorie_lang', 'cl', 'cl.`id_prestablog_categorie` = c.`id_prestablog_categorie`');
        $sql->where('c.`actif` = 1');
        $sql->where('cl.`id_lang` = ' . (int) $language_id);

        if (isset($data['filter_parent_id'])) {
            $sql->where('c.`parent` = ' . $data['filter_parent_id']);
        }

        $sql->orderBy($sort . ' ' . $order);

        if (!empty($data['limit']) && $data['limit'] != -1) {
            $sql->limit($data['limit'], $data['start']);
        }

        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);

        return $result ? $result : array();
    }

    public function getTotalCategories($data = array())
    {
        $parent_id = false;
        if (!empty($data['filter_parent_id']) && $data['filter_parent_id'] !== -1) {
            $parent_id = $data['filter_parent_id'];
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

    public function getCategoryByPostId($post_id)
    {
        $categories = CorrespondancesCategoriesClass::getCategoriesListeName(
            (int)$post_id,
            (int)$this->context->cookie->id_lang,
            1
        );

        return $categories;
    }
}
