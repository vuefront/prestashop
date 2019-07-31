<?php
/**
 * 2019 (c) VueFront
 *
 * MODULE VueFront
 *
 * @author    VueFront
 * @copyright Copyright (c) permanent, VueFront
 * @license   MIT
 *
 * @version   0.1.0
 */
class ResolverBlogPost extends Resolver
{
    public function __construct($registry)
    {
        parent::__construct($registry);
        $this->status = Module::isInstalled('prestablog');
    }

    public function get($args)
    {
        if ($this->status) {
            $this->load->model('blog/post');

            $post = $this->model_blog_post->getPost($args['id']);

            $date_format = '%A %d %B %Y';

            $that = $this;

            return array(
                'id' => $post['id'],
                'name' => $post['title'],
                'title' => $post['title'],
                'shortDescription' => $post['shortDescription'],
                'description' => $post['description'],
                'keyword' => $post['keyword'],
                'image' => $post['image'],
                'imageLazy' => $post['imageLazy'],
                'rating' => null,
                'meta' => array(
                    'title' => $post['meta']['title'],
                    'description' => $post['meta']['description'],
                    'keyword' => $post['meta']['keyword'],
                ),
                'datePublished' => iconv(
                    mb_detect_encoding(strftime($date_format, strtotime($post['datePublished']))),
                    'utf-8//IGNORE',
                    strftime($date_format, strtotime($post['datePublished']))
                ),
                'reviews' => function ($root, $args) use ($that) {
                    return $that->load->resolver('blog/review/get', array(
                        'parent' => $root,
                        'args' => $args,
                    ));
                },
                'categories' => function ($root, $args) use ($that) {
                    return $that->load->resolver('blog/post/categories', array(
                        'parent' => $root,
                        'args' => $args,
                    ));
                },
                'next' => function ($root, $args) use ($that) {
                    return $that->load->resolver('blog/post/next', array(
                        'parent' => $root,
                        'args' => $args,
                    ));
                },
                'prev' => function ($root, $args) use ($that) {
                    return $that->load->resolver('blog/post/prev', array(
                        'parent' => $root,
                        'args' => $args,
                    ));
                },
            );
        } else {
            return array();
        }
    }

    public function getList($args)
    {
        if ($this->status) {
            $this->load->model('blog/post');
            $filter_data = array(
                'limit' => $args['size'],
                'start' => ($args['page'] - 1) * $args['size'],
                'sort' => $args['sort'],
                'order' => $args['order'],
            );

            if ($args['category_id'] !== 0) {
                $filter_data['filter_category_id'] = $args['category_id'];
            }

            $results = $this->model_blog_post->getPosts($filter_data);
            $product_total = $this->model_blog_post->getTotalPosts($filter_data);

            $posts = array();

            foreach ($results as $post) {
                $posts[] = $this->get(array('id' => $post['id_prestablog_news']));
            }

            return array(
                'content' => $posts,
                'first' => $args['page'] === 1,
                'last' => $args['page'] === ceil($product_total / $args['size']),
                'number' => (int) $args['page'],
                'numberOfElements' => count($posts),
                'size' => (int) $args['size'],
                'totalPages' => (int) ceil($product_total / $args['size']),
                'totalElements' => (int) $product_total,
            );
        } else {
            return array(
                'content' => array(),
            );
        }
    }

    public function categories($args)
    {
        if ($this->status) {
            $this->load->model('blog/category');
            $post = $args['parent'];

            $result = $this->model_blog_category->getCategoryByPostId($post['id']);
            $categories = array();
            foreach ($result as $category) {
                $categories[] = $this->load->resolver(
                    'blog/category/get',
                    array('id' => $category['categorie'])
                );
            }

            return $categories;
        } else {
            return array();
        }
    }

    public function next($args)
    {
        if ($this->status) {
            $this->load->model('blog/post');
            $post = $args['parent'];
            $next_post_info = $this->model_blog_post->getNextPost($post['id']);
            if (empty($next_post_info)) {
                return null;
            }

            return $this->get(array('id' => $next_post_info['id_prestablog_news']));
        } else {
            return array();
        }
    }

    public function prev($args)
    {
        if ($this->status) {
            $this->load->model('blog/post');
            $post = $args['parent'];
            $prev_post_info = $this->model_blog_post->getPrevPost($post['id']);

            if (empty($prev_post_info)) {
                return null;
            }

            return $this->get(array('id' => $prev_post_info['id_prestablog_news']));
        } else {
            return array();
        }
    }
}
