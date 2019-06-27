<?php

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
            $image = $this->context->link->getImageLink($product->link_rewrite, $images['id_image'], ImageType::getFormatedName("large"));
            $imageLazy = $this->context->link->getImageLink($product->link_rewrite, $images['id_image'], ImageType::getFormatedName("small"));
        } else {
            $image = '';
            $imageLazy = '';
        }

        return array(
            'id'               => $product->id,
            'name'             => $product->name,
            'description'      => $product->description,
            'shortDescription' => $product->description_short,
            'price'            => $price,
            'special'          => $price != $special ? $special : '',
            'model'            => $product->reference,
            'image'            => $image,
            'imageBig'         => $image,
            'imageLazy'        => $imageLazy,
            'stock'            => $product->quantity > 0,
            'rating'           => (float)0,
            'keyword'          => $product->link_rewrite,
            'images' => function ($root, $args) {
                return $this->getImages(array(
                    'parent' => $root,
                    'args' => $args
                ));
            },
            'products' => function ($root, $args) {
                return $this->getRelatedProducts(array(
                    'parent' => $root,
                    'args' => $args
                ));
            },
            'attributes' => function ($root, $args) {
                return $this->getAttributes(array(
                    'parent' => $root,
                    'args' => $args
                ));
            },
            'reviews' => function ($root, $args) {
                return $this->load->resolver('store/review/get', array(
                    'parent' => $root,
                    'args' => $args
                ));
            },
            'options' => function ($root, $args) {
                return $this->getOptions(array(
                    'parent' => $root,
                    'args' => $args
                ));
            }
        );
    }
    public function getList($args)
    {
        $this->load->model('store/product');
        $filter_data = array(
            'sort'  => $args['sort'],
            'order' => $args['order'],
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

        return $result;
    }
}
