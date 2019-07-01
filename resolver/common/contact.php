<?php

class ResolverCommonContact extends Resolver
{
    private $codename = "d_vuefront";

    public function get()
    {
        global $cookie;

        $address = $this->context->shop->getAddress();

        $format = '{company}' . "\n" . '{address_1}' . "\n" . '{address_2}' . "\n" . '{city} {postcode}' . "\n" . '{zone}' . "\n" . '{country}';

        $find = array(
                '{company}',
                '{address_1}',
                '{address_2}',
                '{city}',
                '{postcode}',
                '{zone}',
                '{zone_code}',
                '{country}'
            );

        $replace = array(
            'firstname' => $address->firstname,
            'lastname'  => $address->lastname,
            'company'   => $address->company,
            'address_1' => $address->address1,
            'address_2' => $address->address2,
            'city'      => $address->city,
            'postcode'  => $address->postcode,
            'zone'      => State::getNameById($cookie->id_lang, $address->id_state),
            'zone_code' => Country::getIsoById($cookie->id_lang, $address->id_state),
            'country'   => Country::getNameById($cookie->id_lang, $address->id_country)
        );

        $locations = array();

        $imageRetriever = new \PrestaShop\PrestaShop\Adapter\Image\ImageRetriever($this->context->link);

        foreach (Store::getStores($cookie->id_lang) as $store) {
            $image = $imageRetriever->getImage(new Store($store['id_store']), $store['id_store']);

            $locations[] = array(
                'name' => $store['name'],
                'comment' => $store['note'],
                'address' => $this->getFormattedAddress($store),
                'geocode' => $store['latitude'].', '.$store['longitude'],
                'telephone' => $store['phone'],
                'fax' => $store['fax'],
                'image' => $image['large']['url'],
                'imageLazy' => $image['small']['url']
            );
        }


        return array(
            'store' => Configuration::get('PS_SHOP_NAME'),
            'email' => Configuration::get('PS_SHOP_EMAIL'),
            'address' => str_replace(array("\r\n", "\r", "\n"), ', ', preg_replace(array("/\r\r+/", "/\n\n+/"), ', ', trim(str_replace($find, $replace, $format)))),
            'geocode' => '',
            'locations' => $locations,
            'telephone' => Configuration::get('PS_SHOP_PHONE'),
            'fax' => Configuration::get('PS_SHOP_FAX'),
            'open' => '',
            'comment' => ''
        );
    }

    public function getFormattedAddress($store)
    {
        global $cookie;

        $format = '{address_1}' . "\n" . '{address_2}' . "\n" . '{city} {postcode}' . "\n" . '{zone}' . "\n" . '{country}';

        $find = array(
            '{address_1}',
            '{address_2}',
            '{city}',
            '{postcode}',
            '{zone}',
            '{zone_code}',
            '{country}'
        );

        $replace = array(
            'address_1' => $store['address1'],
            'address_2' => $store['address2'],
            'city'      => $store['city'],
            'postcode'  => $store['postcode'],
            'zone'      => State::getNameById($cookie->id_lang, $store['id_state']),
            'zone_code' => Country::getIsoById($cookie->id_lang, $store['id_state']),
            'country'   => Country::getNameById($cookie->id_lang, $store['id_country'])
        );

        return str_replace(array("\r\n", "\r", "\n"), ', ', preg_replace(array( "/\r\r+/", "/\n\n+/"), ', ', trim(str_replace($find, $replace, $format))));
    }

    public function send($args)
    {
        try {
            global $cookie;
            Mail::send(
                (int)(Configuration::get('PS_LANG_DEFAULT')),
                'vuefront_contact',
                'Enquiry '.$args['name'],
                array(
                    '{email}' => $args['email'],
                    '{message}' => $args['message']
                ),
                Configuration::get('PS_SHOP_EMAIL'),
                NULL, NULL, NULL,NULL,NULL,_PS_MODULE_DIR_.'d_vuefront/mails/');
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }

        return array(
            "status" => true
        );
    }
}
