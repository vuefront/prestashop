
<?php

class PageVFModel extends VFModel
{
    public function getPage($page_id)
    {

        $page = new CMS($page_id, $this->context->language->id, $this->context->shop->id);

        return array(
            'id' => $page->id,
            'title' => $page->meta_title,
            'description' => html_entity_decode($page->content, ENT_QUOTES, 'UTF-8'),
            'sort_order' => (int) $page->position,
        );
    }

    public function getPages($filter_data)
    {

        $sort = '';
        if ($filter_data['sort'] == 'sort_order') {
            $sort = 'c.`id_cms`, c.`position`';
        }

        if ($filter_data['sort'] == 'title') {
            $sort = 'cl.`meta_title`';
        }

        if ($filter_data['sort'] == 'name') {
            $sort = 'cl.`meta_title`';
        }

        $sql = new DbQuery();
        $sql->select('*');
        $sql->from('cms', 'c');
        $sql->leftJoin('cms_lang', 'cl', 'cl.`id_cms` = c.`id_cms`');
        $sql->where('c.`active` = 1');
        $sql->where('cl.`id_lang` = ' . (int) $this->context->language->id);

        if (!empty($filter_data['filter_title']) && !empty($filter_data['filter_description'])) {
            $sql->where("cl.`meta_title` = '%" . $filter_data['filter_title'] . "%' OR cl.content = '%" . $filter_data['filter_description'] . "%' OR cl.meta_description = '%" . $filter_data['filter_description'] . "%'");
        }

        $sql->orderBy($sort . ' ' . $filter_data['order']);
        $sql->limit($filter_data['limit'], $filter_data['start']);

        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
        $pages = array();
        if ($result) {
            foreach ($result as $item) {
                $pages[] = $this->getPage($item['id_cms']);
            }
        }

        return $pages;

    }

    public function getTotalPages($filter_data)
    {
        $sql = new DbQuery();
        $sql->select('count(*)');
        $sql->from('cms', 'c');
        $sql->leftJoin('cms_lang', 'cl', 'cl.`id_cms` = c.`id_cms`');
        $sql->where('c.`active` = 1');
        $sql->where('cl.`id_lang` = ' . (int) $this->context->language->id);

        if (!empty($filter_data['filter_title']) && !empty($filter_data['filter_description'])) {
            $sql->where("cl.`meta_title` = '%" . $filter_data['filter_title'] . "%' OR cl.content = '%" . $filter_data['filter_description'] . "%' OR cl.meta_description = '%" . $filter_data['filter_description'] . "%'");
        }

        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql);
        return $result['count(*)'];
    }

}
