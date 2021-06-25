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

class ResolverStoreProduct extends Resolver
{
    public function __construct($registry)
    {
        parent::__construct($registry);

        $this->load->model('store/product');
    }

    public function get($args)
    {
        $product = $this->model_store_product->getProduct($args['id']);

        $price = Product::convertAndFormatPrice($product->getPriceWithoutReduct(true, null));
        $special = Product::convertAndFormatPrice($product->getPrice(false, null, 6));

        $images = Product::getCover($args['id']);
        if (!empty($images['id_image'])) {
            $image = $this->context->link->getImageLink(
                $product->link_rewrite,
                $images['id_image'],
                ImageType::getFormatedName("large")
            );
            $imageLazy = $this->context->link->getImageLink(
                $product->link_rewrite,
                $images['id_image'],
                ImageType::getFormatedName("small")
            );
        } else {
            $image = '';
            $imageLazy = '';
        }

        $that = $this;

        $link = $this->context->link->getProductLink(
            $product,
            null,
            null,
            null,
            null,
            null,
            0,
            true
        );

        $link = str_replace($this->context->link->getPageLink(''), '', $link);

        $this->load->model('common/vuefront');
        $resultEvent = $this->model_common_vuefront->pushEvent("fetch_product",  array( "extra" => array(), "product_id" => $product->id));

        return array(
            'id'               => $product->id,
            'name'             => $product->name,
            'description'      => $product->description,
            'shortDescription' => $product->description_short,
            'price'            => $price,
            'special'          => $price != $special ? $special : '',
            'extra'            => $resultEvent['extra'],
            'model'            => $product->reference,
            'image'            => $image,
            'imageBig'         => $image,
            'imageLazy'        => $imageLazy,
            'stock'            => $product->quantity > 0,
            'rating'           => (float)0,
            'manufacturerId' => $product->id_manufacturer,
            'manufacturer' => function($root, $args) {
                return $this->manufacturer(array(
                    'parent' => $root,
                    'args' => $args
                ));
            },
            'keyword'          => $link,
            'meta'             => array(
                'title' => $product->meta_title,
                'description' => $product->meta_description,
                'keyword' => $product->meta_keywords
            ),
            'images' => function ($root, $args) use ($that) {
                return $that->getImages(array(
                    'parent' => $root,
                    'args' => $args
                ));
            },
            'products' => function ($root, $args) use ($that) {
                return $that->getRelatedProducts(array(
                    'parent' => $root,
                    'args' => $args
                ));
            },
            'attributes' => function ($root, $args) use ($that) {
                return $that->getAttributes(array(
                    'parent' => $root,
                    'args' => $args
                ));
            },
            'reviews' => function ($root, $args) use ($that) {
                return $that->load->resolver('store/review/get', array(
                    'parent' => $root,
                    'args' => $args
                ));
            },
            'options' => function ($root, $args) use ($that) {
                return $that->getOptions(array(
                    'parent' => $root,
                    'args' => $args
                ));
            },
            'url' => function($root, $args) {
                return $this->url(array(
                    'parent' => $root,
                    'args' => $args
                ));
            }
        );
    }

    public function manufacturer($data)
    {
        $product_info = $data['parent'];
        
        return $this->load->resolver('store/manufacturer/get', array(
            'id' => $product_info['manufacturerId']
        ));
    }

    public function getList($args)
    {
        $this->load->model('store/product');
        $filter_data = array(
            'sort'  => $args['sort'],
            'order' => $args['order'],
            'filter_manufacturer_id' => $args['manufacturer_id'],
        );

        if ($args['size'] != '-1') {
            $filter_data['start'] = ((int)$args['page'] - 1) * (int)$args['size'];
            $filter_data['limit'] = $args['size'];
        }

        if ($filter_data['sort'] == 'id') {
            $filter_data['sort'] = 'product_id';
        }

        if ($args['category_id'] !== 0) {
            $filter_data['filter_category_id'] = $args['category_id'];
        }

        if (!empty($args['ids'])) {
            $filter_data['filter_ids'] = $args['ids'];
        }

        if (!empty($args['special'])) {
            $filter_data['filter_special'] = true;
        }

        if (!empty($args['search'])) {
            $filter_data['filter_search'] = $args['search'];
        }

        $results = $this->model_store_product->getProducts($filter_data);
        $product_total = $this->model_store_product->getTotalProducts($filter_data);
        $products = [];
        foreach ($results as $product) {
            $products[] = $this->get(array( 'id' => $product['id_product'] ));
        }

        return array(
            'content'          => $products,
            'first'            => $args['page'] === 1,
            'last'             => $args['page'] === ceil($product_total / $args['size']),
            'number'           => (int) $args['page'],
            'numberOfElements' => count($products),
            'size'             => (int) $args['size'],
            'totalPages'       => (int) ceil($product_total / $args['size']),
            'totalElements'    => (int) $product_total,
        );
    }
    public function getRelatedProducts($data)
    {
        $product = $data['parent'];
        $args = $data['args'];

        $upsell_ids = $this->model_store_product->getProductRelated($product['id'], $args['limit']);

        $products = array();

        foreach ($upsell_ids as $product) {
            $products[] = $this->get(array( 'id' => $product['id_product'] ));
        }


        return $products;
    }
    public function getAttributes($data)
    {
        $product = $data['parent'];
        $results = $this->model_store_product->getProductAttributes($product['id']);

        return $results;
    }
    public function getOptions($data)
    {
        $this->load->model('store/product');
        $product = $data['parent'];
       
        $results = $this->model_store_product->getProductOptions($product['id']);
        return $results;
    }
    public function getImages($data)
    {
        $product = $data['parent'];
        $args = $data['args'];
        
        $result = $this->model_store_product->getProductImages($product['id'], $args['limit']);
        $images = Product::getCover($product['id']);

        $result = array_filter($result, function ($value) use ($images) {
            return $value['id_image'] != $images['id_image'];
        });

        return $result;
    }

    public function url($data)
    {
        $product_info = $data['parent'];
        $result = $data['args']['url'];

        $result = str_replace("_id", $product_info['id'], $result);
        $result = str_replace("_name", $product_info['name'], $result);


        if ($product_info['keyword']) {
            $result = '/'.$product_info['keyword'];
            $this->load->model('common/seo');
            $this->model_common_seo->addUrl($result, 'product', $product_info['id']);
        }

        return $result;
    }
}
