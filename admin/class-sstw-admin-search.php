<?php

/**
 * The admin-search functionality of the plugin.
 *
 * @link       https://github.com/shemi
 * @since      1.0.0
 *
 * @package    Sstw
 * @subpackage Sstw/admin
 */

class Sstw_Admin_Search
{


    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     */
    public function __construct()
    {


    }

    /**
     * @param $term
     * @param string $type
     * @return array
     * @throws Exception
     */
    public function search($term, $type = 'all')
    {
        $method = "query_{$type}";

        if (! method_exists($this, $method)) {
            throw new Exception("Cant find type {$type}");
        }

        return $this->{$method}($term);
    }

    protected function query_all($query)
    {
        $terms = $this->query_terms($query);
        $posts = $this->query_posts($query);
        $users = $this->query_users($query);

        return array_merge($terms, $posts, $users);
    }

    protected function query_posts($query)
    {
        $postsResults = [];
        $types = $this->get_post_types();
        $typesNames = array_keys($types);

        $query = new WP_Query([
            'post_type' => $typesNames,
            'post_status' => 'any',
            's' => $query,
            'nopaging' => false,
            'posts_per_page' => 10,
            'posts_per_archive_page' => 10,
            'ignore_sticky_posts' => true,
        ]);

        if (! $query->have_posts()) {
            return [];
        }

        while ($query->have_posts()) {
            $query->the_post();
            $postModel = SstwSearchItemModel::create([
                'id' => get_the_ID(),
                'title' => get_the_title(),
                'originalTitle' => get_the_title(),
                'url' => get_edit_post_link(0, 'edit'),
                'aliases' => [get_the_excerpt()],
                'type' => get_post_type(),
                'typeLabel' => $types[get_post_type()]['label'],
                'actions' => [
                    ['name' => __('View', 'sstw'), 'url' => get_the_permalink()],
                    ['name' => __('Edit', 'sstw'), 'url' => get_edit_post_link(0, 'edit')]
                ]
            ]);

            array_push($postsResults, $postModel);
        }

        wp_reset_postdata();

        return $postsResults;
    }

    protected function query_terms($query)
    {
        $taxonomies = $this->get_taxonomies();
        $taxonomiesNames = array_keys($taxonomies);
        $terms = [];

        $args = [
            'taxonomy' => $taxonomiesNames,
            'name__like' => $query,
            'number' => 10,
            'fields' => 'all',
            'hide_empty' => false,
            'get' => 'all',
        ];

        $termQuery = new WP_Term_Query($args);

        if (empty($termQuery) || is_wp_error($termQuery) || empty($termQuery->terms)) {
            return [];
        }

        /** @var WP_Term $term */
        foreach ($termQuery->terms as $term) {
            $termModel = SstwSearchItemModel::create([
                'id' => $term->term_id,
                'title' => $term->name,
                'originalTitle' => $term->name,
                'url' => esc_url(get_edit_term_link($term->term_id, $term->taxonomy)),
                'aliases' => [$term->description],
                'type' => $term->taxonomy,
                'typeLabel' => $taxonomies[$term->taxonomy]['label'],
                'actions' => [
                    ['name' => __('View', 'sstw'), 'url' => get_term_link($term)],
                    ['name' => __('Edit', 'sstw'), 'url' => esc_url(get_edit_term_link($term->term_id, $term->taxonomy))]
                ]
            ]);

            array_push($terms, $termModel);
        }

        return $terms;
    }

    protected function query_users($query)
    {
        $users = [];

        $args = [
            'search' => $query,
            'search_columns' => ['user_email', 'user_nicename', 'user_login'],
            'number' => 10,
            'fields' => 'all',
        ];

        $userQuery = new WP_User_Query($args);

        if (empty($userQuery->get_results())) {
            return [];
        }

        /** @var WP_User $user */
        foreach ($userQuery->get_results() as $user) {
            $userModel = SstwSearchItemModel::create([
                'id' => $user->ID,
                'title' => $user->display_name,
                'originalTitle' => $user->display_name,
                'url' => esc_url(get_edit_user_link($user->ID)),
                'aliases' => [$user->user_email],
                'type' => 'user',
                'typeLabel' => __('Users', 'sstw'),
                'actions' => []
            ]);

            array_push($users, $userModel);
        }

        return $users;
    }

    public function get_post_types()
    {
        $postTypes = get_post_types([], 'objects');
        $types = [];

        /** @var WP_Post_Type $postType */
        foreach ($postTypes as $postType) {
            if (! $postType->_edit_link) {
                continue;
            }

            $types[$postType->name] = [
                'name' => $postType->name,
                'label' => $postType->label,
                'icon' => $postType->menu_icon,
                'editLink' => $postType->_edit_link,

            ];
        }

        return $types;
    }

    public function get_taxonomies()
    {
        $taxonomies = get_taxonomies([], 'objects');
        $newTaxonomies = [];

        /** @var WP_Taxonomy $taxonomy */
        foreach ($taxonomies as $taxonomy) {
            $newTaxonomies[$taxonomy->name] = [
                'name' => $taxonomy->name,
                'label' => $taxonomy->label,
            ];
        }

        return $newTaxonomies;
    }

    public function get_plugins()
    {
        $allPlugins = get_plugins();
        $plugins = [];

//        var_dump($allPlugins);
//        die;

        return $plugins;
    }

}
