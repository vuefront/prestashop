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

class ResolverCommonLanguage extends Resolver
{
    private $codename = "d_vuefront";

    public function get()
    {
        $this->load->model('common/language');
        $results = $this->model_common_language->getLanguages();

        $languages = array();
        foreach ($results as $value) {
            if (_PS_VERSION_ > '1.7.0.0') {
                $locale = $value['locale'];
            } else {
                $locale = $value['language_code'];
            }
            $code = Tools::strtolower($locale);
            if ($code == 'en-us') {
                $code = 'en-gb';
            }
            $languages[] = array(
                'name' => $value['name'],
                'code' => $code,
                'image'=> '',
                'active' => $value['id_lang'] == $this->context->cookie->id_lang
            );
        }

        return $languages;
    }

    public function edit($args)
    {
        $this->load->model('common/language');

        $code = $args['code'];

        if ($code == 'en-gb') {
            $code = 'en-us';
        }

         
        $lang = $this->model_common_language->getLanguageByLocale($code);

        $this->context->cookie->id_lang = $lang['id_lang'];

        return $this->get();
    }
}
