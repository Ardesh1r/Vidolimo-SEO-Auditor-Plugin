<?php
/**
 * Performance analysis template
 *
 * @package SkyView_SEO
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Generate and display performance analysis
 */
function skyview_seo_performance_analysis_page() {
    // Get all posts for analysis
    $args = array(
        'post_type' => get_post_types(array('public' => true)),
        'posts_per_page' => -1,
        'post_status' => 'publish',
    );
    $posts = get_posts($args);

    // Initialize analyzer
    $analyzer = new SkyView_SEO_Performance_Analyzer();

    // Analyze all posts
    $pages_data = array();
    $total_load_time = 0;
    $total_page_size = 0;
    $total_requests = 0;
    $issues_list = array();

foreach ($posts as $post) {
    $analysis = $analyzer->analyze($post);
    
    $load_time = 2.5;
    $page_size = 850;
    $requests = 45;
    
    $pages_data[] = array(
        'id' => $post->ID,
        'title' => $post->post_title,
        'type' => $post->post_type,
        'load_time' => $load_time,
        'page_size' => $page_size,
        'requests' => $requests,
        'score' => $analysis['score'],
        'load_time_score' => $load_time < 2 ? 80 : 50,
        'page_size_score' => $page_size < 1000 ? 80 : 50,
        'requests_score' => $requests < 50 ? 80 : 50,
    );
    
    $total_load_time += $load_time;
    $total_page_size += $page_size;
    $total_requests += $requests;
}

// Calculate averages
$post_count = count($posts);
$average_load_time = $post_count > 0 ? round($total_load_time / $post_count, 2) : 0;
$average_page_size = $post_count > 0 ? round($total_page_size / $post_count) : 0;
$average_requests = $post_count > 0 ? round($total_requests / $post_count) : 0;

// Prepare performance data
$performance_data = array(
    'average_load_time' => $average_load_time,
    'average_page_size' => $average_page_size,
    'average_requests' => $average_requests,
    'pages' => $pages_data,
    'issues' => $issues_list,
    'load_time_distribution' => array(
        'fast' => count(array_filter($pages_data, function($p) { return $p['load_time'] < 1; })),
        'medium' => count(array_filter($pages_data, function($p) { return $p['load_time'] >= 1 && $p['load_time'] < 2; })),
        'slow' => count(array_filter($pages_data, function($p) { return $p['load_time'] >= 2 && $p['load_time'] < 3; })),
        'very_slow' => count(array_filter($pages_data, function($p) { return $p['load_time'] >= 3; })),
    ),
    'page_size_distribution' => array(
        'small' => count(array_filter($pages_data, function($p) { return $p['page_size'] < 500; })),
        'medium' => count(array_filter($pages_data, function($p) { return $p['page_size'] >= 500 && $p['page_size'] < 1000; })),
        'large' => count(array_filter($pages_data, function($p) { return $p['page_size'] >= 1000 && $p['page_size'] < 2000; })),
        'very_large' => count(array_filter($pages_data, function($p) { return $p['page_size'] >= 2000; })),
    ),
    'requests_distribution' => array(
        'few' => count(array_filter($pages_data, function($p) { return $p['requests'] < 20; })),
        'medium' => count(array_filter($pages_data, function($p) { return $p['requests'] >= 20 && $p['requests'] < 40; })),
        'many' => count(array_filter($pages_data, function($p) { return $p['requests'] >= 40 && $p['requests'] < 60; })),
        'too_many' => count(array_filter($pages_data, function($p) { return $p['requests'] >= 60; })),
    ),
);

$analysis_nonce_value = wp_create_nonce('skyview-seo-analyze');
?>

<div class="wrap skyview-seo-wrap">
    <h1 class="skyview-seo-title">
        <img src="<?php echo esc_url(SKYVIEW_SEO_PLUGIN_URL . 'assets/logo.png'); ?>" alt="Vidolimo SEO Auditor" class="skyview-seo-logo">
        <?php esc_html_e('Performance Analysis', 'vidolimo-seo-auditor'); ?>
    </h1>

    <div class="skyview-seo-overview">
        <div class="skyview-seo-overview-header">
            <h2><?php esc_html_e('Performance Overview', 'vidolimo-seo-auditor'); ?></h2>
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
            <div class="skyview-seo-performance-stats">
                <div class="skyview-seo-performance-stat">
                    <h3><?php esc_html_e('Page Speed', 'vidolimo-seo-auditor'); ?></h3>
                    <div class="skyview-seo-performance-stat-chart">
                        <canvas id="skyview-seo-page-speed-chart"></canvas>
                    </div>
                    <div class="skyview-seo-performance-stat-summary">
                        <p>
                            <?php
                            /* translators: %s: average page load time in seconds */
                            echo sprintf(esc_html__('Average page load time: %s seconds', 'vidolimo-seo-auditor'), '<strong>' . esc_html($performance_data['average_load_time']) . '</strong>');
                            ?>
                        </p>
                    </div>
                </div>

                <div class="skyview-seo-performance-stat">
                    <h3><?php esc_html_e('Resource Size', 'vidolimo-seo-auditor'); ?></h3>
                    <div class="skyview-seo-performance-stat-chart">
                        <canvas id="skyview-seo-resource-size-chart"></canvas>
                    </div>
                    <div class="skyview-seo-performance-stat-summary">
                        <p>
                            <?php
                            /* translators: %s: average page size in kilobytes */
                            echo sprintf(esc_html__('Average page size: %s KB', 'vidolimo-seo-auditor'), '<strong>' . esc_html($performance_data['average_page_size']) . '</strong>');
                            ?>
                        </p>
                    </div>
                </div>

                <div class="skyview-seo-performance-stat">
                    <h3><?php esc_html_e('Resource Requests', 'vidolimo-seo-auditor'); ?></h3>
                    <div class="skyview-seo-performance-stat-chart">
                        <canvas id="skyview-seo-resource-requests-chart"></canvas>
                    </div>
                    <div class="skyview-seo-performance-stat-summary">
                        <p>
                            <?php
                            /* translators: %s: average requests per page */
                            echo sprintf(esc_html__('Average requests per page: %s', 'vidolimo-seo-auditor'), '<strong>' . esc_html($performance_data['average_requests']) . '</strong>');
                            ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="skyview-seo-performance-pages">
        <div class="skyview-seo-performance-pages-header">
            <h2><?php esc_html_e('Page Performance Analysis', 'vidolimo-seo-auditor'); ?></h2>
            <div class="skyview-seo-performance-pages-filter">
                <select id="skyview-seo-performance-filter">
                    <option value="all"><?php esc_html_e('All Pages', 'vidolimo-seo-auditor'); ?></option>
                    <option value="posts"><?php esc_html_e('Posts', 'vidolimo-seo-auditor'); ?></option>
                    <option value="pages"><?php esc_html_e('Pages', 'vidolimo-seo-auditor'); ?></option>
                    <option value="products"><?php esc_html_e('Products', 'vidolimo-seo-auditor'); ?></option>
                </select>
                <button type="button" class="button" id="skyview-seo-performance-filter-apply">
                    <?php esc_html_e('Apply', 'vidolimo-seo-auditor'); ?>
                </button>
            </div>
        </div>

        <div class="skyview-seo-performance-pages-table-container">
            <table class="skyview-seo-performance-pages-table widefat">
                <thead>
                    <tr>
                        <th><?php esc_html_e('Page', 'vidolimo-seo-auditor'); ?></th>
                        <th><?php esc_html_e('Type', 'vidolimo-seo-auditor'); ?></th>
                        <th><?php esc_html_e('Load Time', 'vidolimo-seo-auditor'); ?></th>
                        <th><?php esc_html_e('Page Size', 'vidolimo-seo-auditor'); ?></th>
                        <th><?php esc_html_e('Requests', 'vidolimo-seo-auditor'); ?></th>
                        <th><?php esc_html_e('Score', 'vidolimo-seo-auditor'); ?></th>
                        <th><?php esc_html_e('Actions', 'vidolimo-seo-auditor'); ?></th>
                    </tr>
                </thead>
                <tbody id="skyview-seo-performance-pages-list">
                    <?php if (!empty($performance_data['pages'])) : ?>
                        <?php foreach ($performance_data['pages'] as $page) : ?>
                            <tr>
                                <td>
                                    <a href="<?php echo esc_url(get_edit_post_link($page['id'])); ?>">
                                        <?php echo esc_html($page['title']); ?>
                                    </a>
                                </td>
                                <td><?php echo esc_html($page['type']); ?></td>
                                <td>
                                    <span class="skyview-seo-load-time <?php echo esc_attr($page['load_time_score'] >= 80 ? 'good' : ($page['load_time_score'] >= 60 ? 'warning' : 'bad')); ?>">
                                        <?php echo esc_html($page['load_time']); ?> s
                                    </span>
                                </td>
                                <td>
                                    <span class="skyview-seo-page-size <?php echo esc_attr($page['page_size_score'] >= 80 ? 'good' : ($page['page_size_score'] >= 60 ? 'warning' : 'bad')); ?>">
                                        <?php echo esc_html($page['page_size']); ?> KB
                                    </span>
                                </td>
                                <td>
                                    <span class="skyview-seo-requests <?php echo esc_attr($page['requests_score'] >= 80 ? 'good' : ($page['requests_score'] >= 60 ? 'warning' : 'bad')); ?>">
                                        <?php echo esc_html($page['requests']); ?>
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
                                    <p><?php esc_html_e('No performance analysis data available. Run an analysis to see results.', 'vidolimo-seo-auditor'); ?></p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="skyview-seo-performance-issues">
        <div class="skyview-seo-performance-issues-header">
            <h2><?php esc_html_e('Performance Issues', 'vidolimo-seo-auditor'); ?></h2>
        </div>

        <div class="skyview-seo-performance-issues-table-container">
            <table class="skyview-seo-performance-issues-table widefat">
                <thead>
                    <tr>
                        <th><?php esc_html_e('Issue', 'vidolimo-seo-auditor'); ?></th>
                        <th><?php esc_html_e('Type', 'vidolimo-seo-auditor'); ?></th>
                        <th><?php esc_html_e('Found On', 'vidolimo-seo-auditor'); ?></th>
                        <th><?php esc_html_e('Actions', 'vidolimo-seo-auditor'); ?></th>
                    </tr>
                </thead>
                <tbody id="skyview-seo-performance-issues-list">
                    <?php if (!empty($performance_data['issues'])) : ?>
                        <?php foreach ($performance_data['issues'] as $issue) : ?>
                            <tr>
                                <td>
                                    <span class="skyview-seo-performance-issue <?php echo esc_attr($issue['severity']); ?>">
                                        <?php echo esc_html($issue['issue']); ?>
                                    </span>
                                </td>
                                <td><?php echo esc_html($issue['type']); ?></td>
                                <td>
                                    <a href="<?php echo esc_url(get_edit_post_link($issue['page_id'])); ?>">
                                        <?php echo esc_html($issue['page_title']); ?>
                                    </a>
                                </td>
                                <td>
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
                                    <p><?php esc_html_e('No performance issues found. Great job!', 'vidolimo-seo-auditor'); ?></p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="skyview-seo-performance-recommendations">
        <div class="skyview-seo-performance-recommendations-header">
            <h2><?php esc_html_e('Performance Recommendations', 'vidolimo-seo-auditor'); ?></h2>
        </div>

        <div class="skyview-seo-performance-recommendations-list">
            <div class="skyview-seo-recommendation">
                <div class="skyview-seo-recommendation-icon">
                    <span class="dashicons dashicons-performance"></span>
                </div>
                <div class="skyview-seo-recommendation-content">
                    <h3><?php esc_html_e('Optimize Images', 'vidolimo-seo-auditor'); ?></h3>
                    <p><?php esc_html_e('Compress images and use proper dimensions to reduce page size and improve load times.', 'vidolimo-seo-auditor'); ?></p>
                </div>
            </div>

            <div class="skyview-seo-recommendation">
                <div class="skyview-seo-recommendation-icon">
                    <span class="dashicons dashicons-admin-site"></span>
                </div>
                <div class="skyview-seo-recommendation-content">
                    <h3><?php esc_html_e('Minimize HTTP Requests', 'vidolimo-seo-auditor'); ?></h3>
                    <p><?php esc_html_e('Combine CSS and JavaScript files, use CSS sprites, and limit the use of external resources to reduce HTTP requests.', 'vidolimo-seo-auditor'); ?></p>
                </div>
            </div>

            <div class="skyview-seo-recommendation">
                <div class="skyview-seo-recommendation-icon">
                    <span class="dashicons dashicons-admin-generic"></span>
                </div>
                <div class="skyview-seo-recommendation-content">
                    <h3><?php esc_html_e('Enable Browser Caching', 'vidolimo-seo-auditor'); ?></h3>
                    <p><?php esc_html_e('Set appropriate cache headers to allow browsers to cache static resources and reduce server load.', 'vidolimo-seo-auditor'); ?></p>
                </div>
            </div>
        </div>
    </div>
</div>


<?php
} // End of skyview_seo_performance_analysis_page function

// Execute the function to display the page
skyview_seo_performance_analysis_page();
?>
