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

class ResolverCommonContact extends Resolver
{
    private $codename = "d_vuefront";

    public function get()
    {
        $this->load->model('common/store');
        
        $address = $this->context->shop->getAddress();

        $format = '{company}' . "\n" . '{address_1}' .
            "\n" . '{address_2}' . "\n" .
            '{city} {postcode}' . "\n" .
            '{zone}' . "\n" . '{country}';

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
            'zone'      => State::getNameById($this->context->cookie->id_lang, $address->id_state),
            'zone_code' => Country::getIsoById($this->context->cookie->id_lang, $address->id_state),
            'country'   => Country::getNameById($this->context->cookie->id_lang, $address->id_country)
        );

        $locations = array();

        $result = $this->model_common_store->getStores();

        foreach ($result as $store) {
            $store_info = new Store($store['id_store']);
            if (_PS_VERSION_ > '1.7.0.0') {
                $imageRetriever = new \PrestaShop\PrestaShop\Adapter\Image\ImageRetriever($this->context->link);
                $image = $imageRetriever->getImage($store_info, $store['id_store']);
                $thumb = $image['large']['url'];
                $thumbLazy = $image['small']['url'];
            } else {
                $thumb = $this->context->link->getImageLink($store['name'], $store['id_store']);
                $thumbLazy = $this->context->link->getImageLink($store['name'], $store['id_store']);
            }

            $locations[] = array(
                'name' => $store['name'],
                'comment' => $store['note'],
                'address' => $this->getFormattedAddress($store),
                'geocode' => $store['latitude'].', '.$store['longitude'],
                'telephone' => $store['phone'],
                'fax' => $store['fax'],
                'image' => $thumb,
                'imageLazy' => $thumbLazy
            );
        }

        return array(
            'store' => Configuration::get('PS_SHOP_NAME'),
            'email' => Configuration::get('PS_SHOP_EMAIL'),
            'address' => str_replace(
                array("\r\n", "\r", "\n"),
                ', ',
                preg_replace(
                    array("/\r\r+/", "/\n\n+/"),
                    ', ',
                    trim(str_replace($find, $replace, $format))
                )
            ),
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
        $format = '{address_1}' . "\n" . '{address_2}' .
             "\n" . '{city} {postcode}' .
              "\n" . '{zone}' . "\n" . '{country}';

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
            'zone'      => State::getNameById($this->context->cookie->id_lang, $store['id_state']),
            'zone_code' => Country::getIsoById($this->context->cookie->id_lang, $store['id_state']),
            'country'   => Country::getNameById($this->context->cookie->id_lang, $store['id_country'])
        );

        return str_replace(
            array("\r\n", "\r", "\n"),
            ', ',
            preg_replace(
                array( "/\r\r+/", "/\n\n+/"),
                ', ',
                trim(str_replace($find, $replace, $format))
            )
        );
    }

    public function send($args)
    {
        try {
            Mail::send(
                (int)(Configuration::get('PS_LANG_DEFAULT')),
                'vuefront_contact',
                'Enquiry '.$args['name'],
                array(
                    '{email}' => $args['email'],
                    '{message}' => $args['message']
                ),
                Configuration::get('PS_SHOP_EMAIL'),
                null,
                null,
                null,
                null,
                null,
                _PS_MODULE_DIR_.'d_vuefront/mails/'
            );
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }

        return array(
            "status" => true
        );
    }
}
