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

use PrestaShop\PrestaShop\Adapter\Presenter\Cart\CartPresenter;
use PrestaShop\PrestaShop\Adapter\Product\PriceFormatter;


class ResolverStoreCheckout extends Resolver
{
    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    function __construct($registry)
    {
        parent::__construct($registry);
        $deliveryOptionsFinder = new DeliveryOptionsFinder(
            $this->context,
            $this->translator,
            $this->objectPresenter,
            new PriceFormatter()
        );

        $this->checkoutSession = new CheckoutSession(
            $this->context,
            $deliveryOptionsFinder
        );
    }

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

        $delivers = $this->checkoutSession->getDeliveryOptions();

        $methods = array();

        foreach ($delivers as $carrier_id => $carrier) {
            $addressId = $this->checkoutSession->getIdAddressDelivery();
            $id = $addressId . '-' . $carrier_id;
            $methods[] = array(
                'id' => $id,
                'codename' => $id,
                'name' => $carrier['name']
            );
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

    public function createOrder() {
        $vf_shipping_address = array();

        foreach ($this->shippingAddress() as $value) {
            $vf_shipping_address[$value['name']] = ' ';
        }


        $delivery_address = new Address();
        $delivery_address->alias = 'My Address';
        if(!empty(trim($vf_shipping_address['country']))) {
            $delivery_address->id_country = $vf_shipping_address['country'];
        } else {
            $delivery_address->id_country = 0;
        }
        $delivery_address->city = $vf_shipping_address['city'];
        $delivery_address->company = $vf_shipping_address['company'];
        $delivery_address->firstname = $vf_shipping_address['firstName'];
        $delivery_address->lastname = $vf_shipping_address['lastName'];
        $delivery_address->postcode = $vf_shipping_address['postcode'];
        $delivery_address->address1 = $vf_shipping_address['address1'];
        $delivery_address->address2 = $vf_shipping_address['address2'];
        $delivery_address->save();

        $this->context->cookie->vf_shipping_address = $delivery_address->id;

        $vf_payment_address = array(
            'custom_field' => array()
        );

        $paymentAddress = $this->paymentAddress();
        foreach ($paymentAddress['fields'] as $value) {
            $vf_payment_address[$value['name']] = ' ';
        }

        $invoice_address = new Address();
        $invoice_address->alias = 'My Address';
        if(!empty(trim($vf_payment_address['country']))) {
            $invoice_address->id_country = $vf_payment_address['country'];
        } else {
            $invoice_address->id_country = 0;
        }
        $invoice_address->city = $vf_payment_address['city'];
        $invoice_address->company = $vf_payment_address['company'];
        $invoice_address->firstname = $vf_payment_address['firstName'];
        $invoice_address->lastname = $vf_payment_address['lastName'];
        $invoice_address->postcode = $vf_payment_address['postcode'];
        $invoice_address->vat_number = $vf_payment_address['inn'];
        $invoice_address->address1 = $vf_payment_address['address1'];
        $invoice_address->address2 = $vf_payment_address['address2'];

        $invoice_address->save();

        $this->context->cookie->vf_payment_address = $invoice_address->id;

        $this->context->cookie->vf_payment_method = '';
        $this->context->cookie->vf_shipping_method = '';
        return array('success'=> 'success');
    }

    public function updateOrder($args) {
        $vf_shipping_address_id = 0;

        if (isset($this->context->cookie->vf_shipping_address)) {
            $vf_shipping_address_id = $this->context->cookie->vf_shipping_address;
        }
        $vf_shipping_address = array();
        foreach ($args['shippingAddress'] as $value) {
            if ($value['value']) {
                $vf_shipping_address[$value['name']] = $value['value'];
            } else {
                $vf_shipping_address[$value['name']] = ' ';
            }
        }

        $delivery_address = new Address($vf_shipping_address_id);
        if(!empty(trim($vf_shipping_address['country']))) {
            $delivery_address->id_country = $vf_shipping_address['country'];
            $this->checkoutSession->setIdAddressDelivery($delivery_address->id);
            
        } else {
            $delivery_address->id_country = 0;
        }

        $delivery_address->city = $vf_shipping_address['city'];
        $delivery_address->company = $vf_shipping_address['company'];
        $delivery_address->firstname = $vf_shipping_address['firstName'];
        $delivery_address->lastname = $vf_shipping_address['lastName'];
        $delivery_address->postcode = $vf_shipping_address['postcode'];
        $delivery_address->address1 = $vf_shipping_address['address1'];
        $delivery_address->address2 = $vf_shipping_address['address2'];

        $delivery_address->save(true);

        $vf_payment_address_id = 0;

        if (isset($this->context->cookie->vf_payment_address)) {
            $vf_payment_address_id = $this->context->cookie->vf_payment_address;
        }

        $vf_payment_address = array();

        foreach ($args['paymentAddress'] as $value) {
            if ($value['value']) {
                $vf_payment_address[$value['name']] = $value['value'];
            } else {
                $vf_payment_address[$value['name']] = ' ';
            }
        }

        $invoice_address = new Address($vf_payment_address_id);
        $invoice_address->alias = 'My Address';
        if(!empty(trim($vf_payment_address['country']))) {
            $invoice_address->id_country = $vf_payment_address['country'];
            $this->checkoutSession->setIdAddressInvoice($invoice_address->id);
        } else {
            $invoice_address->id_country = 0;
        }
        $invoice_address->city = $vf_payment_address['city'];
        $invoice_address->company = $vf_payment_address['company'];
        $invoice_address->firstname = $vf_payment_address['firstName'];
        $invoice_address->lastname = $vf_payment_address['lastName'];
        $invoice_address->postcode = $vf_payment_address['postcode'];
        $invoice_address->vat_number = $vf_payment_address['inn'];
        $invoice_address->address1 = $vf_payment_address['address1'];
        $invoice_address->address2 = $vf_payment_address['address2'];

        $invoice_address->save(true);

        $this->context->cookie->vf_payment_method = $args['paymentMethod'];
        $this->context->cookie->vf_shipping_method = $args['shippingMethod'];

        if (!empty($args['shippingMethod'])) {
            $delivery_option = explode('-', $args['shippingMethod']);
            $this->checkoutSession->setDeliveryOption(array($delivery_address->id => $delivery_option[1]));
        }

        return array(
            'paymentMethods' => $this->load->resolver('store/checkout/paymentMethods'),
            'shippingMethods' => $this->load->resolver('store/checkout/shippingMethods'),
            'totals' => $this->load->resolver('store/checkout/totals'),
        );
    }

    public function totals() {
        $totals = array();

        $cart_presenter = new CartPresenter();

        $cart = $cart_presenter->present($this->context->cart);

        foreach ($cart['subtotals'] as $subtotal) {
            if ($subtotal['value'] && $subtotal['type'] != 'tax') {
                $totals[] = array(
                    'title' => $subtotal['label'],
                    'text' => $subtotal['value']
                );
            }
        }

        $display_prices_tax_incl = (bool) (new TaxConfiguration())->includeTaxes();
        $tax_enabled = (bool) Configuration::get('PS_TAX');

        if(!$display_prices_tax_incl && $tax_enabled) {
            $totals[] = array(
                'title' => $cart['totals']['total']['label'].' '.$cart['labels']['tax_short'],
                'text' => $cart['totals']['total']['value']
            );
            $totals[] = array(
                'title' => $cart['totals']['total_including_tax']['label'],
                'text' => $cart['totals']['total_including_tax']['value']
            );
        } else {
            $suffix = '';

            if($tax_enabled) {
                $suffix = ' '.$cart['labels']['tax_short'];
            }

            $totals[] = array(
                'title' => $cart['totals']['total']['label'].$suffix,
                'text' => $cart['totals']['total']['value']
            );
        }

        if($cart['subtotals']['tax']) {
            $totals[] = array(
                'title' => $cart['subtotals']['tax']['label'],
                'text' => sprintf('%label%', $cart['subtotals']['tax']['label'])
            );
        }

        return $totals;
    }

    public function confirmOrder($args)
    {
        $this->load->model('store/checkout');

        $vf_shipping_address_id = 0;

        if (isset($this->context->cookie->vf_shipping_address)) {
            $vf_shipping_address_id = $this->context->cookie->vf_shipping_address;
        }

        $vf_payment_address_id = 0;

        if (isset($this->context->cookie->vf_payment_address)) {
            $vf_payment_address_id = $this->context->cookie->vf_payment_address;
        }

        $paymentMethod = $this->context->cookie->vf_payment_method;
        
        $response = $this->model_store_checkout->requestCheckout(
            'query($codename: String){
                payment(codename: $codename) {
                    codename
                    name
                }
            }',
            array(
                'codename' => $paymentMethod
            )
        );

        $paymentMethod = $response['payment'];

        do {
            $reference = Order::generateReference();
        } while (Order::getByReference($reference)->count());

        if (empty($this->context->cart->getProducts())) {
            throw new Exception("Empty cart");
            return;
        }

        $order = new Order();

        $order->id_address_delivery = $vf_shipping_address_id;
        $order->id_address_invoice = $vf_payment_address_id;
        $order->id_customer = (int) $this->context->cart->id_customer;
        $order->id_currency = $this->context->currency->id;
        $order->id_lang = (int) $this->context->cart->id_lang;
        $order->id_cart = (int) $this->context->cart->id;
        $order->id_shop = (int) $this->context->shop->id;
        $order->id_shop_group = (int) $this->context->shop->id_shop_group;
        $order->payment = $paymentMethod['name'];
        $order->module = "vuefront";

        $order->product_list = $this->context->cart->getProducts();
        $order->recyclable = $this->context->cart->recyclable;
        $order->gift = (int) $this->context->cart->gift;
        $order->gift_message = $this->context->cart->gift_message;
        $order->mobile_theme = $this->context->cart->mobile_theme;
        $order->conversion_rate = $this->context->currency->conversion_rate;
        $order->total_paid_real = 0;
        $shippingMethod = $this->context->cookie->vf_shipping_method;
        $delivery_option = explode('-', $shippingMethod);
        $order->id_carrier =  str_replace(',', '', $delivery_option[1]);

        $order->reference = $reference;
        $order->secure_key = md5(uniqid(rand(), true));

        $order->total_products = (float) $this->context->cart->getOrderTotal(false, Cart::ONLY_PRODUCTS);
        $order->total_products_wt = (float) $this->context->cart->getOrderTotal(true, Cart::ONLY_PRODUCTS);
        $order->total_discounts_tax_excl = (float) abs($this->context->cart->getOrderTotal(false, Cart::ONLY_DISCOUNTS));
        $order->total_discounts_tax_incl = (float) abs($this->context->cart->getOrderTotal(true, Cart::ONLY_DISCOUNTS));
        $order->total_discounts = $order->total_discounts_tax_incl;

        $order->total_wrapping_tax_excl = (float) abs($this->context->cart->getOrderTotal(false, Cart::ONLY_WRAPPING));
        $order->total_wrapping_tax_incl = (float) abs($this->context->cart->getOrderTotal(true, Cart::ONLY_WRAPPING));
        $order->total_wrapping = $order->total_wrapping_tax_incl;

        $order->total_paid_tax_excl = (float) Tools::ps_round((float) $this->context->cart->getOrderTotal(false, Cart::BOTH), _PS_PRICE_COMPUTE_PRECISION_);
        $order->total_paid_tax_incl = (float) Tools::ps_round((float) $this->context->cart->getOrderTotal(true, Cart::BOTH), _PS_PRICE_COMPUTE_PRECISION_);
        $order->total_paid = $order->total_paid_tax_incl;
        $order->round_mode = Configuration::get('PS_PRICE_ROUND_MODE');
        $order->round_type = Configuration::get('PS_ROUND_TYPE');

        $order_total = $order->total_paid;

        $result = $order->add();

        if (!$result) {
            throw new PrestaShopException('Can\'t save Order');
        }

        $order_detail = new OrderDetail(null, null, $this->context);

        $order_detail->createList($order, $this->context->cart, Configuration::get('PS_OS_PREPARATION'), $order->product_list);

        $order_list[] = $order;
        $order_detail_list[] = $order_detail;

        $new_history = new OrderHistory();
        $new_history->id_order = (int) $order->id;
        $new_history->changeIdOrderState((int) Configuration::get('PS_OS_PREPARATION'), $order, true);
        if ($args['withPayment']) {
            $customer = new Customer($order->id_customer);
            $response = $this->model_store_checkout->requestCheckout(
                'mutation($paymentMethod: String, $total: Float, $callback: String, $customerId: String, $customerEmail: String) {
                createOrder(paymentMethod: $paymentMethod, total: $total, callback: $callback, customerId: $customerId, customerEmail: $customerEmail) {
                    url
                }
            }',
                array(
                'paymentMethod' => $paymentMethod['codename'],
                'total' => floatval($order->getOrdersTotalPaid()),
                'customerId' => $order->id_customer,
                'customerEmail' => $customer->email,
                'callback' => Tools::getHttpHost(true) .
                    __PS_BASE_URI__ . 'index.php?controller=callback&module=vuefront&fc=module'
            )
            );
        } else {
            $response = array(
                'createOrder' => array(
                    'url' => ''
                )
            );
        }
            return array(
            'url' => $response['createOrder']['url'],
            'callback' => Tools::getHttpHost(true) .
            __PS_BASE_URI__ . 'index.php?controller=callback&module=vuefront&fc=module',
            'order' => array(
                'id' => $order->id
            )
        );
        
    }

    public function callback()
    {
        $order_id = $_GET['order_id'];
        $rawInput = Tools::file_get_contents('php://input');

        $input = json_decode($rawInput, true);

        if ($input['status'] == 'COMPLETE') {
            $order = new Order($order_id);
            $new_history = new OrderHistory();
            $new_history->id_order = (int) $order->id;
            $new_history->changeIdOrderState((int) Configuration::get('PS_OS_PREPARATION'), $order, true);
        }

        die(json_encode(array('success' => 'success')));
    }
}
