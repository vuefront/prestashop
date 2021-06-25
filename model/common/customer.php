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

class ModelCommonCustomer extends Model
{
    public function getCustomers($data = array())
    {
        $sort = '';
        if ($data['sort'] == 'email') {
            $sort = 'p.`email`';
        }

        if ($data['sort'] == 'firstName') {
            $sort = 'p.`firstname`';
        }

        if ($data['sort'] == 'lastName') {
            $sort = 'p.`lastname`';
        }

        $sql = new DbQuery();
        $sql->select('*');
        $sql->from('customer', 'p');
        $sql->where('p.`active` = 1');

        if (!empty($data['search'])) {
            $sql->where("p.`firstname` LIKE '%" . pSQL($data['search']) .
            "%' OR p.lastname LIKE '%" . pSQL($data['search']) .
            "%' OR p.email LIKE '%" . pSQL($data['search']). "%'");
        }

        $sql->orderBy($sort . ' ' . $data['order']);
        if (!empty($data['limit']) && $data['limit'] != -1) {
            $sql->limit($data['limit'], $data['start']);
        }

        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);

        return $result;
    }

    public function getTotalCustomers($data = array())
    {
        $sql = new DbQuery();
        $sql->select('count(*)');
        $sql->from('customer', 'p');
        $sql->where('p.`active` = 1');

        if (!empty($data['search'])) {
            $sql->where("p.`firstname` LIKE '%" . pSQL($data['search']) .
            "%' OR p.lastname LIKE '%" . pSQL($data['search']) .
            "%' OR p.email LIKE '%" . pSQL($data['search']). "%'");
        }

        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql);
        return $result['count(*)'];
    }

    public function updateCustomer($customer)
    {
        $this->context->cookie->id_compare = isset($this->context->cookie->id_compare) ?
            $this->context->cookie->id_compare
            : CompareProduct::getIdCompareByIdCustomer($customer->id);
        $this->context->cookie->id_customer = (int)($customer->id);
        $this->context->cookie->customer_lastname = $customer->lastname;
        $this->context->cookie->customer_firstname = $customer->firstname;
        $this->context->cookie->logged = 1;
        $customer->logged = 1;
        $this->context->cookie->is_guest = $customer->isGuest();
        $this->context->cookie->passwd = $customer->passwd;
        $this->context->cookie->email = $customer->email;

        // Add customer to the context
        $this->context->customer = $customer;

        if (Configuration::get('PS_CART_FOLLOWING') &&
            (
                empty($this->context->cookie->id_cart) || Cart::getNbProducts($this->context->cookie->id_cart) == 0
            )
            && $id_cart = (int)Cart::lastNoneOrderedCart($this->context->customer->id)) {
            $this->context->cart = new Cart($id_cart);
        } else {
            $id_carrier = (int)$this->context->cart->id_carrier;
            $this->context->cart->id_carrier = 0;
            $this->context->cart->setDeliveryOption(null);
        }
        $this->context->cart->id_customer = (int)$customer->id;
        $this->context->cart->secure_key = $customer->secure_key;

        if ($this->ajax && isset($id_carrier) && $id_carrier && Configuration::get('PS_ORDER_PROCESS_TYPE')) {
            $delivery_option = array($this->context->cart->id_address_delivery => $id_carrier.',');
            $this->context->cart->setDeliveryOption($delivery_option);
        }

        $this->context->cart->save();
        $this->context->cookie->id_cart = (int)$this->context->cart->id;
        $this->context->cookie->write();
        $this->context->cart->autosetProductAddress();
    }
}
