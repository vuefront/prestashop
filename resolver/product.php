<?php

class ProductVFResolver extends VFResolver
{
    private $codename = "d_vuefront";

    public function products($args)
    {
        $this->load->model('product');

        $products = array();

        $filter_data = array(
            'filter_category_id' => $args['category_id'],
            'filter_filter' => $args['filter'],
            'sort' => $args['sort'],
            'order' => $args['order'],
            'start' => ($args['page'] - 1) * $args['size'],
            'limit' => $args['size'],
        );

        if (!empty($args['search'])) {
            $filter_data['filter_name'] = $args['search'];
            $filter_data['filter_tag'] = $args['search'];
            $filter_data['filter_description'] = $args['search'];
        }

        if (!empty($args['special'])) {
            $filter_data['filter_special'] = true;
        }

        if (!empty($args['ids'])) {
            $filter_data['filter_product_ids'] = $args['ids'];
        }

        $product_total = $this->model_product->getTotalProducts($filter_data);

        $products = $this->model_product->getProducts($filter_data);

        return array(
            'content' => $products,
            'first' => $args['page'] === 1,
            'last' => $args['page'] === ceil($product_total / $args['size']),
            'number' => (int) $args['page'],
            'numberOfElements' => count($products),
            'size' => (int) $args['size'],
            'totalPages' => (int) ceil($product_total / $args['size']),
            'totalElements' => (int) $product_total,
        );
    }

    public function product($args)
    {
        $this->load->model('product');
        $product_info = $this->model_product->getProduct($args['id']);

        // $width = $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_width');
        // $height = $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_height');
        // if ($product_info['image']) {
        //     $image = $this->model_tool_image->resize($product_info['image'], $width, $height);
        //     $imageLazy = $this->model_tool_image->resize($product_info['image'], 10, ceil(10 * $height / $width));
        // } else {
        //     $image = $this->model_tool_image->resize('placeholder.png', $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_height'));
        //     $imageLazy = $this->model_tool_image->resize('placeholder.png', 10, 6);
        // }

        // if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
        //     $price = $this->currency->format($this->tax->calculate($product_info['price'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
        // } else {
        //     $price = '';
        // }

        // if ((float) $product_info['special']) {
        //     $special = $this->currency->format($this->tax->calculate($product_info['special'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
        // } else {
        //     $special = '';
        // }

        // if ($this->config->get('config_review_status')) {
        //     $rating = (int) $product_info['rating'];
        // } else {
        //     $rating = '';
        // }

        if ($product_info['quantity'] <= 0) {
            $stock = false;
        } else {
            $stock = true;
        }

        return array(
            'id' => $product_info['id'],
            'name' => $product_info['name'],
            'description' => $product_info['description'],
            'shortDescription' => $product_info['shortDescription'],
            'price' => $product_info['price'],
            'special' => $product_info['special'],
            'model' => $product_info['model'],
            'image' => $product_info['image'],
            'imageLazy' => $product_info['imageLazy'],
            'stock' => $stock,
            'rating' => $product_info['rating'],
        );
    }

    public function relatedProducts($data)
    {
        $this->load->model('product');
        $product_info = $data['parent'];
        $products = $this->model_product->getProductRelated($product_info['id']);

        return $products;
    }

    public function productAttribute($data)
    {
        $this->load->model('product');
        $product_info = $data['parent'];

        $attributes = $this->model_product->getProductAttributes($product_info['id']);

        return $attributes;
    }

    //PrestaShop dosen't have Reviews by default. We will need to implement them.
    public function productReview($data)
    {
        // $this->load->model('catalog/review');
        // $product = $data['parent'];

        // $results = $this->model_catalog_review->getReviewsByProductId($product['id']);

        // $reviews = array();

        // foreach ($results as $result) {
        //     $reviews[] = array(
        //         'author' => $result['author'],
        //         'author_email' => '',
        //         'content' => nl2br($result['text']),
        //         'rating' => (float) $result['rating'],
        //         'created_at' => date($this->language->get('date_format_short'), strtotime($result['date_added'])),

        //     );
        // }

        // return $reviews;
        return array();
    }

    public function productOption($data)
    {
        // $this->load->model('catalog/product');
        // $product_info = $data['parent'];
        // $results = $this->model_catalog_product->getProductOptions($product_info['id']);
        // $options = array();

        // foreach ($results as $option) {
        //     if ($option['type'] == 'checkbox' || $option['type'] == 'radio' || $option['type'] == 'select') {
        //         $product_option_value_data = array();

        //         foreach ($option['product_option_value'] as $option_value) {
        //             if (!$option_value['subtract'] || ($option_value['quantity'] > 0)) {
        //                 $product_option_value_data[] = array(
        //                     'id' => $option_value['product_option_value_id'],
        //                     'name' => $option_value['name'],
        //                 );
        //             }
        //         }

        //         $options[] = array(
        //             'id' => $option['product_option_id'],
        //             'values' => $product_option_value_data,
        //             'name' => $option['name'],
        //         );
        //     }
        // }

        $this->load->model('product');
        $product_info = $data['parent'];

        $options = $this->model_product->getProductOptions($product_info['id']);

        return $options;
    }

    public function productImage($data)
    {
        $this->load->model('product');
        $product_info = $data['parent'];

        $results = $this->model_product->getProductImages($product_info['id']);

        $images = array();

        foreach ($results as $result) {
            // $width = $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_width');
            // $height = $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_height');
            // if ($product_info['image']) {
            //     $image = $this->model_tool_image->resize($result['image'], $width, $height);
            //     $imageLazy = $this->model_tool_image->resize($result['image'], 10, ceil(10 * $height / $width));
            // } else {
            //     $image = $this->model_tool_image->resize('placeholder.png', $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_height'));
            //     $imageLazy = $this->model_tool_image->resize('placeholder.png', 10, 6);
            // }

            $images[] = array(
                'image' => $result['image'],
                'imageLazy' => $result['imageLazy'],
            );
        }

        return $images;
    }

    public function addReview($args)
    {
        // $this->load->model('catalog/review');

        // $reviewData = array(
        //     'name' => $args['author'],
        //     'text' => $args['content'],
        //     'rating' => $args['rating'],
        // );

        // $this->model_catalog_review->addReview($args['id'], $reviewData);

        // return $this->product($args);
    }
}
