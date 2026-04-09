<?php
/**
 * Main plugin class
 *
 * @package SkyView_SEO
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Main SkyView SEO class
 */
class SkyView_SEO {

    /**
     * Plugin instance
     *
     * @var SkyView_SEO
     */
    private static $instance = null;

    /**
     * Admin class instance
     *
     * @var SkyView_SEO_Admin
     */
    public $admin;

    /**
     * Analyzer class instance
     *
     * @var SkyView_SEO_Analyzer
     */
    public $analyzer;

    /**
     * Constructor
     */
    public function __construct() {
        // Nothing to do here
    }

    /**
     * Initialize the plugin
     */
    public function init() {
        // Initialize admin
        $this->admin = new SkyView_SEO_Admin();
        $this->admin->init();

        // Initialize analyzer
        $this->analyzer = new SkyView_SEO_Analyzer();
        $this->analyzer->init();

        // Register scripts and styles
        add_action('admin_enqueue_scripts', array($this, 'register_assets'));
    }

    /**
     * Get plugin instance
     *
     * @return SkyView_SEO
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Register scripts and styles
     */
    public function register_assets() {
        // Register styles
        wp_register_style(
            'skyview-seo-admin',
            SKYVIEW_SEO_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            SKYVIEW_SEO_VERSION
        );

        // Register scripts
        wp_register_script(
            'skyview-seo-admin',
            SKYVIEW_SEO_PLUGIN_URL . 'assets/js/admin.js',
            array('jquery', 'wp-api'),
            SKYVIEW_SEO_VERSION,
            true
        );

        // Localize script
        wp_localize_script('skyview-seo-admin', 'skyviewSEO', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('skyview-seo-nonce'),
            'i18n' => array(
                'analyzing' => __('Analyzing...', 'vidolimo-seo-auditor'),
                'complete' => __('Analysis Complete', 'vidolimo-seo-auditor'),
                'error' => __('Error', 'vidolimo-seo-auditor'),
            ),
        ));
    }
}
