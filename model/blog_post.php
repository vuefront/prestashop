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

include_once _PS_MODULE_DIR_ . 'prestablog/class/news.class.php';

class Blog_PostVFModel extends VFModel
{
    public function getPost($post_id)
    {
        $post = new NewsClass((int) $post_id, (int) $this->context->language->id, $this->context->shop->id);

        return array(
            'id' => $post->id,
            'title' => $post->title,
            'description' => html_entity_decode($post->content, ENT_QUOTES, 'UTF-8'),
            'shortDescription' => strip_tags(html_entity_decode($post->paragraph, ENT_QUOTES, 'UTF-8')),
            'image' => $this->getImage($post->id),
            'imageLazy' => $this->getImageLazy($post->id),
        );

    }

    public function getImage($post_id)
    {
        $uri = __PS_BASE_URI__ . 'modules/prestablog/views/img/' . PrestaBlog::getT() . '/up-img/' . $post_id . '.jpg';
        return $this->context->link->protocol_content . Tools::getMediaServer($uri) . $uri;
    }

    public function getImageLazy($post_id)
    {
        $uri = __PS_BASE_URI__ . 'modules/prestablog/views/img/' . PrestaBlog::getT() . '/up-img/thumb_' . $post_id . '.jpg';
        return $this->context->link->protocol_content . Tools::getMediaServer($uri) . $uri;
    }

    public function getPosts($filter_data)
    {
        $sort = 'pn.`id_prestablog_news';
        if ($filter_data['sort'] == 'sort_order') {
            $sort = 'pn.`id_prestablog_news`';
        }

        if ($filter_data['sort'] == 'date_added') {
            $sort = 'pn.`date`';
        }

        if ($filter_data['sort'] == 'name') {
            $sort = 'pnl.`title`';
        }

        $sql = new DbQuery();
        $sql->select('*');
        $sql->from('prestablog_news', 'pn');
        $sql->leftJoin('prestablog_news_lang', 'pnl', 'pnl.`id_prestablog_news` = pn.`id_prestablog_news`');
        if (!empty($filter_data['filter_category_id']) && $filter_data['filter_category_id'] > 0) {
            $sql->leftJoin('prestablog_correspondancecategorie', 'pcc', 'pcc.`news` = pn.`id_prestablog_news`');
        }
        $sql->where('pn.`actif` = 1');
        $sql->where('pnl.`id_lang` = ' . (int) $this->context->language->id);

        if (!empty($filter_data['filter_category_id']) && $filter_data['filter_category_id'] > 0) {
            $sql->where('pcc.`categorie` = ' . (int) $filter_data['filter_category_id']);
        }

        if (!empty($filter_data['filter_post_ids'])) {
            $sql->where('p.`id_prestablog_news` IN ' . "('" . impload("','", expload(",", preg_replace('/\s+/', ' ', $filter_data['filter_post_ids']))) . "')");
        }

        if (!empty($filter_data['filter_description']) && !empty($filter_data['filter_name'])) {
            $sql->where("pnl.`title` = '%" . $filter_data['filter_name'] . "%' OR pnl.content = '%" . $filter_data['filter_description'] . "%' OR pnl.paragraph = '%" . $filter_data['filter_description'] . "%'");
        }

        $sql->orderBy($sort . ' ' . $filter_data['order']);
        $sql->limit($filter_data['limit'], $filter_data['start']);

        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
        $posts = array();
        if ($result) {
            foreach ($result as $item) {
                $posts[] = $this->getPost($item['id_prestablog_news']);
            }
        }

        return $posts;
    }

    public function getTotalPosts($filter_data)
    {
        $sql = new DbQuery();
        $sql->select('count(*)');
        $sql->from('prestablog_news', 'pn');
        $sql->leftJoin('prestablog_news_lang', 'pnl', 'pnl.`id_prestablog_news` = pn.`id_prestablog_news`');
        if (!empty($filter_data['filter_category_id']) && $filter_data['filter_category_id'] > 0) {
            $sql->leftJoin('prestablog_correspondancecategorie', 'pcc', 'pcc.`news` = pn.`id_prestablog_news`');
        }
        $sql->where('pn.`actif` = 1');
        $sql->where('pnl.`id_lang` = ' . (int) $this->context->language->id);

        if (!empty($filter_data['filter_category_id']) && $filter_data['filter_category_id'] > 0) {
            $sql->where('pcc.`categorie` = ' . (int) $filter_data['filter_category_id']);
        }

        if (!empty($filter_data['filter_post_ids'])) {
            $sql->where('p.`id_prestablog_news` IN ' . "('" . impload("','", expload(",", preg_replace('/\s+/', ' ', $filter_data['filter_post_ids']))) . "')");
        }

        if (!empty($filter_data['filter_description']) && !empty($filter_data['filter_name'])) {
            $sql->where("pnl.`title` = '%" . $filter_data['filter_name'] . "%' OR pnl.content = '%" . $filter_data['filter_description'] . "%' OR pnl.paragraph = '%" . $filter_data['filter_description'] . "%'");
        }

        //tags are not yet implemented

        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql);
        return $result['count(*)'];
    }
}
