<?php

class Blog_PostVFResolver extends VFResolver
{
    private $codename = "d_vuefront";

    public function __construct($registry)
    {
        parent::__construct($registry);
        $this->load->model('blog_post');
    }

    public function post($args)
    {
        $post_info = $this->model_blog_post->getPost($args['id']);

        return array(
            'id' => $post_info['id'],
            'title' => $post_info['title'],
            'description' => $post_info['description'],
            'shortDescription' => $post_info['shortDescription'],
            'image' => $post_info['image'],
            'imageLazy' => $post_info['imageLazy'],
        );
    }

    public function postList($args)
    {
        $this->load->model('blog_post');

        $posts = array();

        $filter_data = array(
            'filter_category_id' => $args['category_id'],
            'sort' => $args['sort'],
            'order' => $args['order'],
            'start' => ($args['page'] - 1) * $args['size'],
            'limit' => $args['size'],
        );

        if (!empty($args['search'])) {
            $filter_data['filter_name'] = $args['search'];
            $filter_data['filter_description'] = $args['search'];
        }

        $post_total = $this->model_blog_post->getTotalPosts($filter_data);

        $posts = $this->model_blog_post->getPosts($filter_data);

        return array(
            'content' => $posts,
            'first' => $args['page'] === 1,
            'last' => $args['page'] === ceil($post_total / $args['size']),
            'number' => (int) $args['page'],
            'numberOfElements' => count($posts),
            'size' => (int) $args['size'],
            'totalPages' => (int) ceil($post_total / $args['size']),
            'totalElements' => (int) $post_total,
        );
    }

    public function postReview($data)
    {
        $post = $data['parent'];

        $this->load->model('blog_review');

        $results = $this->model_blog_review->getReviewsByPostId($post['id']);

        $reviews = array();

        foreach ($results as $result) {
            $reviews[] = array(
                'authorName' => $result['authorName'],
                'authorEmail' => $result['authorEmail'],
                'description' => $result['description'],
                'dateAdded' => $result['dateAdded'],
                'rating' => (float) $result['rating'],
            );
        }

        return $reviews;
    }

    public function addReview($args)
    {
        $this->load->model('review');

        $reviewData = array(
            'authorName' => $args['authorName'],
            'image' => '',
            'description' => $args['description'],
            'rating' => $args['rating'],
        );

        $reviewData['status'] = 0;

        // if (!$this->setting['review']['moderate']) {
        //     $reviewData['status'] = 1;
        // }

        $this->model_blog_review->addReview($args['id'], $reviewData);

        return $this->post($args);
    }
}
