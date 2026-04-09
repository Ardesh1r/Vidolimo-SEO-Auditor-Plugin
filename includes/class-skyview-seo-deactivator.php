<?php
/**
 * Deactivator class
 *
 * @package SkyView_SEO
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * SkyView SEO Deactivator class
 */
class SkyView_SEO_Deactivator {

    /**
     * Deactivate the plugin
     */
    public static function deactivate() {
        // Clear scheduled cron jobs
        self::clear_scheduled_hooks();
        
        // Add deactivation notice
        add_option('skyview_seo_deactivation_notice', true);
    }

    /**
     * Clear scheduled hooks
     */
    private static function clear_scheduled_hooks() {
        // Clear daily analysis cron
        $timestamp = wp_next_scheduled('skyview_seo_daily_analysis');
        if ($timestamp) {
            wp_unschedule_event($timestamp, 'skyview_seo_daily_analysis');
        }
        
        // Clear weekly report cron
        $timestamp = wp_next_scheduled('skyview_seo_weekly_report');
        if ($timestamp) {
            wp_unschedule_event($timestamp, 'skyview_seo_weekly_report');
        }
    }
}
