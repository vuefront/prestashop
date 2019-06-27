<?php

class ResolverCommonLanguage extends Resolver
{
    private $codename = "d_vuefront";

    public function get()
    {
        global $cookie;

        $this->load->model('common/language');
        $results = $this->model_common_language->getLanguages();

        $languages = array();
        foreach($results as $value) {
            $languages[] = array(
                'name' => $value['name'],
                'code' => strtolower($value['locale']),
                'image'=> '',
                'active' => $value['id_lang'] == $cookie->id_lang
            );
        }

        return $languages;
    }

    public function edit($args)
    {
        global $cookie;
        $lang = $this->model_common_language->getLanguageByLocale($args['code']);

        $cookie->id_lang = $lang['id_lang'];

        return $this->get();
    }
}
