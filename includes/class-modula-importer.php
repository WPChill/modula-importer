<?php

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

class Modula_Importer {

    /**
     * Holds the class object.
     *
     * @since 1.0.0
     *
     * @var object
     */
    public static $instance;

    /**
     * The name of the plugin.
     *
     * @since 1.0.0
     *
     * @var string
     */
    public $plugin_name = 'Modula Importer';

    /**
     * Unique plugin slug identifier.
     *
     * @since 1.0.0
     *
     * @var string
     */
    public $plugin_slug = 'modula-importer';

    /**
     * Primary class constructor.
     *
     * @since 1.0.0
     */
    public function __construct() {

        // Load the plugin textdomain.
        add_action('plugins_loaded', array($this, 'load_plugin_textdomain'));

        // Add Importer Tab
        add_filter('modula_admin_page_tabs', array($this, 'add_importer_tab'));

       // Render Importer tab
        add_action('modula_admin_tab_importer', array($this, 'render_importer_tab'));

        // Include required scripts for import
        add_action('admin_enqueue_scripts', array($this, 'admin_importer_scripts'));

        // Required files
        require_once MODULA_IMPORTER_PATH . 'includes/nextgen/class-modula-nextgen-importer.php';
        require_once MODULA_IMPORTER_PATH . 'includes/envira/class-modula-envira-importer.php';
        require_once MODULA_IMPORTER_PATH . 'includes/final-tiles/class-modula-final-tiles-importer.php';
        require_once MODULA_IMPORTER_PATH . 'includes/photoblocks/class-modula-photoblocks-importer.php';
        require_once MODULA_IMPORTER_PATH . 'includes/wp-core-gallery/class-modula-wp-core-gallery-importer.php';

        // Load the plugin.
        $this->init();

    }

    /**
     * Loads the plugin textdomain for translation.
     *
     * @since 1.0.0
     */
    public function load_plugin_textdomain() {

        load_plugin_textdomain($this->plugin_slug, false, MODULA_IMPORTER_PATH . '/languages/');
    }

    /**
     * Loads the plugin into WordPress.
     *
     * @since 1.0.0
     */
    public function init() {

        // Load admin only components.
        if (is_admin()) {
            add_action('modula_pro_updater', array($this, 'addon_updater'), 15, 2);
            add_filter('modula_uninstall_options',array($this,'uninstall_options'),16,1);
            add_action('wp_ajax_modula_importer_get_galleries',array($this,'get_source_galleries'));
        }

    }

    /**
     * Updater
     *
     * @param $license_key
     * @param $store_url
     *
     * @since 1.0.0
     */
    public function addon_updater($license_key, $store_url) {

        if (class_exists('Modula_Pro_Base_Updater')) {
            $modula_addon_updater = new Modula_Pro_Base_Updater($store_url, MODULA_IMPORTER_FILE,
                array(
                    'version' => MODULA_IMPORTER_VERSION,        // current version number
                    'license' => $license_key,               // license key (used get_option above to retrieve from DB)
                    'item_id' => 0,                      // ID of the product
                    'author'  => 'MachoThemes',            // author of this plugin
                    'beta'    => false,
                )
            );
        }
    }

    /**
     * Enqueue import script
     *
     * @since 1.0.0
     */
    public function admin_importer_scripts() {

        $screen = get_current_screen();

        // only enqueue script if we are in Modula Settings page
        if ('modula-gallery' == $screen->post_type && 'modula-gallery_page_modula' == $screen->base ) {

            $ajax_url      = admin_url('admin-ajax.php');
            $nonce         = wp_create_nonce('modula-importer');
            $empty_gallery = esc_html__('Please choose at least one gallery to migrate.', 'modula-importer');

            wp_enqueue_style('modula-importer', MODULA_IMPORTER_URL . 'assets/css/modula-importer.css', array(), MODULA_IMPORTER_VERSION);
            wp_enqueue_script('modula-importer', MODULA_IMPORTER_URL . 'assets/js/modula-importer.js', array('jquery'), MODULA_IMPORTER_VERSION, true);
            wp_localize_script(
                'modula-importer',
                'modula_importer',
                array(
                    'ajax'                    => $ajax_url,
                    'nonce'                   => $nonce,
                    'importing'               => '<span style="color:green">' . esc_html__(' Migration started...', 'modula-importer') . '</span>',
                    'empty_gallery_selection' => $empty_gallery,
                )
            );
        }
    }


