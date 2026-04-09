<?php
/**
 * Activator class
 *
 * @package SkyView_SEO
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * SkyView SEO Activator class
 */
class SkyView_SEO_Activator {

    /**
     * Activate the plugin
     */
    public static function activate() {
        // Create database tables
        self::create_tables();
        
        // Set default options
        self::set_default_options();
        
        // Schedule cron jobs
        self::schedule_cron_jobs();
        
        // Add activation notice
        add_option('skyview_seo_activation_notice', true);
    }

    /**
     * Create database tables
     */
    private static function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Create analysis history table
        $table_name = $wpdb->prefix . 'skyview_seo_analysis';
        
        $sql = "CREATE TABLE $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            post_id bigint(20) NOT NULL,
            analysis_data longtext NOT NULL,
            analysis_score int(3) NOT NULL,
            analysis_date datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
            PRIMARY KEY  (id),
            KEY post_id (post_id)
        ) $charset_collate;";
        
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
        
        // Create issues table
        $table_name = $wpdb->prefix . 'skyview_seo_issues';
        
        $sql = "CREATE TABLE $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            post_id bigint(20) NOT NULL,
            issue_type varchar(50) NOT NULL,
            issue_severity varchar(20) NOT NULL,
            issue_message text NOT NULL,
            issue_status varchar(20) DEFAULT 'open' NOT NULL,
            issue_date datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
            PRIMARY KEY  (id),
            KEY post_id (post_id),
            KEY issue_type (issue_type),
            KEY issue_severity (issue_severity),
            KEY issue_status (issue_status)
        ) $charset_collate;";
        
        dbDelta($sql);
    }

    /**
     * Set default options
     */
    private static function set_default_options() {
        // General settings
        add_option('skyview_seo_settings', array(
            'analyze_on_save' => true,
            'analyze_post_types' => array('post', 'page', 'product'),
            'email_notifications' => false,
            'notification_email' => get_option('admin_email'),
        ));
        
        // Content analysis settings
        add_option('skyview_seo_content_settings', array(
            'min_word_count' => 300,
            'ideal_word_count' => 600,
            'min_title_length' => 30,
            'max_title_length' => 60,
            'min_meta_desc_length' => 120,
            'max_meta_desc_length' => 160,
        ));
        
        // Image analysis settings
        add_option('skyview_seo_image_settings', array(
            'check_alt_tags' => true,
            'check_image_size' => true,
            'max_image_size' => 100, // KB
            'check_lazy_loading' => true,
            'check_filenames' => true,
        ));
        
        // Performance settings
        add_option('skyview_seo_performance_settings', array(
            'check_page_speed' => true,
            'check_mobile_responsiveness' => true,
        ));
    }

    /**
     * Schedule cron jobs
     */
    private static function schedule_cron_jobs() {
        // Schedule daily analysis
        if (!wp_next_scheduled('skyview_seo_daily_analysis')) {
            wp_schedule_event(time(), 'daily', 'skyview_seo_daily_analysis');
        }
        
        // Schedule weekly report
        if (!wp_next_scheduled('skyview_seo_weekly_report')) {
            wp_schedule_event(time(), 'weekly', 'skyview_seo_weekly_report');
        }
    }
}
