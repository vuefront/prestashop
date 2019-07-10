<?php
/**
 * 2019 (c) VueFront
 *
 * MODULE VueFront
 *
 * @author    VueFront
 * @copyright Copyright (c) permanent, VueFront
 * @license   MIT
 * @version   0.1.0
 */

class ModelBlogUrl extends Model
{
    public function link($params)
    {
        $base_url_blog = 'blog';

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
            $ok_rewrite_titre = $this->prestablogFilter(Tools::link_rewrite($params['titre']));
            $param += 1;
        }
        if (isset($params['categorie']) && $params['categorie'] != '') {
            $ok_rewrite_categorie = $this->prestablogFilter(Tools::link_rewrite($params['categorie']));
            if (isset($params['start']) && isset($params['p']) && $params['start'] != '' && $params['p'] != '') {
                $ok_rewrite_categorie .=  '-';
            } else {
                $ok_rewrite_categorie .=  '';
            }
            $param += 1;
        }
        if (isset($params['seo']) && $params['seo'] != '') {
            $ok_rewrite_titre = $this->prestablogFilter(Tools::link_rewrite($params['seo']));
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

    public function prestablogFilter($retourne)
    {
        $search = array('/--+/');
        $replace = array('-');

        $retourne = Tools::strtolower(preg_replace($search, $replace, $retourne));

        $url_replace = array(
            '/А/' => 'A', '/а/' => 'a',
            '/Б/' => 'B', '/б/' => 'b',
            '/В/' => 'V', '/в/' => 'v',
            '/Г/' => 'G', '/г/' => 'g',
            '/Д/' => 'D', '/д/' => 'd',
            '/Е/' => 'E', '/е/' => 'e',
            '/Ж/' => 'J', '/ж/' => 'j',
            '/З/' => 'Z', '/з/' => 'z',
            '/И/' => 'I', '/и/' => 'i',
            '/Й/' => 'Y', '/й/' => 'y',
            '/К/' => 'K', '/к/' => 'k',
            '/Л/' => 'L', '/л/' => 'l',
            '/М/' => 'M', '/м/' => 'm',
            '/Н/' => 'N', '/н/' => 'n',
            '/О/' => 'O', '/о/' => 'o',
            '/П/' => 'P', '/п/' => 'p',
            '/Р/' => 'R', '/р/' => 'r',
            '/С/' => 'S', '/с/' => 's',
            '/Т/' => 'T', '/т/' => 't',
            '/У/' => 'U', '/у/' => 'u',
            '/Ф/' => 'F', '/ф/' => 'f',
            '/Х/' => 'H', '/х/' => 'h',
            '/Ц/' => 'C', '/ц/' => 'c',
            '/Ч/' => 'CH', '/ч/' => 'ch',
            '/Ш/' => 'SH', '/ш/' => 'sh',
            '/Щ/' => 'SHT', '/щ/' => 'sht',
            '/Ъ/' => 'A', '/ъ/' => 'a',
            '/Ь/' => 'X', '/ь/' => 'x',
            '/Ю/' => 'YU', '/ю/' => 'yu',
            '/Я/' => 'YA', '/я/' => 'ya',
        );

        $cyrillic_find = array_keys($url_replace);
        $cyrillic_replace = array_values($url_replace);

        $retourne = Tools::strtolower(preg_replace($cyrillic_find, $cyrillic_replace, $retourne));

        return $retourne;
    }
}
