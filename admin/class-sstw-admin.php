<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://github.com/shemi
 * @since      1.0.0
 *
 * @package    Sstw
 * @subpackage Sstw/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Sstw
 * @subpackage Sstw/admin
 * @author     Shemi Perez <shemi.perez@gmail.com>
 */
class Sstw_Admin
{

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $plugin_name The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $version The current version of this plugin.
     */
    private $version;

    protected $templatesLoader;

    protected $searchController;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param      string $plugin_name The name of this plugin.
     * @param      string $version The version of this plugin.
     */
    public function __construct($plugin_name, $version)
    {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
        $this->templatesLoader = new SstwTemplateLoader('admin');
        $this->searchController = new Sstw_Admin_Search;

        $this->define_hooks();
    }

    protected function define_hooks()
    {
        add_action('admin_bar_menu', [$this, 'search_button'], 5);
    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_styles()
    {
        wp_enqueue_style(
            $this->plugin_name,
            plugin_dir_url(__FILE__) . 'css/sstw-admin.css', array(),
            $this->version,
            'all'
        );
    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts()
    {
        wp_enqueue_script(
            'fusejs',
            plugin_dir_url(__FILE__) . 'js/fuse.min.js',
            ['jquery'],
            '3.3.0',
            true
        );

        wp_enqueue_script(
            'mousetrap',
            plugin_dir_url(__FILE__) . 'js/mousetrap.min.js',
            ['jquery'],
            '1.6.2',
            true
        );

        wp_enqueue_script(
            'invert',
            plugin_dir_url(__FILE__) . 'js/invert.min.js',
            ['jquery'],
            '2.0.0',
            true
        );

        wp_enqueue_script(
            $this->plugin_name,
            plugin_dir_url(__FILE__) . 'js/sstw-admin.js',
            ['jquery', 'underscore', 'backbone', 'fusejs', 'mousetrap', 'invert'],
            $this->version,
            true
        );

        wp_localize_script($this->plugin_name, 'sstwData', [
            'i18n' => Sstw_i18n::formTranslations(),
            'staticItems' => SstwAdminMenuExtractor::items(),
            'types' => $this->searchController->get_post_types(),
            'taxonomies' => $this->searchController->get_taxonomies(),
            'plugins' => $this->searchController->get_plugins()
        ]);
    }

    public function admin_menu()
    {
        add_submenu_page(
            'options-general.php',
            __('Search The WP Settings', 'sstw'),
            __('Search The WP', 'sstw'),
            'manage_options',
            'sstw-settings',
            [$this, 'display_settings_page']
        );
    }

    /**
     * @throws Exception
     */
    public function display_settings_page()
    {
        $this->templatesLoader->load('sstw-admin-display', ['sstwAdmin' => $this]);
    }

    /**
     * @param WP_Admin_Bar $admin_bar
     * @throws Exception
     */
    public function search_button(WP_Admin_Bar $admin_bar)
    {
        $admin_bar->remove_node('search');

        $admin_bar->add_menu([
            'parent' => 'top-secondary',
            'id' => 'search',
            'title' => $this->templatesLoader->load('sstw-search-form-template', ['sstwAdmin' => $this], false),
            'group' => false,
            'meta' => [
                'tabindex' => -1,
                'class' => 'sstw-app menupop admin-bar-search'
            ],
        ]);
    }

    /**
     * @throws Exception
     */
    public function search()
    {
        $query = isset($_POST['q']) ? $_POST['q'] : false;
        $type = isset($_POST['type']) ? $_POST['type'] : 'all';

        if(! $query) {
            throw new Exception("Query search term missing");
        }

        $results = $this->searchController->search($query, $type);

        $results = [
            'data' => $results,
            'success' => true
        ];

        echo json_encode($results);

        die();
    }

    /**
     * @throws Exception
     */
    public function render_templates()
    {
        $this->templatesLoader->load('sstw-search-item-template', ['sstwAdmin' => $this]);
    }

}
