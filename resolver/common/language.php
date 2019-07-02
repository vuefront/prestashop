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
        foreach ($results as $value) {
            $code = strtolower($value['locale']);
            if($code == 'en-us') {
                $code = 'en-gb';
            }
            $languages[] = array(
                'name' => $value['name'],
                'code' => $code,
                'image'=> '',
                'active' => $value['id_lang'] == $cookie->id_lang
            );
        }

        return $languages;
    }

    public function edit($args)
    {
        global $cookie;

        $this->load->model('common/language');

        $code = $args['code'];

        if ($code == 'en-gb') {
            $code = 'en-us';
        }

         
        $lang = $this->model_common_language->getLanguageByLocale($code);

        $cookie->id_lang = $lang['id_lang'];

        return $this->get();
    }
}
