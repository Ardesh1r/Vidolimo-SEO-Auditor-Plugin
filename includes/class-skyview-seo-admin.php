<?php
/**
 * Admin class
 *
 * @package SkyView_SEO
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * SkyView SEO Admin class
 */
class SkyView_SEO_Admin {

    /**
     * Constructor
     */
    public function __construct() {
        // Nothing to do here
    }

    /**
     * Initialize the admin
     */
    public function init() {
        // Add admin menu
        add_action('admin_menu', array($this, 'add_admin_menu'));
        
        // Register settings
        add_action('admin_init', array($this, 'register_settings'));
        
        // Metabox registration removed - analysis only available in plugin page
        
        // Add AJAX handlers
        add_action('wp_ajax_skyview_seo_analyze', array($this, 'ajax_analyze'));
        add_action('wp_ajax_skyview_seo_refresh', array($this, 'ajax_refresh'));
        add_action('wp_ajax_skyview_seo_clear_data', array($this, 'ajax_clear_data'));
        add_action('wp_ajax_skyview_seo_get_analysis', array($this, 'ajax_get_analysis'));
        add_action('wp_ajax_skyview_seo_export_report', array($this, 'ajax_export_report'));
        
        // Add admin notices
        add_action('admin_notices', array($this, 'admin_notices'));
        
        // Enqueue admin scripts and styles
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        // Enqueue global menu icon styles on all admin pages
        add_action('admin_enqueue_scripts', array($this, 'enqueue_menu_icon_styles'));
    }

    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        // Add main menu - points to dashboard (no submenus as requested)
        add_menu_page(
            __('Vidolimo SEO Auditor', 'vidolimo-seo-auditor'),
            __('Vidolimo SEO', 'vidolimo-seo-auditor'),
            'manage_options',
            'vidolimo-seo-auditor',
            array($this, 'render_dashboard_page'),
            'dashicons-admin-generic',
            81
        );

        // Add a mirror dashboard submenu (required so WordPress keeps the top-level click on Dashboard)
        add_submenu_page(
            'vidolimo-seo-auditor',
            __('Vidolimo SEO Auditor', 'vidolimo-seo-auditor'),
            __('Dashboard', 'vidolimo-seo-auditor'),
            'manage_options',
            'vidolimo-seo-auditor',
            array($this, 'render_dashboard_page')
        );

