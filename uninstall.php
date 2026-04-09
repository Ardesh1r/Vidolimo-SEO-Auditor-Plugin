<?php
// Exit if accessed directly.
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Delete options.
delete_option('skyview_seo_settings');
delete_option('skyview_seo_content_settings');
delete_option('skyview_seo_image_settings');
delete_option('skyview_seo_performance_settings');
delete_option('skyview_seo_admin_notices');
delete_option('skyview_seo_activation_notice');
delete_option('skyview_seo_deactivation_notice');

// Clear scheduled hooks.
wp_clear_scheduled_hook('skyview_seo_daily_analysis');
wp_clear_scheduled_hook('skyview_seo_weekly_report');

// Remove stored analysis post meta.
delete_metadata('post', 0, '_skyview_seo_analysis', '', true);

// Drop custom tables.
global $wpdb;
$skyview_seo_analysis_table = esc_sql($wpdb->prefix . 'skyview_seo_analysis');
$skyview_seo_issues_table = esc_sql($wpdb->prefix . 'skyview_seo_issues');

// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter
$wpdb->query("DROP TABLE IF EXISTS {$skyview_seo_analysis_table}");
// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter
$wpdb->query("DROP TABLE IF EXISTS {$skyview_seo_issues_table}");
