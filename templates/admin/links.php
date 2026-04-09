<?php
/**
 * Links analysis template
 *
 * @package SkyView_SEO
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Generate and display link analysis
 */
function skyview_seo_links_analysis_page() {
    // Get all posts for analysis
    $args = array(
        'post_type' => get_post_types(array('public' => true)),
        'posts_per_page' => -1,
        'post_status' => 'publish',
    );
    $posts = get_posts($args);

    // Initialize analyzer
    $analyzer = new SkyView_SEO_Link_Analyzer();

    // Analyze all posts
    $pages_data = array();
    $total_internal_links = 0;
    $total_external_links = 0;
    $total_broken_links = 0;
    $broken_links_list = array();

foreach ($posts as $post) {
    $analysis = $analyzer->analyze($post);
    
    $internal_count = isset($analysis['internal_links']['count']) ? $analysis['internal_links']['count'] : 0;
    $external_count = isset($analysis['external_links']['count']) ? $analysis['external_links']['count'] : 0;
    $broken_count = isset($analysis['broken_links']['count']) ? $analysis['broken_links']['count'] : 0;
    
    $pages_data[] = array(
        'id' => $post->ID,
        'title' => $post->post_title,
        'type' => $post->post_type,
        'internal_links' => $internal_count,
        'external_links' => $external_count,
        'broken_links' => $broken_count,
        'score' => $analysis['score'],
        'internal_links_score' => $internal_count >= 3 ? 80 : 50,
        'external_links_score' => ($external_count >= 2 && $external_count <= 4) ? 80 : 50,
    );
    
    $total_internal_links += $internal_count;
    $total_external_links += $external_count;
    $total_broken_links += $broken_count;
    
    if ($broken_count > 0 && isset($analysis['broken_links']['urls'])) {
        foreach ($analysis['broken_links']['urls'] as $url) {
            $broken_links_list[] = array(
                'url' => $url,
                'page_id' => $post->ID,
                'page_title' => $post->post_title,
                'status' => '404',
            );
        }
    }
}

// Calculate averages
$post_count = count($posts);
$average_internal_links = $post_count > 0 ? round($total_internal_links / $post_count) : 0;
$average_external_links = $post_count > 0 ? round($total_external_links / $post_count) : 0;

// Prepare links data
$links_data = array(
    'total_internal_links' => $total_internal_links,
    'average_internal_links' => $average_internal_links,
    'total_external_links' => $total_external_links,
    'average_external_links' => $average_external_links,
    'total_broken_links' => $total_broken_links,
    'pages_with_broken_links' => count(array_filter($pages_data, function($p) { return $p['broken_links'] > 0; })),
    'total_pages' => $post_count,
    'pages' => $pages_data,
    'broken_links' => $broken_links_list,
    'internal_links_distribution' => array(
        'none' => count(array_filter($pages_data, function($p) { return $p['internal_links'] == 0; })),
        'few' => count(array_filter($pages_data, function($p) { return $p['internal_links'] > 0 && $p['internal_links'] <= 3; })),
        'optimal' => count(array_filter($pages_data, function($p) { return $p['internal_links'] > 3 && $p['internal_links'] <= 10; })),
        'many' => count(array_filter($pages_data, function($p) { return $p['internal_links'] > 10; })),
    ),
    'external_links_distribution' => array(
        'none' => count(array_filter($pages_data, function($p) { return $p['external_links'] == 0; })),
        'few' => count(array_filter($pages_data, function($p) { return $p['external_links'] > 0 && $p['external_links'] <= 2; })),
        'optimal' => count(array_filter($pages_data, function($p) { return $p['external_links'] > 2 && $p['external_links'] <= 5; })),
        'many' => count(array_filter($pages_data, function($p) { return $p['external_links'] > 5; })),
    ),
);

$analysis_nonce_value = wp_create_nonce('skyview-seo-analyze');
?>

<div class="wrap skyview-seo-wrap">
    <h1 class="skyview-seo-title">
        <img src="<?php echo esc_url(SKYVIEW_SEO_PLUGIN_URL . 'assets/logo.png'); ?>" alt="Vidolimo SEO Auditor" class="skyview-seo-logo">
        <?php esc_html_e('Link Analysis', 'vidolimo-seo-auditor'); ?>
    </h1>

    <div class="skyview-seo-overview">
        <div class="skyview-seo-overview-header">
            <h2><?php esc_html_e('Link Analysis Overview', 'vidolimo-seo-auditor'); ?></h2>
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
            <div class="skyview-seo-links-stats">
                <div class="skyview-seo-links-stat">
                    <h3><?php esc_html_e('Internal Links', 'vidolimo-seo-auditor'); ?></h3>
                    <div class="skyview-seo-links-stat-chart">
                        <canvas id="skyview-seo-internal-links-chart"></canvas>
                    </div>
                    <div class="skyview-seo-links-stat-summary">
                        <p>
                            <?php
                            /* translators: %s: total internal links count */
                            echo sprintf(esc_html__('Total internal links: %s', 'vidolimo-seo-auditor'), '<strong>' . esc_html($links_data['total_internal_links']) . '</strong>');
                            ?>
                        </p>
                        <p>
                            <?php
                            /* translators: %s: average internal links per page */
                            echo sprintf(esc_html__('Average internal links per page: %s', 'vidolimo-seo-auditor'), '<strong>' . esc_html($links_data['average_internal_links']) . '</strong>');
                            ?>
                        </p>
                    </div>
                </div>

                <div class="skyview-seo-links-stat">
                    <h3><?php esc_html_e('External Links', 'vidolimo-seo-auditor'); ?></h3>
                    <div class="skyview-seo-links-stat-chart">
                        <canvas id="skyview-seo-external-links-chart"></canvas>
                    </div>
                    <div class="skyview-seo-links-stat-summary">
                        <p>
                            <?php
                            /* translators: %s: total external links count */
                            echo sprintf(esc_html__('Total external links: %s', 'vidolimo-seo-auditor'), '<strong>' . esc_html($links_data['total_external_links']) . '</strong>');
                            ?>
                        </p>
                        <p>
                            <?php
                            /* translators: %s: average external links per page */
                            echo sprintf(esc_html__('Average external links per page: %s', 'vidolimo-seo-auditor'), '<strong>' . esc_html($links_data['average_external_links']) . '</strong>');
                            ?>
                        </p>
                    </div>
                </div>

                <div class="skyview-seo-links-stat">
                    <h3><?php esc_html_e('Broken Links', 'vidolimo-seo-auditor'); ?></h3>
                    <div class="skyview-seo-links-stat-chart">
                        <canvas id="skyview-seo-broken-links-chart"></canvas>
                    </div>
                    <div class="skyview-seo-links-stat-summary">
                        <p>
                            <?php
                            /* translators: %s: total broken links count */
                            echo sprintf(esc_html__('Total broken links: %s', 'vidolimo-seo-auditor'), '<strong>' . esc_html($links_data['total_broken_links']) . '</strong>');
                            ?>
                        </p>
                        <p>
                            <?php
                            /* translators: %s: number of pages with broken links */
                            echo sprintf(esc_html__('Pages with broken links: %s', 'vidolimo-seo-auditor'), '<strong>' . esc_html($links_data['pages_with_broken_links']) . '</strong>');
                            ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="skyview-seo-links-pages">
        <div class="skyview-seo-links-pages-header">
            <h2><?php esc_html_e('Page Link Analysis', 'vidolimo-seo-auditor'); ?></h2>
            <div class="skyview-seo-links-pages-filter">
                <select id="skyview-seo-links-filter">
                    <option value="all"><?php esc_html_e('All Pages', 'vidolimo-seo-auditor'); ?></option>
                    <option value="posts"><?php esc_html_e('Posts', 'vidolimo-seo-auditor'); ?></option>
                    <option value="pages"><?php esc_html_e('Pages', 'vidolimo-seo-auditor'); ?></option>
                    <option value="products"><?php esc_html_e('Products', 'vidolimo-seo-auditor'); ?></option>
                </select>
                <button type="button" class="button" id="skyview-seo-links-filter-apply">
                    <?php esc_html_e('Apply', 'vidolimo-seo-auditor'); ?>
                </button>
            </div>
        </div>

        <div class="skyview-seo-links-pages-table-container">
            <table class="skyview-seo-links-pages-table widefat">
                <thead>
                    <tr>
                        <th><?php esc_html_e('Page', 'vidolimo-seo-auditor'); ?></th>
                        <th><?php esc_html_e('Type', 'vidolimo-seo-auditor'); ?></th>
                        <th><?php esc_html_e('Internal Links', 'vidolimo-seo-auditor'); ?></th>
                        <th><?php esc_html_e('External Links', 'vidolimo-seo-auditor'); ?></th>
                        <th><?php esc_html_e('Broken Links', 'vidolimo-seo-auditor'); ?></th>
                        <th><?php esc_html_e('Score', 'vidolimo-seo-auditor'); ?></th>
                        <th><?php esc_html_e('Actions', 'vidolimo-seo-auditor'); ?></th>
                    </tr>
                </thead>
                <tbody id="skyview-seo-links-pages-list">
                    <?php if (!empty($links_data['pages'])) : ?>
                        <?php foreach ($links_data['pages'] as $page) : ?>
                            <tr>
                                <td>
                                    <a href="<?php echo esc_url(get_edit_post_link($page['id'])); ?>">
                                        <?php echo esc_html($page['title']); ?>
                                    </a>
                                </td>
                                <td><?php echo esc_html($page['type']); ?></td>
                                <td>
                                    <span class="skyview-seo-internal-links <?php echo esc_attr($page['internal_links_score'] >= 80 ? 'good' : ($page['internal_links_score'] >= 60 ? 'warning' : 'bad')); ?>">
                                        <?php echo esc_html($page['internal_links']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="skyview-seo-external-links <?php echo esc_attr($page['external_links_score'] >= 80 ? 'good' : ($page['external_links_score'] >= 60 ? 'warning' : 'bad')); ?>">
                                        <?php echo esc_html($page['external_links']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="skyview-seo-broken-links <?php echo esc_attr($page['broken_links'] > 0 ? 'bad' : 'good'); ?>">
                                        <?php echo esc_html($page['broken_links']); ?>
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
                                    <p><?php esc_html_e('No link analysis data available. Run an analysis to see results.', 'vidolimo-seo-auditor'); ?></p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="skyview-seo-broken-links">
        <div class="skyview-seo-broken-links-header">
            <h2><?php esc_html_e('Broken Links', 'vidolimo-seo-auditor'); ?></h2>
        </div>

        <div class="skyview-seo-broken-links-table-container">
            <table class="skyview-seo-broken-links-table widefat">
                <thead>
                    <tr>
                        <th><?php esc_html_e('URL', 'vidolimo-seo-auditor'); ?></th>
                        <th><?php esc_html_e('Found On', 'vidolimo-seo-auditor'); ?></th>
                        <th><?php esc_html_e('Status', 'vidolimo-seo-auditor'); ?></th>
                        <th><?php esc_html_e('Actions', 'vidolimo-seo-auditor'); ?></th>
                    </tr>
                </thead>
                <tbody id="skyview-seo-broken-links-list">
                    <?php if (!empty($links_data['broken_links'])) : ?>
                        <?php foreach ($links_data['broken_links'] as $link) : ?>
                            <tr>
                                <td>
                                    <a href="<?php echo esc_url($link['url']); ?>" target="_blank" rel="nofollow">
                                        <?php echo esc_html($link['url']); ?>
                                    </a>
                                </td>
                                <td>
                                    <a href="<?php echo esc_url(get_edit_post_link($link['page_id'])); ?>">
                                        <?php echo esc_html($link['page_title']); ?>
                                    </a>
                                </td>
                                <td>
                                    <span class="skyview-seo-link-status bad">
                                        <?php echo esc_html($link['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="<?php echo esc_url(get_edit_post_link($link['page_id'])); ?>" class="button button-small">
                                        <?php esc_html_e('Edit Page', 'vidolimo-seo-auditor'); ?>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="4">
                                <div class="skyview-seo-no-data">
                                    <p><?php esc_html_e('No broken links found. Great job!', 'vidolimo-seo-auditor'); ?></p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="skyview-seo-links-recommendations">
        <div class="skyview-seo-links-recommendations-header">
            <h2><?php esc_html_e('Link Recommendations', 'vidolimo-seo-auditor'); ?></h2>
        </div>

        <div class="skyview-seo-links-recommendations-list">
            <div class="skyview-seo-recommendation">
                <div class="skyview-seo-recommendation-icon">
                    <span class="dashicons dashicons-admin-links"></span>
                </div>
                <div class="skyview-seo-recommendation-content">
                    <h3><?php esc_html_e('Add Internal Links', 'vidolimo-seo-auditor'); ?></h3>
                    <p><?php esc_html_e('Aim for at least 3-5 internal links per page to improve site structure and help search engines discover your content.', 'vidolimo-seo-auditor'); ?></p>
                </div>
            </div>

            <div class="skyview-seo-recommendation">
                <div class="skyview-seo-recommendation-icon">
                    <span class="dashicons dashicons-external"></span>
                </div>
                <div class="skyview-seo-recommendation-content">
                    <h3><?php esc_html_e('Use External Links Wisely', 'vidolimo-seo-auditor'); ?></h3>
                    <p><?php esc_html_e('Link to authoritative sources to support your content, but keep external links to a reasonable number (2-4 per page).', 'vidolimo-seo-auditor'); ?></p>
                </div>
            </div>

            <div class="skyview-seo-recommendation">
                <div class="skyview-seo-recommendation-icon">
                    <span class="dashicons dashicons-warning"></span>
                </div>
                <div class="skyview-seo-recommendation-content">
                    <h3><?php esc_html_e('Fix Broken Links', 'vidolimo-seo-auditor'); ?></h3>
                    <p><?php esc_html_e('Regularly check and fix broken links to improve user experience and avoid negative SEO impact.', 'vidolimo-seo-auditor'); ?></p>
                </div>
            </div>
        </div>
    </div>
</div>


<?php
} // End of skyview_seo_links_analysis_page function

// Execute the function to display the page
skyview_seo_links_analysis_page();
?>
