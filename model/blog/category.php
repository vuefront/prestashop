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

        $url = $this->prestablogUrl(array(
            'c' => $category->id,
            'categorie' => $category->link_rewrite
        ));

        return array(
            'id' => $category->id,
            'name' => $category->title,
            'description' => html_entity_decode($category->description, ENT_QUOTES, 'UTF-8'),
            'parent_id' => $category->parent,
            'image' => $this->getImage($category->id),
            'imageLazy' => $this->getImageLazy($category->id),
            'keyword' => $url
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
        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('
        SELECT    cl.`title`, cl.`link_rewrite`, cc.`categorie`
        FROM `'.bqSQL(_DB_PREFIX_.'prestablog_correspondancecategorie').'` as cc
        LEFT JOIN `'.bqSQL(_DB_PREFIX_).'prestablog_categorie` as c
            ON (cc.`categorie` = c.`id_prestablog_categorie`)
        LEFT JOIN `'.bqSQL(_DB_PREFIX_).'prestablog_categorie_lang` as cl
            ON (cc.`categorie` = cl.`id_prestablog_categorie`)
        WHERE cc.`news` = '.(int)$post_id.'
            AND cl.`id_lang` = '.(int)$this->context->cookie->id_lang.'
            AND c.`actif` = 1
        ORDER BY cl.`title`');

        return $result;
    }

    public function prestablogUrl($params)
    {
        $base_url_blog = 'blog';
        //$base_url_blog = 'articles';

        $param = null;
        $ok_rewrite = '';
        $ok_rewrite_id = '';
        $ok_rewrite_do = '';
        $ok_rewrite_cat = '';
        $ok_rewrite_categorie = '';
        $ok_rewrite_page = '';
        $ok_rewrite_titre = '';
        $ok_rewrite_seo = '';
        $ok_rewrite_year = '';
        $ok_rewrite_month = '';

        $ko_rewrite = '';
        $ko_rewrite_id = '';
        $ko_rewrite_do = '';
        $ko_rewrite_cat = '';
        $ko_rewrite_page = '';
        $ko_rewrite_year = '';
        $ko_rewrite_month = '';

        if (isset($params['do']) && $params['do'] != '') {
            $ko_rewrite_do = 'do='.$params['do'];
            $ok_rewrite_do = $params['do'];
            $param += 1;
        }
        if (isset($params['id']) && $params['id'] != '') {
            $ko_rewrite_id = '&id='.$params['id'];
            $ok_rewrite_id = '-n'.$params['id'];
            $param += 1;
        }
        if (isset($params['c']) && $params['c'] != '') {
            $ko_rewrite_cat = '&c='.$params['c'];
            $ok_rewrite_cat = '-c'.$params['c'];
            $param += 1;
        }
        if (isset($params['start']) && isset($params['p']) && $params['start'] != '' && $params['p'] != '') {
            $ko_rewrite_page = '&start='.$params['start'].'&p='.$params['p'];
            $ok_rewrite_page = $params['start'].'p'.$params['p'];
            $param += 1;
        }
        if (isset($params['titre']) && $params['titre'] != '') {
            $ok_rewrite_titre = PrestaBlog::prestablogFilter(Tools::link_rewrite($params['titre']));
            $param += 1;
        }
        if (isset($params['categorie']) && $params['categorie'] != '') {
            $ok_rewrite_categorie = PrestaBlog::prestablogFilter(Tools::link_rewrite($params['categorie']));
            if (isset($params['start']) && isset($params['p']) && $params['start'] != '' && $params['p'] != '') {
                $ok_rewrite_categorie .=  '-';
            } else {
                $ok_rewrite_categorie .=  '';
            }
            $param += 1;
        }
        if (isset($params['seo']) && $params['seo'] != '') {
            $ok_rewrite_titre = PrestaBlog::prestablogFilter(Tools::link_rewrite($params['seo']));
            $param += 1;
        }
        if (isset($params['y']) && $params['y'] != '') {
            $ko_rewrite_year = '&y='.$params['y'];
            $ok_rewrite_year = 'y'.$params['y'];
            $param += 1;
        }
        if (isset($params['m']) && $params['m'] != '') {
            $ko_rewrite_month = '&m='.$params['m'];
            $ok_rewrite_month = '-m'.$params['m'];
            $param += 1;
        }
        if (isset($params['seo']) && $params['seo'] != '') {
            $ok_rewrite_seo = $params['seo'];
            $ok_rewrite_titre = '';
            $param += 1;
        }
        if (isset($params) && count($params) > 0 && !isset($params['rss'])) {
            $ok_rewrite = $base_url_blog.'/'.$ok_rewrite_do.$ok_rewrite_categorie.$ok_rewrite_page;
            $ok_rewrite .= $ok_rewrite_year.$ok_rewrite_month.$ok_rewrite_titre.$ok_rewrite_seo;
            $ok_rewrite .= $ok_rewrite_cat.$ok_rewrite_id;

            $ko_rewrite = '?fc=module&module=prestablog&controller=blog&'.ltrim(
                $ko_rewrite_do.$ko_rewrite_id.$ko_rewrite_cat.$ko_rewrite_page.$ko_rewrite_year.$ko_rewrite_month,
                '&'
            );
        } elseif (isset($params['rss'])) {
            if ($params['rss'] == 'all') {
                $ok_rewrite = 'rss';
                $ko_rewrite = '?fc=module&module=prestablog&controller=rss';
            } else {
                $ok_rewrite = 'rss/'.$params['rss'];
                $ko_rewrite = '?fc=module&module=prestablog&controller=rss&rss='.$params['rss'];
            }
        } else {
            $ok_rewrite = $base_url_blog;
            $ko_rewrite = '?fc=module&module=prestablog&controller=blog';
        }

        if (!isset($params['id_lang'])) {
            (int)$params['id_lang'] = null;
        }

        if ((int)Configuration::get('PS_REWRITING_SETTINGS') && (int)Configuration::get('prestablog_rewrite_actif')) {
            return $ok_rewrite;
        } else {
            return $ko_rewrite;
        }
    }
}