    /**
     * Add Importer tab
     *
     * @param $tabs
     * @return mixed
     *
     * @since 1.0.0
     */
    public function add_importer_tab($tabs) {
        $tabs['importer'] = array(
            'label'    => esc_html__('Migrate galleries', 'modula-importer'),
            'priority' => 50,
        );

        return $tabs;
    }


    /**
     * Render Importer tab
     *
     * @since 1.0.0
     */
    public function render_importer_tab() {
        include 'tabs/modula-importer-tab.php';
    }

    public function uninstall_options($options_array){
        array_push($options_array,'modula_importer');

        return $options_array;
    }

    /**
     * Count galleries
     *
     * @return mixed
     *
     * @since 1.0.0
     */
    public function get_sources() {

        global $wpdb;
        $sources = array();

        // Assume they are none
        $envira       = false;
        $nextgen      = false;
        $final_tiles  = false;
        $photoblolcks = false;
        $wp_core      = false;

        $envira = $wpdb->get_results(" SELECT COUNT(ID) FROM " . $wpdb->prefix . "posts WHERE post_type ='envira'");

        if ($wpdb->get_var("SHOW TABLES LIKE '" . $wpdb->prefix . "ngg_gallery'")) {
            $nextgen = $wpdb->get_results(" SELECT COUNT(gid) FROM " . $wpdb->prefix . "ngg_gallery");
        }

        if ($wpdb->get_var("SHOW TABLES LIKE '" . $wpdb->prefix . "finaltiles_gallery'")) {
            $final_tiles = $wpdb->get_results(" SELECT COUNT(Id) FROM " . $wpdb->prefix . "finaltiles_gallery");
        }

        if ($wpdb->get_var("SHOW TABLES LIKE '" . $wpdb->prefix . "photoblocks'")) {
            $photoblolcks = $wpdb->get_results(" SELECT COUNT(id) FROM " . $wpdb->prefix . "photoblocks");
        }

        $sql     = "SELECT COUNT(ID) FROM " . $wpdb->prefix . "posts WHERE `post_content` LIKE '%[galler%'";
        $wp_core = $wpdb->get_results($sql);

        // Need to get this so we can handle the object to check if mysql returned 0
        $envira_return = (NULL != $envira) ? get_object_vars($envira[0]) : false;
        $nextgen_return = (NULL != $nextgen) ? get_object_vars($nextgen[0]) : false;
        $final_tiles_return = (NULL != $final_tiles) ? get_object_vars($final_tiles[0]) : false;
        $photoblocks_return = (NULL != $photoblolcks) ? get_object_vars($photoblolcks[0]) : false;
        $wp_core_return = (NULL != $wp_core) ? get_object_vars($wp_core[0]) : false;

        // Check to see if there are any entries and insert into array
        if ($envira && NULL != $envira && !empty($envira) && $envira_return  && '0' != $envira_return['COUNT(ID)']) {
            $sources['envira'] = 'Envira Gallery';
        }
        if ($nextgen && NULL != $nextgen && !empty($nextgen) && $nextgen_return && '0' != $nextgen_return['COUNT(gid)']) {
            $sources['nextgen'] = 'NextGEN Gallery';
        }
        if ($final_tiles && NULL != $final_tiles && !empty($final_tiles) && $final_tiles_return && '0' != $final_tiles_return['COUNT(Id)']) {
            $sources['final_tiles'] = 'Image Photo Gallery Final Tiles Grid';
        }
        if ($photoblolcks && NULL != $photoblolcks && !empty($photoblolcks) && $photoblocks_return && '0' != $photoblocks_return['COUNT(id)']) {
            $sources['photoblocks'] = 'Gallery PhotoBlocks';
        }
        if ($wp_core && NULL != $wp_core && !empty($wp_core) && $wp_core_return && '0' != $wp_core_return['COUNT(ID)'] ) {
            $sources['wp_core'] = 'WP Core Galleries';
        }

        if (!empty($sources)) {
            return $sources;
        }

        return false;
    }


