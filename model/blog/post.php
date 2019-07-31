<?php
/**
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
 *
 * 2019 (c) VueFront
 *
 * MODULE VueFront
 *
 * @author    VueFront
 * @copyright Copyright (c) permanent, VueFront
 * @license   MIT
 *
 * @version   0.1.0
 */
include_once _PS_MODULE_DIR_ . 'prestablog/class/news.class.php';

class ModelBlogPost extends Model
{
    public function getPost($post_id)
    {
        $post = new NewsClass((int) $post_id, (int) $this->context->language->id, $this->context->shop->id);

        $this->load->model('blog/url');

        $url = $this->model_blog_url->link(array(
            'id' => $post->id,
            'seo' => $post->link_rewrite,
            'titre' => $post->title,
        ));

        return array(
            'id' => $post->id,
            'title' => $post->title,
            'datePublished' => $post->date,
            'description' => html_entity_decode($post->content, ENT_QUOTES, 'UTF-8'),
            'shortDescription' => strip_tags(html_entity_decode($post->paragraph, ENT_QUOTES, 'UTF-8')),
            'image' => $this->getImage($post->id),
            'imageLazy' => $this->getImageLazy($post->id),
            'keyword' => $url,
            'meta' => array(
                'title' => $post->meta_title,
                'description' => $post->meta_description,
                'keyword' => $post->meta_keywords,
            ),
        );
    }

    public function getImage($post_id)
    {
        if (file_exists(_PS_ROOT_DIR_ . '/modules/prestablog/views/img/' . Configuration::get('prestablog_theme') .
             '/up-img/' . $post_id . '.jpg')) {
            $uri = __PS_BASE_URI__ . 'modules/prestablog/views/img/' . Configuration::get('prestablog_theme') .
             '/up-img/' . $post_id . '.jpg';

            return $this->context->link->protocol_content . Tools::getMediaServer($uri) . $uri;
        } else {
            return '';
        }
    }

    public function getImageLazy($post_id)
    {
        if (file_exists(_PS_ROOT_DIR_ . '/modules/prestablog/views/img/' . Configuration::get('prestablog_theme') .
             '/up-img/thumb_' . $post_id . '.jpg')) {
            $uri = __PS_BASE_URI__ . 'modules/prestablog/views/img/' . Configuration::get('prestablog_theme') .
             '/up-img/thumb_' . $post_id . '.jpg';

            return $this->context->link->protocol_content . Tools::getMediaServer($uri) . $uri;
        } else {
            return '';
        }
    }

    public function getPosts($data = array())
    {
        $sort = 'pn.`id_prestablog_news`';
        if ($data['sort'] == 'sort_order') {
            $sort = 'pn.`id_prestablog_news`';
        }

        if ($data['sort'] == 'date_added') {
            $sort = 'pn.`date`';
        }

        if ($data['sort'] == 'name') {
            $sort = 'pnl.`title`';
        }

        $sql = new DbQuery();
        $sql->select('*');
        $sql->from('prestablog_news', 'pn');
        $sql->leftJoin('prestablog_news_lang', 'pnl', 'pnl.`id_prestablog_news` = pn.`id_prestablog_news`');
        if (!empty($data['filter_category_id']) && $data['filter_category_id'] > 0) {
            $sql->leftJoin('prestablog_correspondancecategorie', 'pcc', 'pcc.`news` = pn.`id_prestablog_news`');
        }
        $sql->where('pn.`actif` = 1');
        $sql->where('pnl.`id_lang` = ' . (int) $this->context->language->id);

        if (!empty($data['filter_category_id']) && $data['filter_category_id'] > 0) {
            $sql->where('pcc.`categorie` = ' . (int) $data['filter_category_id']);
        }

        if (!empty($data['filter_post_ids'])) {
            $sql->where('p.`id_prestablog_news` IN ' . "('" .
            pSQL(implode("','", explode(',', preg_replace('/\s+/', ' ', $data['filter_post_ids'])))) . "')");
        }

        if (!empty($data['filter_description']) && !empty($data['filter_name'])) {
            $sql->where("pnl.`title` = '%" . pSQL($data['filter_name']) .
            "%' OR pnl.content = '%" . pSQL($data['filter_description']) .
            "%' OR pnl.paragraph = '%" . pSQL($data['filter_description']) . "%'");
        }

        $sql->orderBy($sort . ' ' . $data['order']);
        if (!empty($data['limit']) && $data['limit'] != -1) {
            $sql->limit($data['limit'], $data['start']);
        }

        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);

        return $result;
    }

    public function getTotalPosts($data = array())
    {
        $sql = new DbQuery();
        $sql->select('count(*)');
        $sql->from('prestablog_news', 'pn');
        $sql->leftJoin('prestablog_news_lang', 'pnl', 'pnl.`id_prestablog_news` = pn.`id_prestablog_news`');
        if (!empty($data['filter_category_id']) && $data['filter_category_id'] > 0) {
            $sql->leftJoin('prestablog_correspondancecategorie', 'pcc', 'pcc.`news` = pn.`id_prestablog_news`');
        }
        $sql->where('pn.`actif` = 1');
        $sql->where('pnl.`id_lang` = ' . (int) $this->context->language->id);

        if (!empty($data['filter_category_id']) && $data['filter_category_id'] > 0) {
            $sql->where('pcc.`categorie` = ' . (int) $data['filter_category_id']);
        }

        if (!empty($data['filter_post_ids'])) {
            $sql->where('p.`id_prestablog_news` IN ' . "('" .
            implode("','", explode(',', preg_replace('/\s+/', ' ', $data['filter_post_ids']))) . "')");
        }

        if (!empty($data['filter_description']) && !empty($data['filter_name'])) {
            $sql->where("pnl.`title` = '%" . pSQL($data['filter_name']) . "%' OR pnl.content = '%" .
            pSQL($data['filter_description']) . "%' OR pnl.paragraph = '%" . pSQL($data['filter_description']) . "%'");
        }

        //tags are not yet implemented

        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql);

        return $result['count(*)'];
    }

    public function getNextPost($post_id)
    {
        $sql = new DbQuery();
        $sql->select('*');
        $sql->from('prestablog_news', 'pn');
        $sql->leftJoin('prestablog_news_lang', 'pnl', 'pnl.`id_prestablog_news` = pn.`id_prestablog_news`');
        $sql->where('pn.`actif` = 1');
        $sql->where('pnl.`id_lang` = ' . (int) $this->context->language->id);
        $sql->orderBy('pn.date ASC');
        $sql->where("pn.id_prestablog_news > '" . (int) $post_id . "'");
        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql);

        return $result;
    }

    public function getPrevPost($post_id)
    {
        $sql = new DbQuery();
        $sql->select('*');
        $sql->from('prestablog_news', 'pn');
        $sql->leftJoin('prestablog_news_lang', 'pnl', 'pnl.`id_prestablog_news` = pn.`id_prestablog_news`');
        $sql->where('pn.`actif` = 1');
        $sql->where('pnl.`id_lang` = ' . (int) $this->context->language->id);
        $sql->orderBy('pn.id_prestablog_news DESC');
        $sql->where("pn.id_prestablog_news < '" . (int) $post_id . "'");

        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql);

        return $result;
    }
}
