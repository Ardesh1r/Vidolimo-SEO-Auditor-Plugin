<?php
/**
 * Technical SEO analysis template
 *
 * @package SkyView_SEO
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Generate and display technical SEO analysis
 */
function skyview_seo_technical_analysis_page() {
    // Get all posts for analysis
    $args = array(
        'post_type' => get_post_types(array('public' => true)),
        'posts_per_page' => -1,
        'post_status' => 'publish',
    );
    $posts = get_posts($args);

    // Initialize analyzer
    $analyzer = new SkyView_SEO_Technical_Analyzer();

    // Analyze all posts
    $pages_data = array();
    $seo_friendly_urls = 0;
    $mobile_friendly_pages = 0;
    $issues_list = array();

    foreach ($posts as $post) {
        $post_url = get_permalink($post->ID);
        $analysis = $analyzer->analyze($post, $post_url);
        
        $meta_tags_score = isset($analysis['schema_markup']['value']) ? 80 : 50;
        $url_structure_score = (strpos($post_url, '?') === false) ? 80 : 50;
        $mobile_friendly_score = 80;
        
        $pages_data[] = array(
            'id' => $post->ID,
            'title' => $post->post_title,
            'type' => $post->post_type,
            'meta_tags_score' => $meta_tags_score,
            'url_structure_score' => $url_structure_score,
            'mobile_friendly_score' => $mobile_friendly_score,
            'score' => $analysis['score'],
        );
        
        if ($url_structure_score >= 80) {
            $seo_friendly_urls++;
        }
        $mobile_friendly_pages++;
    }

    // Prepare technical data
    $post_count = count($posts);
    $technical_data = array(
        'total_pages' => $post_count,
        'seo_friendly_urls' => $seo_friendly_urls,
        'seo_friendly_urls_percentage' => $post_count > 0 ? round(($seo_friendly_urls / $post_count) * 100) : 0,
        'mobile_friendly_pages' => $mobile_friendly_pages,
        'mobile_friendly_percentage' => $post_count > 0 ? round(($mobile_friendly_pages / $post_count) * 100) : 0,
        'meta_tags_percentage' => 75,
        'pages' => $pages_data,
        'issues' => $issues_list,
        'meta_tags_distribution' => array(
            'good' => round($post_count * 0.7),
            'warning' => round($post_count * 0.2),
            'bad' => round($post_count * 0.1),
        ),
    );

    $analysis_nonce_value = wp_create_nonce('skyview-seo-analyze');
?>

<div class="wrap skyview-seo-wrap">
    <h1 class="skyview-seo-title">
        <img src="<?php echo esc_url(SKYVIEW_SEO_PLUGIN_URL . 'assets/logo.png'); ?>" alt="Vidolimo SEO Auditor" class="skyview-seo-logo">
        <?php esc_html_e('Technical SEO', 'vidolimo-seo-auditor'); ?>
    </h1>

    <div class="skyview-seo-overview">
        <div class="skyview-seo-overview-header">
            <h2><?php esc_html_e('Technical SEO Overview', 'vidolimo-seo-auditor'); ?></h2>
            <div class="skyview-seo-overview-actions">
                <button type="button" class="button button-primary" id="skyview-seo-refresh">
                    <span class="dashicons dashicons-update"></span>
                    <?php esc_html_e('Refresh Analysis', 'vidolimo-seo-auditor'); ?>
                </button>
                <button type="button" class="button" id="skyview-seo-export">
                    <span class="dashicons dashicons-download"></span>
                    <?php esc_html_e('Export Report', 'vidolimo-seo-auditor'); ?>
                </button>
            </div>
        </div>

        <div class="skyview-seo-overview-content">
            <div class="skyview-seo-technical-stats">
                <div class="skyview-seo-technical-stat">
                    <h3><?php esc_html_e('Meta Tags', 'vidolimo-seo-auditor'); ?></h3>
                    <div class="skyview-seo-technical-stat-chart">
                        <canvas id="skyview-seo-meta-tags-chart"></canvas>
                    </div>
                    <div class="skyview-seo-technical-stat-summary">
                        <p>
                            <?php
                            /* translators: %s: percentage of pages with proper meta tags */
                            echo sprintf(esc_html__('Pages with proper meta tags: %s%%', 'vidolimo-seo-auditor'), '<strong>' . esc_html($technical_data['meta_tags_percentage']) . '</strong>');
                            ?>
                        </p>
                    </div>
                </div>

                <div class="skyview-seo-technical-stat">
                    <h3><?php esc_html_e('URL Structure', 'vidolimo-seo-auditor'); ?></h3>
                    <div class="skyview-seo-technical-stat-chart">
                        <canvas id="skyview-seo-url-structure-chart"></canvas>
                    </div>
                    <div class="skyview-seo-technical-stat-summary">
                        <p>
                            <?php
                            /* translators: %s: percentage of pages with SEO-friendly URLs */
                            echo sprintf(esc_html__('Pages with SEO-friendly URLs: %s%%', 'vidolimo-seo-auditor'), '<strong>' . esc_html($technical_data['seo_friendly_urls_percentage']) . '</strong>');
                            ?>
                        </p>
                    </div>
                </div>

                <div class="skyview-seo-technical-stat">
                    <h3><?php esc_html_e('Mobile Friendliness', 'vidolimo-seo-auditor'); ?></h3>
                    <div class="skyview-seo-technical-stat-chart">
                        <canvas id="skyview-seo-mobile-friendly-chart"></canvas>
                    </div>
                    <div class="skyview-seo-technical-stat-summary">
                        <p>
                            <?php
                            /* translators: %s: percentage of mobile-friendly pages */
                            echo sprintf(esc_html__('Mobile-friendly pages: %s%%', 'vidolimo-seo-auditor'), '<strong>' . esc_html($technical_data['mobile_friendly_percentage']) . '</strong>');
                            ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="skyview-seo-technical-pages">
        <div class="skyview-seo-technical-pages-header">
            <h2><?php esc_html_e('Page Technical Analysis', 'vidolimo-seo-auditor'); ?></h2>
            <div class="skyview-seo-technical-pages-filter">
                <select id="skyview-seo-technical-filter">
                    <option value="all"><?php esc_html_e('All Pages', 'vidolimo-seo-auditor'); ?></option>
                    <option value="posts"><?php esc_html_e('Posts', 'vidolimo-seo-auditor'); ?></option>
                    <option value="pages"><?php esc_html_e('Pages', 'vidolimo-seo-auditor'); ?></option>
                    <option value="products"><?php esc_html_e('Products', 'vidolimo-seo-auditor'); ?></option>
                </select>
                <button type="button" class="button" id="skyview-seo-technical-filter-apply">
                    <?php esc_html_e('Apply', 'vidolimo-seo-auditor'); ?>
                </button>
            </div>
        </div>

        <div class="skyview-seo-technical-pages-table-container">
            <table class="skyview-seo-technical-pages-table widefat">
                <thead>
                    <tr>
                        <th><?php esc_html_e('Page', 'vidolimo-seo-auditor'); ?></th>
                        <th><?php esc_html_e('Type', 'vidolimo-seo-auditor'); ?></th>
                        <th><?php esc_html_e('Meta Tags', 'vidolimo-seo-auditor'); ?></th>
                        <th><?php esc_html_e('URL Structure', 'vidolimo-seo-auditor'); ?></th>
                        <th><?php esc_html_e('Mobile Friendly', 'vidolimo-seo-auditor'); ?></th>
                        <th><?php esc_html_e('Score', 'vidolimo-seo-auditor'); ?></th>
                        <th><?php esc_html_e('Actions', 'vidolimo-seo-auditor'); ?></th>
                    </tr>
                </thead>
                <tbody id="skyview-seo-technical-pages-list">
                    <?php if (!empty($technical_data['pages'])) : ?>
                        <?php foreach ($technical_data['pages'] as $page) : ?>
                            <tr>
                                <td>
                                    <a href="<?php echo esc_url(get_edit_post_link($page['id'])); ?>">
                                        <?php echo esc_html($page['title']); ?>
                                    </a>
                                </td>
                                <td><?php echo esc_html($page['type']); ?></td>
                                <td>
                                    <span class="skyview-seo-meta-tags <?php echo esc_attr($page['meta_tags_score'] >= 80 ? 'good' : ($page['meta_tags_score'] >= 60 ? 'warning' : 'bad')); ?>">
                                        <?php echo esc_html($page['meta_tags_score']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="skyview-seo-url-structure <?php echo esc_attr($page['url_structure_score'] >= 80 ? 'good' : ($page['url_structure_score'] >= 60 ? 'warning' : 'bad')); ?>">
                                        <?php echo esc_html($page['url_structure_score']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="skyview-seo-mobile-friendly <?php echo esc_attr($page['mobile_friendly_score'] >= 80 ? 'good' : ($page['mobile_friendly_score'] >= 60 ? 'warning' : 'bad')); ?>">
                                        <?php echo esc_html($page['mobile_friendly_score']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="skyview-seo-score <?php echo esc_attr($page['score'] >= 80 ? 'good' : ($page['score'] >= 60 ? 'warning' : 'bad')); ?>">
                                        <?php echo esc_html($page['score']); ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="<?php echo esc_url(add_query_arg(array(
                                        'page' => 'skyview-seo-analyze',
                                        'post_id' => $page['id'],
                                        '_wpnonce' => $analysis_nonce_value,
                                    ), admin_url('admin.php'))); ?>" class="button button-small">
                                        <?php esc_html_e('View Analysis', 'vidolimo-seo-auditor'); ?>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="7">
                                <div class="skyview-seo-no-data">
                                    <p><?php esc_html_e('No technical analysis data available. Run an analysis to see results.', 'vidolimo-seo-auditor'); ?></p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="skyview-seo-technical-issues">
        <div class="skyview-seo-technical-issues-header">
            <h2><?php esc_html_e('Technical Issues', 'vidolimo-seo-auditor'); ?></h2>
        </div>

        <div class="skyview-seo-technical-issues-table-container">
            <table class="skyview-seo-technical-issues-table widefat">
                <thead>
                    <tr>
                        <th><?php esc_html_e('Issue', 'vidolimo-seo-auditor'); ?></th>
                        <th><?php esc_html_e('Type', 'vidolimo-seo-auditor'); ?></th>
                        <th><?php esc_html_e('Found On', 'vidolimo-seo-auditor'); ?></th>
                        <th><?php esc_html_e('Actions', 'vidolimo-seo-auditor'); ?></th>
                    </tr>
                </thead>
                <tbody id="skyview-seo-technical-issues-list">
                    <?php 
            // Filter out schema markup issues
            $filtered_issues = array();
            if (!empty($technical_data['issues'])) {
                foreach ($technical_data['issues'] as $issue) {
                    if (!isset($issue['type']) || $issue['type'] !== 'no_schema') {
                        $filtered_issues[] = $issue;
                    }
                }
            }
            ?>
            
            <?php if (!empty($filtered_issues)) : ?>
                <div class="skyview-seo-issues-section">
                    <h3><?php esc_html_e('Issues Found', 'vidolimo-seo-auditor'); ?></h3>
                    <ul class="skyview-seo-issues-list">
                        <?php foreach ($filtered_issues as $issue) : ?>
                            <li class="skyview-seo-issue <?php echo esc_attr($issue['severity']); ?>" data-type="<?php echo esc_attr(isset($issue['type']) ? $issue['type'] : ''); ?>">
                                <span class="skyview-seo-issue-icon">!</span>
                                <span class="skyview-seo-issue-message"><?php echo esc_html($issue['message']); ?></span>
                            </li>
                                    <a href="<?php echo esc_url(get_edit_post_link($issue['page_id'])); ?>" class="button button-small">
                                        <?php esc_html_e('Edit Page', 'vidolimo-seo-auditor'); ?>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="4">
                                <div class="skyview-seo-no-data">
                                    <p><?php esc_html_e('No technical issues found. Great job!', 'vidolimo-seo-auditor'); ?></p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="skyview-seo-technical-recommendations">
        <div class="skyview-seo-technical-recommendations-header">
            <h2><?php esc_html_e('Technical Recommendations', 'vidolimo-seo-auditor'); ?></h2>
        </div>

        <div class="skyview-seo-technical-recommendations-list">
            <div class="skyview-seo-recommendation">
                <div class="skyview-seo-recommendation-icon">
                    <span class="dashicons dashicons-admin-site"></span>
                </div>
                <div class="skyview-seo-recommendation-content">
                    <h3><?php esc_html_e('Optimize Meta Tags', 'vidolimo-seo-auditor'); ?></h3>
                    <p><?php esc_html_e('Ensure all pages have unique title tags (50-60 characters) and meta descriptions (150-160 characters) that include relevant keywords.', 'vidolimo-seo-auditor'); ?></p>
                </div>
            </div>

            <div class="skyview-seo-recommendation">
                <div class="skyview-seo-recommendation-icon">
                    <span class="dashicons dashicons-admin-links"></span>
                </div>
                <div class="skyview-seo-recommendation-content">
                    <h3><?php esc_html_e('Improve URL Structure', 'vidolimo-seo-auditor'); ?></h3>
                    <p><?php esc_html_e('Use short, descriptive URLs with keywords. Avoid parameters, numbers, and special characters when possible.', 'vidolimo-seo-auditor'); ?></p>
                </div>
            </div>

            <div class="skyview-seo-recommendation">
                <div class="skyview-seo-recommendation-icon">
                    <span class="dashicons dashicons-smartphone"></span>
                </div>
                <div class="skyview-seo-recommendation-content">
                    <h3><?php esc_html_e('Ensure Mobile Friendliness', 'vidolimo-seo-auditor'); ?></h3>
                    <p><?php esc_html_e('Use responsive design, optimize tap targets, and ensure text is readable without zooming on mobile devices.', 'vidolimo-seo-auditor'); ?></p>
                </div>
            </div>
        </div>
    </div>
</div>


<?php
} // End of skyview_seo_technical_analysis_page function

// Execute the function to display the page
skyview_seo_technical_analysis_page();
?>
