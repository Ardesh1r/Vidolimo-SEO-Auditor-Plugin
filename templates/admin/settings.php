<?php
/**
 * Simplified Settings template
 *
 * @package SkyView_SEO
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Generate and display settings page
 */
function skyview_seo_settings_page() {
    // Get settings
    $settings = get_option('skyview_seo_settings', array());

    // Default settings
    $default_settings = array(
        'analyze_homepage' => true,
        'analyze_posts' => true,
        'analyze_pages' => true,
    'analyze_products' => true,
    'analyze_categories' => false,
    'analyze_tags' => false,
);

// Merge with defaults
$settings = wp_parse_args($settings, $default_settings);
?>

<div class="wrap skyview-seo-wrap">
    <h1 class="skyview-seo-title">
        <img src="<?php echo esc_url(SKYVIEW_SEO_PLUGIN_URL . 'assets/logo.png'); ?>" alt="Vidolimo SEO Auditor" class="skyview-seo-logo">
        <?php esc_html_e('Settings', 'vidolimo-seo-auditor'); ?>
    </h1>

    <div class="skyview-seo-notice skyview-seo-notice-info">
        <p>
            <?php esc_html_e('Vidolimo SEO automatically analyzes your content when you view the dashboard.', 'vidolimo-seo-auditor'); ?>
            <?php esc_html_e('Configure below which content types should be included in the analysis.', 'vidolimo-seo-auditor'); ?>
        </p>
    </div>

    <form method="post" action="options.php" id="skyview-seo-settings-form">
        <?php settings_fields('skyview_seo_settings'); ?>
        <?php do_settings_sections('skyview_seo_settings'); ?>

        <div class="skyview-seo-settings-section">
            <h2><?php esc_html_e('Content Analysis Settings', 'vidolimo-seo-auditor'); ?></h2>
            <table class="form-table">
                <tr>
                    <th scope="row"><?php esc_html_e('Analyze Content Types', 'vidolimo-seo-auditor'); ?></th>
                    <td>
                        <fieldset>
                            <legend class="screen-reader-text"><?php esc_html_e('Analyze Content Types', 'vidolimo-seo-auditor'); ?></legend>
                            <label for="skyview_seo_analyze_homepage">
                                <input type="checkbox" name="skyview_seo_settings[analyze_homepage]" id="skyview_seo_analyze_homepage" value="1" <?php checked($settings['analyze_homepage']); ?>>
                                <?php esc_html_e('Homepage', 'vidolimo-seo-auditor'); ?>
                            </label>
                            <br>
                            <label for="skyview_seo_analyze_posts">
                                <input type="checkbox" name="skyview_seo_settings[analyze_posts]" id="skyview_seo_analyze_posts" value="1" <?php checked($settings['analyze_posts']); ?>>
                                <?php esc_html_e('Posts', 'vidolimo-seo-auditor'); ?>
                            </label>
                            <br>
                            <label for="skyview_seo_analyze_pages">
                                <input type="checkbox" name="skyview_seo_settings[analyze_pages]" id="skyview_seo_analyze_pages" value="1" <?php checked($settings['analyze_pages']); ?>>
                                <?php esc_html_e('Pages', 'vidolimo-seo-auditor'); ?>
                            </label>
                            <br>
                            <label for="skyview_seo_analyze_products">
                                <input type="checkbox" name="skyview_seo_settings[analyze_products]" id="skyview_seo_analyze_products" value="1" <?php checked($settings['analyze_products']); ?>>
                                <?php esc_html_e('Products', 'vidolimo-seo-auditor'); ?>
                            </label>
                            <br>
                            <label for="skyview_seo_analyze_categories">
                                <input type="checkbox" name="skyview_seo_settings[analyze_categories]" id="skyview_seo_analyze_categories" value="1" <?php checked($settings['analyze_categories']); ?>>
                                <?php esc_html_e('Categories', 'vidolimo-seo-auditor'); ?>
                            </label>
                            <br>
                            <label for="skyview_seo_analyze_tags">
                                <input type="checkbox" name="skyview_seo_settings[analyze_tags]" id="skyview_seo_analyze_tags" value="1" <?php checked($settings['analyze_tags']); ?>>
                                <?php esc_html_e('Tags', 'vidolimo-seo-auditor'); ?>
                            </label>
                        </fieldset>
                        <p class="description">
                            <?php esc_html_e('Select which content types should be included in the SEO analysis.', 'vidolimo-seo-auditor'); ?>
                        </p>
                    </td>
                </tr>
            </table>
        </div>

        <div class="skyview-seo-settings-section">
            <h2><?php esc_html_e('Database Management', 'vidolimo-seo-auditor'); ?></h2>
            <table class="form-table">
                <tr>
                    <th scope="row"><?php esc_html_e('Reset Analysis Data', 'vidolimo-seo-auditor'); ?></th>
                    <td>
                        <button type="button" class="button" id="skyview-seo-clear-data">
                            <?php esc_html_e('Clear All Analysis Data', 'vidolimo-seo-auditor'); ?>
                        </button>
                        <p class="description"><?php esc_html_e('This will reset all analysis data. Use this if you want to start fresh or if you encounter any issues.', 'vidolimo-seo-auditor'); ?></p>
                    </td>
                </tr>
            </table>
        </div>

        <?php submit_button(__('Save Settings', 'vidolimo-seo-auditor')); ?>
    </form>
</div>

<?php
} // End of skyview_seo_settings_page function

// Execute the function to display the page
skyview_seo_settings_page();
?>

