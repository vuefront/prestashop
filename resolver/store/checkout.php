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

class ResolverStoreCheckout extends Resolver
{
    public function link()
    {
        return array(
            'link' => $this->context->link->getPageLink('order')
        );
    }


    public function paymentMethods()
    {
        $this->load->model('store/checkout');

        $response = $this->model_store_checkout->requestCheckout(
            '{
                payments {
                    setting
                    codename
                    status
                    name
              }
            }',
            array()
        );

        $methods = array();

        foreach ($response['payments'] as $key => $value) {
            if ($value['status']) {
                $methods[] = array(
                    'id' => $value['codename'],
                    'codename' => $value['codename'],
                    "name" => $value['name']
                );
            }
        }

        return $methods;
    }

    public function shippingMethods()
    {
        $this->load->model('store/checkout');

        $response = $this->model_store_checkout->requestCheckout(
            '{
                shippings {
                    setting
                    codename
                    status
                    name
              }
            }',
            array()
        );

        $methods = array();

        foreach ($response['shippings'] as $key => $value) {
            if ($value['status']) {
                $methods[] = array(
                    'id' => $value['codename'],
                    'codename' => $value['codename'],
                    "name" => $value['name']
                );
            }
        }

        return $methods;
    }

    public function paymentAddress()
    {
        $fields = array();

        $fields[] = array(
            'type' => 'text',
            'name' => 'firstName',
            'required' => true
        );
        $fields[] = array(
            'type' => 'text',
            'name' => 'lastName',
            'required' => true
        );

        $fields[] = array(
            'type' => 'text',
            'name' => 'email',
            'required' => true
        );

        $fields[] = array(
            'type' => 'text',
            'name' => 'company',
            'required' => false
        );

        $fields[] = array(
            'type' => 'text',
            'name' => 'inn',
            'required' => false
        );

        $fields[] = array(
            'type' => 'text',
            'name' => 'address1',
            'required' => true
        );
        $fields[] = array(
            'type' => 'text',
            'name' => 'address2',
            'required' => false
        );

        $fields[] = array(
            'type' => 'text',
            'name' => 'postcode',
            'required' => true
        );

        $fields[] = array(
            'type' => 'text',
            'name' => 'city',
            'required' => true
        );

        $fields[] = array(
            'type' => 'country',
            'name' => 'country',
            'required' => true
        );

        $fields[] = array(
            'type' => 'text',
            'name' => 'phone',
            'required' => true
        );

        $agree = null;

        return array(
            'fields' => $fields,
            'agree' => $agree
        );
    }

    public function shippingAddress()
    {
        $fields = array();
        $fields = array();

        $fields[] = array(
            'type' => 'text',
            'name' => 'firstName',
            'required' => true
        );
        $fields[] = array(
            'type' => 'text',
            'name' => 'lastName',
            'required' => true
        );

        $fields[] = array(
            'type' => 'text',
            'name' => 'company',
            'required' => false
        );

        $fields[] = array(
            'type' => 'text',
            'name' => 'inn',
            'required' => false
        );

        $fields[] = array(
            'type' => 'text',
            'name' => 'address1',
            'required' => true
        );
        $fields[] = array(
            'type' => 'text',
            'name' => 'address2',
            'required' => false
        );

        $fields[] = array(
            'type' => 'text',
            'name' => 'postcode',
            'required' => true
        );

        $fields[] = array(
            'type' => 'text',
            'name' => 'city',
            'required' => true
        );

        $fields[] = array(
            'type' => 'country',
            'name' => 'country',
            'required' => true
        );

        $fields[] = array(
            'type' => 'text',
            'name' => 'phone',
            'required' => true
        );

        return $fields;
    }

    public function createOrder($args)
    {
        $this->load->model('store/checkout');

        $paymentAddress = array();

        foreach ($args['paymentAddress'] as $value) {
            $paymentAddress[$value['name']] = $value['value'];
        }

        $shippingAddress = array();

        foreach ($args['shippingAddress'] as $value) {
            $shippingAddress[$value['name']] = $value['value'];
        }

        $response = $this->model_store_checkout->requestCheckout(
            'query($pCodename: String, $sCodename: String){
                payment(codename: $pCodename) {
                    codename
                    name
                }
                shipping(codename: $sCodename) {
                    codename
                    name
                }
            }',
            array(
                'pCodename' => $args['paymentMethod'],
                'sCodename' => $args['shippingMethod']
            )
        );

        $shippingMethod = $response['shipping'];
        $paymentMethod = $response['payment'];

        $delivery_option_list = $this->context->cart->getDeliveryOptionList();
        $package_list = $this->context->cart->getPackageList();
        $cart_delivery_option = $this->context->cart->getDeliveryOption();

        // If some delivery options are not defined, or not valid, use the first valid option
        foreach ($delivery_option_list as $id_address => $package) {
            if (!isset($cart_delivery_option[$id_address]) || !array_key_exists($cart_delivery_option[$id_address], $package)) {
                foreach ($package as $key => $val) {
                    $cart_delivery_option[$id_address] = $key;
                    break;
                }
            }
        }

        $order_id = 0;
        $order_total = 0;
        $order_list = array();
        $order_detail_list = array();

        do {
            $reference = Order::generateReference();
        } while (Order::getByReference($reference)->count());

        foreach ($cart_delivery_option as $id_address => $key_carriers) {
            foreach ($delivery_option_list[$id_address][$key_carriers]['carrier_list'] as $id_carrier => $data) {
                foreach ($data['package_list'] as $id_package) {
                    // Rewrite the id_warehouse
                    $package_list[$id_address][$id_package]['id_warehouse'] = (int) $this->context->cart->getPackageIdWarehouse($package_list[$id_address][$id_package], (int) $id_carrier);
                    $package_list[$id_address][$id_package]['id_carrier'] = $id_carrier;
                }
            }
        }

        CartRule::cleanCache();
        $cart_rules = $this->context->cart->getCartRules();
        foreach ($cart_rules as $cart_rule) {
            if (($rule = new CartRule((int) $cart_rule['obj']->id)) && Validate::isLoadedObject($rule)) {
                if ($rule->checkValidity($this->context, true, true)) {
                    $this->context->cart->removeCartRule((int) $rule->id);
                    if (isset($this->context->cookie, $this->context->cookie->id_customer) && $this->context->cookie->id_customer && !empty($rule->code)) {
                        Tools::redirect('index.php?controller=order&submitAddDiscount=1&discount_name=' . urlencode($rule->code));
                    }
                }
            }
        }


        if(empty($package_list)) {
            throw new Exception("Empty cart");
            return;
        }

        foreach ($package_list as $id_address => $packageByAddress) {
            foreach ($packageByAddress as $id_package => $package) {
                $order = new Order();

                $carrierId = isset($package['id_carrier']) ? $package['id_carrier'] : null;

                $carrier = null;
                if (!$this->context->cart->isVirtualCart() && isset($carrierId)) {
                    $carrier = new Carrier((int) $carrierId, (int) $this->context->cart->id_lang);
                    $order->id_carrier = (int) $carrier->id;
                    $carrierId = (int) $carrier->id;
                } else {
                    $order->id_carrier = 0;
                    $carrierId = 0;
                }

                $delivery_address = new Address();
                $delivery_address->alias = 'My Address';
                $delivery_address->id_country = $shippingAddress['country'];
                $delivery_address->city = $shippingAddress['city'];
                $delivery_address->company = $shippingAddress['company'];
                $delivery_address->firstname = $shippingAddress['firstName'];
                $delivery_address->lastname = $shippingAddress['lastName'];
                $delivery_address->postcode = $shippingAddress['postcode'];
                // $delivery_address->id_state = $shippingAddress['zoneId'];
                $delivery_address->address1 = $shippingAddress['address1'];
                $delivery_address->address2 = $shippingAddress['address2'];

                $delivery_address->save();

                $invoice_address = new Address();
                $invoice_address->alias = 'My Address';
                $invoice_address->id_country = $paymentAddress['country'];
                $invoice_address->city = $paymentAddress['city'];
                $invoice_address->company = $paymentAddress['company'];
                $invoice_address->firstname = $paymentAddress['firstName'];
                $invoice_address->lastname = $paymentAddress['lastName'];
                $invoice_address->postcode = $paymentAddress['postcode'];
                $invoice_address->vat_number = $paymentAddress['inn'];
                $invoice_address->address1 = $paymentAddress['address1'];
                $invoice_address->address2 = $paymentAddress['address2'];

                $invoice_address->save();

                $order->id_address_delivery = $delivery_address->id;
                $order->id_address_invoice = $invoice_address->id;
                $order->id_customer = (int) $this->context->cart->id_customer;
                $order->id_currency = $this->context->currency->id;
                $order->id_lang = (int) $this->context->cart->id_lang;
                $order->id_cart = (int) $this->context->cart->id;
                $order->id_shop = (int) $this->context->shop->id;
                $order->id_shop_group = (int) $this->context->shop->id_shop_group;
                $order->payment = $paymentMethod['name'];
                $order->module = "vuefront";
                $order->product_list = $package['product_list'];
                $order->recyclable = $this->context->cart->recyclable;
                $order->gift = (int) $this->context->cart->gift;
                $order->gift_message = $this->context->cart->gift_message;
                $order->mobile_theme = $this->context->cart->mobile_theme;
                $order->conversion_rate = $this->context->currency->conversion_rate;
                $order->total_paid_real = 0;

                $order->reference = $reference;
                $order->secure_key = md5(uniqid(rand(), true));

                $order->total_products = (float) $this->context->cart->getOrderTotal(false, Cart::ONLY_PRODUCTS, $order->product_list, $carrierId);
                $order->total_products_wt = (float) $this->context->cart->getOrderTotal(true, Cart::ONLY_PRODUCTS, $order->product_list, $carrierId);
                $order->total_discounts_tax_excl = (float) abs($this->context->cart->getOrderTotal(false, Cart::ONLY_DISCOUNTS, $order->product_list, $carrierId));
                $order->total_discounts_tax_incl = (float) abs($this->context->cart->getOrderTotal(true, Cart::ONLY_DISCOUNTS, $order->product_list, $carrierId));
                $order->total_discounts = $order->total_discounts_tax_incl;

                $order->total_shipping_tax_excl = (float) $this->context->cart->getPackageShippingCost($carrierId, false, null, $order->product_list);
                $order->total_shipping_tax_incl = (float) $this->context->cart->getPackageShippingCost($carrierId, true, null, $order->product_list);
                $order->total_shipping = $order->total_shipping_tax_incl;

                if (null !== $carrier && Validate::isLoadedObject($carrier)) {
                    $order->carrier_tax_rate = $carrier->getTaxesRate(new Address((int) $this->context->cart->{Configuration::get('PS_TAX_ADDRESS_TYPE')}));
                }

                $order->total_wrapping_tax_excl = (float) abs($this->context->cart->getOrderTotal(false, Cart::ONLY_WRAPPING, $order->product_list, $carrierId));
                $order->total_wrapping_tax_incl = (float) abs($this->context->cart->getOrderTotal(true, Cart::ONLY_WRAPPING, $order->product_list, $carrierId));
                $order->total_wrapping = $order->total_wrapping_tax_incl;

                $order->total_paid_tax_excl = (float) Tools::ps_round((float) $this->context->cart->getOrderTotal(false, Cart::BOTH, $order->product_list, $carrierId), _PS_PRICE_COMPUTE_PRECISION_);
                $order->total_paid_tax_incl = (float) Tools::ps_round((float) $this->context->cart->getOrderTotal(true, Cart::BOTH, $order->product_list, $carrierId), _PS_PRICE_COMPUTE_PRECISION_);
                $order->total_paid = $order->total_paid_tax_incl;
                $order->round_mode = Configuration::get('PS_PRICE_ROUND_MODE');
                $order->round_type = Configuration::get('PS_ROUND_TYPE');

                $order_total = $order->total_paid;

                $order->invoice_date = '0000-00-00 00:00:00';
                $order->delivery_date = '0000-00-00 00:00:00';

                $result = $order->add();


                if (!$result) {
                    throw new PrestaShopException('Can\'t save Order');
                }
                $order_detail = new OrderDetail(null, null, $this->context);

                $order_detail->createList($order, $this->context->cart, Configuration::get('PS_OS_PREPARATION'), $order->product_list, 0, true, $package_list[$id_address][$id_package]['id_warehouse']);

                $order_list[] = $order;
                $order_detail_list[] = $order_detail;

                $new_history = new OrderHistory();
                $new_history->id_order = (int) $order->id;
                $new_history->changeIdOrderState((int) Configuration::get('PS_OS_PREPARATION'), $order, true);


                if (null !== $carrier) {
                    $order_carrier = new OrderCarrier();
                    $order_carrier->id_order = (int) $order->id;
                    $order_carrier->id_carrier = $carrierId;
                    $order_carrier->weight = (float) $order->getTotalWeight();
                    $order_carrier->shipping_cost_tax_excl = (float) $order->total_shipping_tax_excl;
                    $order_carrier->shipping_cost_tax_incl = (float) $order->total_shipping_tax_incl;
                    $order_carrier->add();
                }
                $order_id = $order_id;
            }
        }

        $response = $this->model_store_checkout->requestCheckout(
            'mutation($paymentMethod: String, $shippingMethod: String, $total: Float, $callback: String) {
                createOrder(paymentMethod: $paymentMethod, shippingMethod: $shippingMethod, total: $total, callback: $callback) {
                    url
                }
            }',
            array(
                'paymentMethod' => $paymentMethod['codename'],
                'shippingMethod' => $shippingMethod['codename'],
                'total' => floatval($order_total),
                'callback' => Tools::getHttpHost(true).
                __PS_BASE_URI__.'index.php?controller=callback&module=vuefront&fc=module',
                // 'callback' => urldecode(add_query_arg(
                //     array(
                //         'order_id' => $order_id 
                //     ), 
                //     get_rest_url( null, '/vuefront/v1/callback')
                // ))
            )
        );

        return array(
            'url' => $response['createOrder']['url'],
            'order' => array(
                'id' => $order_id
            )
        );
    }

    public function callback()
    {
        $order_id = $_GET['order_id'];
        $rawInput = Tools::file_get_contents('php://input');

        $input = json_decode($rawInput, true);

        if($input['status'] == 'COMPLETE') {
            $order = new Order($order_id);
            $new_history = new OrderHistory();
            $new_history->id_order = (int) $order->id;
            $new_history->changeIdOrderState((int) Configuration::get('PS_OS_PREPARATION'), $order, true);
        }

        die(json_encode(array('success' => 'success')));
    }
}