    public function get_source_galleries() {

        check_ajax_referer('modula-importer', 'nonce');
        $source = isset($_POST['source']) ? $_POST['source'] : false;

        if (!$source || 'none' == $source) {
            echo esc_html__('There is no source selected', 'modula-importer');
            wp_die();
        }

        $import_settings = get_option('modula_importer');
        $import_settings = wp_parse_args($import_settings, array('galleries' => array()));
        $galleries       = array();
        $html            = '';

        switch ($source) {
            case 'envira' :
                $gal_source = Modula_Envira_Importer::get_instance();
                $galleries  = $gal_source->get_galleries();
                break;
            case 'nextgen':
                $gal_source = Modula_Nextgen_Importer::get_instance();
                $galleries  = $gal_source->get_galleries();
                break;
            case 'final_tiles' :
                $gal_source = Modula_Final_Tiles_Importer::get_instance();
                $galleries  = $gal_source->get_galleries();
                break;
            case 'photoblocks':
                $gal_source = Modula_Photoblocks_Importer::get_instance();
                $galleries  = $gal_source->get_galleries();
                break;
            case 'wp_core':
                $gal_source = Modula_WP_Core_Gallery_Importer::get_instance();
                $galleries  = $gal_source->get_galleries();
                break;
        }

        // Although this isn't necessary, sources have been checked before in tab
        // it is best if we do another check, just to be sure.
        if (empty($galleries)) {
            echo esc_html__('There are no galleries present');
            wp_die();
        }

        foreach ($galleries as $gallery) {
            $imported = false;
            switch ($source) {
                case 'envira':
                    if (isset($import_settings['galleries']['envira']) && in_array($gallery->ID, $import_settings['galleries']['envira'])) {
                        $imported = true;
                    }
                    $id    = $gallery->ID;
                    $title = $gallery->post_title;
                    break;
                case 'final_tiles' :
                    if (isset($import_settings['galleries']['final_tiles']) && in_array($gallery->Id, $import_settings['galleries']['final_tiles'])) {
                        $imported = true;
                    }
                    $id         = $gallery->Id;
                    $ftg_config = json_decode($gallery->configuration);
                    $title      = $ftg_config->name;
                    break;
                case 'nextgen':
                    if (isset($import_settings['galleries']['nextgen']) && in_array($gallery->gid, $import_settings['galleries']['nextgen'])) {
                        $imported = true;
                    }
                    $id    = $gallery->gid;
                    $title = $gallery->title;
                    break;
                case
                'photoblocks':
                    if (isset($import_settings['galleries']['photoblocks']) && in_array($gallery->id, $import_settings['galleries']['photoblocks'])) {
                        $imported = true;
                    }
                    $id    = $gallery->id;
                    $title = $gallery->name;
                    break;
                case 'wp_core':
                    $id    = $gallery->ID;
                    $title = $gallery->post_title;
                    break;
                default:
                    if (isset($import_settings['galleries'][$source]) && in_array($gallery->id, $import_settings['galleries'][$source])) {
                        $imported = true;
                    }
                    $id    = $gallery->ID;
                    $title = $gallery->post_title;

            }


            $html .= '<div class="modula-importer-checkbox-wrapper">' .
                     '<label for="' . esc_attr($source) . '-galleries-' . esc_attr($id) . '"' .
                     ' data-id="' . esc_attr($id) . '" ' . ($imported ? ' class="imported"' : '') . '>' .
                     '<input type="checkbox" name="gallery"' .
                     ' id="' . esc_attr($source) . '-galleries-' . esc_attr($id) . '"' .
                     ' value="' . esc_attr($id) . '"/>';
            $html .= esc_html($title);
            $html .= '<span class="modula-importer-gallery-status">';

            if ($imported) {
                $html .= '<i class="imported-check dashicons dashicons-yes"></i>';
            }

            $html .= '</span></label></div>';

        }

        echo $html;
        wp_die();
    }

    /**
     * Returns the singleton instance of the class.
     *
     * @return object The Modula_Importer object.
     *
     * @since 1.0.0
     */
    public static function get_instance() {

        if (!isset(self::$instance) && !(self::$instance instanceof Modula_Importer)) {
            self::$instance = new Modula_Importer();
        }

        return self::$instance;
    }
}