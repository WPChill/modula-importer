<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
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

        // Add Importer tab
        add_filter('modula_admin_page_tabs', array($this, 'add_nextgen_importer_tab'));

        // Render Importer tab
        add_action('modula_admin_tab_import_nextgen', array($this, 'render_nextgen_importer_tab'));

        // Include required scripts for import
        add_action('admin_enqueue_scripts', array($this, 'admin_nextgen_scripts'));

        // Required files
        require_once MODULA_IMPORTER_PATH . 'includes/nextgen/class-modula-nextgen-importer.php';

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
    public function admin_nextgen_scripts() {

        $screen = get_current_screen();

        // only enqueue script if we are in Modula Settings page
        if ('modula-gallery' == $screen->post_type && 'modula-gallery_page_modula' == $screen->base) {

            wp_register_script('modula-nextgen-importer', MODULA_IMPORTER_URL . 'assets/js/modula-nextgen-importer.js', '', MODULA_IMPORTER_VERSION, true);
            wp_enqueue_script('modula-nextgen-importer');

            // Strings added to js are used for translation
            wp_localize_script(
                'modula-nextgen-importer',
                'modula_importer_settings',
                array(
                    'ajax'                    => admin_url('admin-ajax.php'),
                    'nonce'                   => wp_create_nonce('modula-importer'),
                    'importing'               => '<span style="color:green">' . __('Import started...', 'modula-importer') . '</span>',
                    'empty_gallery_selection' => __('Please choose at least one NextGEN Gallery to import.', 'modula-importer'),
                )
            );
        }
    }

    /**
     * Add NextGEN Gallery Importer tab
     *
     * @param $tabs
     * @return mixed
     *
     * @since 1.0.0
     */
    public function add_nextgen_importer_tab($tabs) {

        $tabs['import_nextgen'] = array(
            'label'    => esc_html__('Import NextGEN galleries', 'modula-importer'),
            'priority' => 50,
        );

        return $tabs;
    }

    /**
     * Render Importer tab
     *
     * @since 1.0.0
     */
    public function render_nextgen_importer_tab() {
        
        include 'tabs/nextgen-importer-tab.php';
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