        // Add analyze page (hidden from menu, used for individual page analysis)
        add_submenu_page(
            'vidolimo-seo-auditor',
            __('Analyze', 'vidolimo-seo-auditor'),
            null,
            'manage_options',
            'skyview-seo-analyze',
            array($this, 'render_analyze_page')
        );
    }

    // No submenu cleanup needed; CSS hides the submenu flyout visually.

    /**
     * Add meta boxes
     * 
     * Note: Metabox registration has been disabled as requested.
     * SEO analysis is now only available through the dedicated plugin page.
     */
    public function add_meta_boxes() {
        // Metabox registration disabled - analysis only available in plugin page
        return;
    }
    
    /**
     * Register settings
     */
    public function register_settings() {
        // Register the settings
        register_setting(
            'skyview_seo_settings',
            'skyview_seo_settings',
            array(
                'sanitize_callback' => array($this, 'sanitize_settings'),
                'default' => array(
                    'analyze_homepage' => true,
                    'analyze_posts' => true,
                    'analyze_pages' => true,
                    'analyze_products' => true,
                    'analyze_categories' => true,
                    'analyze_tags' => true
                )
            )
        );
    }
    
    /**
     * Sanitize settings
     *
     * @param array $input Settings input
     * @return array Sanitized settings
     */
    public function sanitize_settings($input) {
        $sanitized = array();
        
        // Sanitize checkboxes
        $checkboxes = array('analyze_homepage', 'analyze_posts', 'analyze_pages', 'analyze_products', 'analyze_categories', 'analyze_tags');
        
        foreach ($checkboxes as $checkbox) {
            $sanitized[$checkbox] = isset($input[$checkbox]) ? true : false;
        }
        
        return $sanitized;
    }

    /**
     * Render dashboard page
     */
    public function render_dashboard_page() {
        // Enqueue styles and scripts
        wp_enqueue_style('skyview-seo-admin');
        wp_enqueue_script('skyview-seo-admin');
        
        // Include dashboard template
        include SKYVIEW_SEO_PLUGIN_DIR . 'templates/admin/dashboard.php';
    }

    /**
     * Render content analysis page
     */
    public function render_content_page() {
        // Enqueue styles and scripts
        wp_enqueue_style('skyview-seo-admin');
        wp_enqueue_script('skyview-seo-admin');
        
        // Include content analysis template
        include SKYVIEW_SEO_PLUGIN_DIR . 'templates/admin/content.php';
    }

    /**
     * Render links analysis page
     */
    public function render_links_page() {
        // Enqueue styles and scripts
        wp_enqueue_style('skyview-seo-admin');
        wp_enqueue_script('skyview-seo-admin');
        
        // Include links analysis template
        include SKYVIEW_SEO_PLUGIN_DIR . 'templates/admin/links.php';
    }

    /**
     * Render images analysis page
     */
    public function render_images_page() {
        // Enqueue styles and scripts
        wp_enqueue_style('skyview-seo-admin');
        wp_enqueue_script('skyview-seo-admin');
        
        // Include images analysis template
        include SKYVIEW_SEO_PLUGIN_DIR . 'templates/admin/images.php';
    }

    /**
     * Render technical SEO page
     */
    public function render_technical_page() {
        // Enqueue styles and scripts
        wp_enqueue_style('skyview-seo-admin');
        wp_enqueue_script('skyview-seo-admin');
        
        // Include technical SEO template
        include SKYVIEW_SEO_PLUGIN_DIR . 'templates/admin/technical.php';
    }

    /**
     * Render performance page
     */
    public function render_performance_page() {
        // Enqueue styles and scripts
        wp_enqueue_style('skyview-seo-admin');
        wp_enqueue_script('skyview-seo-admin');
        
        // Include performance template
        include SKYVIEW_SEO_PLUGIN_DIR . 'templates/admin/performance.php';
    }

    /**
     * Render settings page
     */
    public function render_settings_page() {
        // Enqueue styles and scripts
        wp_enqueue_style('skyview-seo-admin');
        wp_enqueue_script('skyview-seo-admin');
        
        // Include settings template
        include SKYVIEW_SEO_PLUGIN_DIR . 'templates/admin/settings.php';
    }

    /**
     * Render meta box - DISABLED
     * 
     * This function has been disabled as the metabox feature is no longer used.
     * SEO analysis is only available through the dedicated plugin page.
     *
     * @param WP_Post $post Post object
     */
    public function render_meta_box($post) {
        // Function disabled - analysis only available in plugin page
        return;
    }

    /**
     * AJAX analyze handler
     */
    public function ajax_analyze() {
        // Check nonce
        $nonce = isset($_POST['nonce']) ? sanitize_text_field(wp_unslash($_POST['nonce'])) : '';
        if (empty($nonce) || !wp_verify_nonce($nonce, 'skyview-seo-nonce')) {
            wp_send_json_error(array('message' => esc_html__('Security check failed', 'vidolimo-seo-auditor')));
        }

        // Check permissions
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(array('message' => esc_html__('You do not have permission to do this', 'vidolimo-seo-auditor')));
        }

        // Check post ID
        $post_id = isset($_POST['post_id']) ? absint(wp_unslash($_POST['post_id'])) : 0;
        if (!$post_id) {
            wp_send_json_error(array('message' => esc_html__('Invalid post ID', 'vidolimo-seo-auditor')));
        }

        // Get post ID
        // Get analyzer
        $analyzer = new SkyView_SEO_Analyzer();

        // Run analysis
        $analysis = $analyzer->analyze_post($post_id);

        // Send response
        wp_send_json_success(array(
            'message' => esc_html__('Analysis complete', 'vidolimo-seo-auditor'),
            'analysis' => $analysis,
        ));
    }

    /**
     * Enqueue lightweight global styles for the admin menu icon so it appears correctly on all admin pages
     */
    public function enqueue_menu_icon_styles() {
        wp_enqueue_style('skyview-seo-menu-icon', SKYVIEW_SEO_PLUGIN_URL . 'assets/css/menu-icon.css', array(), SKYVIEW_SEO_VERSION);
    }
    
    /**
     * AJAX get analysis data handler
     * Used by the global search feature to get analysis data for posts
     */
    public function ajax_get_analysis() {
        // Check nonce
        $nonce = isset($_POST['nonce']) ? sanitize_text_field(wp_unslash($_POST['nonce'])) : '';
        if (empty($nonce) || !wp_verify_nonce($nonce, 'skyview-seo-analysis')) {
            wp_send_json_error(array('message' => esc_html__('Security check failed', 'vidolimo-seo-auditor')));
        }

        // Check permissions
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(array('message' => esc_html__('You do not have permission to do this', 'vidolimo-seo-auditor')));
        }

        // Check post ID
        $post_id = isset($_POST['post_id']) ? absint(wp_unslash($_POST['post_id'])) : 0;
        if (!$post_id) {
            wp_send_json_error(array('message' => esc_html__('Invalid post ID', 'vidolimo-seo-auditor')));
        }

        // Get analyzer
        $analyzer = new SkyView_SEO_Analyzer();

        // Get analysis data
        $analysis = $analyzer->get_post_analysis($post_id);

        // Send response
        wp_send_json_success($analysis);
    }

    /**
     * Admin notices
     */
    public function admin_notices() {
        // Check if we need to show any notices
        $notices = get_option('skyview_seo_admin_notices', array());
        
        // If no notices, return
        if (empty($notices)) {
            return;
        }
        
        // Display notices
        foreach ($notices as $notice_id => $notice) {
            echo '<div class="notice notice-' . esc_attr($notice['type']) . ' is-dismissible">';
            echo '<p>' . wp_kses_post($notice['message']) . '</p>';
            echo '</div>';
        }
        
        // Clear notices
        update_option('skyview_seo_admin_notices', array());
    }

    /**
     * Enqueue admin assets
     */
    public function enqueue_admin_assets($hook) {
        // Only enqueue on SkyView SEO pages
        if (strpos($hook, 'vidolimo-seo-auditor') === false) {
            return;
        }
        
        // Enqueue admin styles
        wp_enqueue_style('skyview-seo-admin', SKYVIEW_SEO_PLUGIN_URL . 'assets/css/admin.css', array(), SKYVIEW_SEO_VERSION);
        
        // Enqueue admin scripts
        wp_enqueue_script('skyview-seo-admin', SKYVIEW_SEO_PLUGIN_URL . 'assets/js/admin.js', array('jquery'), SKYVIEW_SEO_VERSION, true);
        
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

    /**
     * AJAX refresh handler
     */
    public function ajax_refresh() {
        // Check nonce
        $nonce = isset($_POST['nonce']) ? sanitize_text_field(wp_unslash($_POST['nonce'])) : '';
        if (empty($nonce) || !wp_verify_nonce($nonce, 'skyview-seo-nonce')) {
            wp_send_json_error(array('message' => esc_html__('Security check failed', 'vidolimo-seo-auditor')));
        }

        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => esc_html__('You do not have permission to do this', 'vidolimo-seo-auditor')));
        }

        wp_send_json_success(array('message' => esc_html__('Analysis refreshed', 'vidolimo-seo-auditor')));
    }

    /**
     * AJAX export report handler
     */
    public function ajax_export_report() {
        // Check nonce
        $nonce = isset($_POST['nonce']) ? sanitize_text_field(wp_unslash($_POST['nonce'])) : '';
        if (empty($nonce) || !wp_verify_nonce($nonce, 'skyview-seo-nonce')) {
            wp_send_json_error(array('message' => esc_html__('Security check failed', 'vidolimo-seo-auditor')));
        }

        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => esc_html__('You do not have permission to do this', 'vidolimo-seo-auditor')));
        }

        $analyzer = new SkyView_SEO_Analyzer();
        $site_data = $analyzer->get_site_analysis();

        $filename = 'skyview-seo-report-' . gmdate('Y-m-d') . '.json';

        nocache_headers();
        header('Content-Type: application/json; charset=' . get_option('blog_charset'));
        header('Content-Disposition: attachment; filename=' . $filename);

        echo wp_json_encode($site_data);
        wp_die();
    }

    /**
     * AJAX clear data handler
     */
    public function ajax_clear_data() {
        // Check nonce
        $nonce = isset($_POST['nonce']) ? sanitize_text_field(wp_unslash($_POST['nonce'])) : '';
        if (empty($nonce) || !wp_verify_nonce($nonce, 'skyview-seo-clear-data')) {
            wp_send_json_error(array('message' => esc_html__('Security check failed', 'vidolimo-seo-auditor')));
        }

        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => esc_html__('You do not have permission to do this', 'vidolimo-seo-auditor')));
        }

        wp_send_json_success(array('message' => esc_html__('Data cleared', 'vidolimo-seo-auditor')));
    }

    /**
     * Render analyze page
     */
    public function render_analyze_page() {
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have permission to access this page.', 'vidolimo-seo-auditor'));
        }

        // Enqueue styles and scripts
        wp_enqueue_style('skyview-seo-admin');
        wp_enqueue_script('skyview-seo-admin');
        
        // Include analyze template
        include SKYVIEW_SEO_PLUGIN_DIR . 'templates/admin/analyze.php';
    }
}
