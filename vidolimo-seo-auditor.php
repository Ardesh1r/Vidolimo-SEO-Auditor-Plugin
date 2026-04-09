<?php
/**
 * Plugin Name: Vidolimo SEO Auditor
 * Description: Comprehensive WordPress SEO analysis tool. Analyze pages for SEO issues and get actionable recommendations for images, content, links, technical SEO, performance, and content freshness.
 * Version: 1.0.2
 * Author: Ardeshir Shojaei
 * Author URI: https://ardeshirshojaei.com
 * Plugin URI: https://github.com/Ardesh1r/Vidolimo-SEO-Auditor-Plugin
 * Text Domain: vidolimo-seo-auditor
 * Domain Path: /languages
 * License: GPL v2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('SKYVIEW_SEO_VERSION', '1.0.2');
define('SKYVIEW_SEO_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('SKYVIEW_SEO_PLUGIN_URL', plugin_dir_url(__FILE__));
define('SKYVIEW_SEO_PLUGIN_BASENAME', plugin_basename(__FILE__));

// Include required files
require_once SKYVIEW_SEO_PLUGIN_DIR . 'includes/class-skyview-seo.php';
require_once SKYVIEW_SEO_PLUGIN_DIR . 'includes/class-skyview-seo-admin.php';
require_once SKYVIEW_SEO_PLUGIN_DIR . 'includes/class-skyview-seo-analyzer.php';
require_once SKYVIEW_SEO_PLUGIN_DIR . 'includes/class-skyview-seo-content-analyzer.php';
require_once SKYVIEW_SEO_PLUGIN_DIR . 'includes/class-skyview-seo-link-analyzer.php';
require_once SKYVIEW_SEO_PLUGIN_DIR . 'includes/class-skyview-seo-image-analyzer.php';
require_once SKYVIEW_SEO_PLUGIN_DIR . 'includes/class-skyview-seo-technical-analyzer.php';
require_once SKYVIEW_SEO_PLUGIN_DIR . 'includes/class-skyview-seo-performance-analyzer.php';
require_once SKYVIEW_SEO_PLUGIN_DIR . 'includes/class-skyview-seo-freshness-analyzer.php';

/**
 * Initialize the plugin
 */
function skyview_seo_init() {
    // Initialize the main plugin class
    $skyview_seo = new SkyView_SEO();
    $skyview_seo->init();
}
add_action('plugins_loaded', 'skyview_seo_init');

/**
 * Register activation hook
 */
function skyview_seo_activate() {
    // Create necessary database tables
    require_once SKYVIEW_SEO_PLUGIN_DIR . 'includes/class-skyview-seo-activator.php';
    SkyView_SEO_Activator::activate();
}
register_activation_hook(__FILE__, 'skyview_seo_activate');

/**
 * Register deactivation hook
 */
function skyview_seo_deactivate() {
    // Clean up if needed
    require_once SKYVIEW_SEO_PLUGIN_DIR . 'includes/class-skyview-seo-deactivator.php';
    SkyView_SEO_Deactivator::deactivate();
}
register_deactivation_hook(__FILE__, 'skyview_seo_deactivate');
