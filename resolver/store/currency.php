<?php

class ResolverStoreCurrency extends Resolver
{
    private $codename = "d_vuefront";

    public function get()
    {
        global $cookie;

        $this->load->model('store/currency');
        $results = $this->model_store_currency->getCurrencies();
        $currencies = array();

        foreach ($results  as $result) {
            $currencies[] = array(
                'title'        => $result['name'],
                'code'         => $result['id_currency'],
                'symbol_left'  => '',
                'symbol_right' => '',
                'active' => $cookie->id_currency == $result['id_currency']
            );
        }

        return $currencies;
    }

    public function edit($args)
    {
        global $cookie;

        $cookie->id_currency = $args['code'];

        return $this->get();
    }
}
