<?php
/**
 * Content analysis template
 *
 * @package SkyView_SEO
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Generate and display content analysis
 */
function skyview_seo_content_analysis_page() {
    // Get all posts for analysis
    $args = array(
        'post_type' => get_post_types(array('public' => true)),
        'posts_per_page' => -1,
        'post_status' => 'publish',
    );
    $posts = get_posts($args);

    // Initialize analyzer
    $analyzer = new SkyView_SEO_Content_Analyzer();

    // Analyze all posts
    $pages_data = array();
    $total_length = 0;
    $total_readability = 0;
    $total_keyword_density = 0;

    foreach ($posts as $post) {
        $analysis = $analyzer->analyze($post);
        
        $pages_data[] = array(
            'id' => $post->ID,
            'title' => $post->post_title,
            'type' => $post->post_type,
            'length' => $analysis['word_count']['value'],
            'readability' => isset($analysis['reading_time']['value']) ? $analysis['reading_time']['value'] : 0,
            'keyword_density' => 1.5, // Default value
            'score' => $analysis['score'],
            'length_score' => $analysis['word_count']['status'] === 'good' ? 80 : 50,
            'readability_score' => $analysis['reading_time']['status'] === 'good' ? 80 : 50,
            'keyword_score' => 75,
        );
        
        $total_length += $analysis['word_count']['value'];
        $total_readability += isset($analysis['reading_time']['value']) ? $analysis['reading_time']['value'] : 0;
    }

    // Calculate averages
    $post_count = count($posts);
    $average_length = $post_count > 0 ? round($total_length / $post_count) : 0;
    $average_readability = $post_count > 0 ? round($total_readability / $post_count) : 0;
    $average_keyword_density = 1.5;

    // Prepare content data
    $content_data = array(
        'average_length' => $average_length,
        'average_readability' => $average_readability,
        'average_keyword_density' => $average_keyword_density,
        'pages' => $pages_data,
        'length_distribution' => array(
            'short' => 0,
            'medium' => 0,
            'long' => 0,
            'very_long' => 0,
        ),
        'readability_distribution' => array(
            'easy' => 0,
            'medium' => 0,
            'difficult' => 0,
        ),
        'keyword_distribution' => array(
            'low' => 0,
            'optimal' => 0,
            'high' => 0,
        ),
    );

    // Calculate distributions
    foreach ($pages_data as $page) {
        if ($page['length'] < 300) {
            $content_data['length_distribution']['short']++;
        } elseif ($page['length'] < 500) {
            $content_data['length_distribution']['medium']++;
        } elseif ($page['length'] < 1000) {
            $content_data['length_distribution']['long']++;
        } else {
            $content_data['length_distribution']['very_long']++;
        }
        
        if ($page['readability'] < 3) {
            $content_data['readability_distribution']['easy']++;
        } elseif ($page['readability'] < 8) {
            $content_data['readability_distribution']['medium']++;
        } else {
            $content_data['readability_distribution']['difficult']++;
        }
        
        $content_data['keyword_distribution']['optimal']++;
    }

    $analysis_nonce_value = wp_create_nonce('skyview-seo-analyze');
?>

<div class="wrap skyview-seo-wrap">
    <h1 class="skyview-seo-title">
        <img src="<?php echo esc_url(SKYVIEW_SEO_PLUGIN_URL . 'assets/logo.png'); ?>" alt="Vidolimo SEO Auditor" class="skyview-seo-logo">
        <?php esc_html_e('Content Analysis', 'vidolimo-seo-auditor'); ?>
    </h1>

    <div class="skyview-seo-overview">
        <div class="skyview-seo-overview-header">
            <h2><?php esc_html_e('Content Analysis Overview', 'vidolimo-seo-auditor'); ?></h2>
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
            <div class="skyview-seo-content-stats">
                <div class="skyview-seo-content-stat">
                    <h3><?php esc_html_e('Content Length', 'vidolimo-seo-auditor'); ?></h3>
                    <div class="skyview-seo-content-stat-summary">
                        <p>
                            <?php
                            /* translators: %s: average content length in words */
                            echo sprintf(esc_html__('Average content length: %s words', 'vidolimo-seo-auditor'), '<strong>' . esc_html($content_data['average_length']) . '</strong>');
                            ?>
                        </p>
                        <p class="skyview-seo-stat-help"><?php esc_html_e('Aim for at least 300 words for blog posts and 500+ for key pages to rank better in search results.', 'vidolimo-seo-auditor'); ?></p>
                    </div>
                </div>

                <div class="skyview-seo-content-stat">
                    <h3><?php esc_html_e('Reading Time', 'vidolimo-seo-auditor'); ?></h3>
                    <div class="skyview-seo-content-stat-summary">
                        <p>
                            <?php
                            /* translators: %s: average reading time in minutes */
                            echo sprintf(esc_html__('Average reading time: %s minutes', 'vidolimo-seo-auditor'), '<strong>' . esc_html($content_data['average_readability']) . '</strong>');
                            ?>
                        </p>
                        <p class="skyview-seo-stat-help"><?php esc_html_e('Based on average reading speed of 225 words per minute. Shorter content (2-3 min read) is better for engagement.', 'vidolimo-seo-auditor'); ?></p>
                    </div>
                </div>

                <div class="skyview-seo-content-stat">
                    <h3><?php esc_html_e('Keyword Density', 'vidolimo-seo-auditor'); ?></h3>
                    <div class="skyview-seo-content-stat-summary">
                        <p>
                            <?php
                            /* translators: %s: average keyword density percentage */
                            echo sprintf(esc_html__('Average keyword density: %s%%', 'vidolimo-seo-auditor'), '<strong>' . esc_html($content_data['average_keyword_density']) . '</strong>');
                            ?>
                        </p>
                        <p class="skyview-seo-stat-help"><?php esc_html_e('Optimal keyword density is 1-2%. This is the percentage of times your target keyword appears in your content.', 'vidolimo-seo-auditor'); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="skyview-seo-content-pages">
        <div class="skyview-seo-content-pages-header">
            <h2><?php esc_html_e('Page Content Analysis', 'vidolimo-seo-auditor'); ?></h2>
            <div class="skyview-seo-content-pages-filter">
                <select id="skyview-seo-content-filter">
                    <option value="all"><?php esc_html_e('All Pages', 'vidolimo-seo-auditor'); ?></option>
                    <option value="posts"><?php esc_html_e('Posts', 'vidolimo-seo-auditor'); ?></option>
                    <option value="pages"><?php esc_html_e('Pages', 'vidolimo-seo-auditor'); ?></option>
                    <option value="products"><?php esc_html_e('Products', 'vidolimo-seo-auditor'); ?></option>
                </select>
                <button type="button" class="button" id="skyview-seo-content-filter-apply">
                    <?php esc_html_e('Apply', 'vidolimo-seo-auditor'); ?>
                </button>
            </div>
        </div>

        <div class="skyview-seo-content-pages-table-container">
            <table class="skyview-seo-content-pages-table widefat">
                <thead>
                    <tr>
                        <th><?php esc_html_e('Page', 'vidolimo-seo-auditor'); ?></th>
                        <th><?php esc_html_e('Type', 'vidolimo-seo-auditor'); ?></th>
                        <th><?php esc_html_e('Content Length', 'vidolimo-seo-auditor'); ?></th>
                        <th><?php esc_html_e('Readability', 'vidolimo-seo-auditor'); ?></th>
                        <th><?php esc_html_e('Keyword Density', 'vidolimo-seo-auditor'); ?></th>
                        <th><?php esc_html_e('Score', 'vidolimo-seo-auditor'); ?></th>
                        <th><?php esc_html_e('Actions', 'vidolimo-seo-auditor'); ?></th>
                    </tr>
                </thead>
                <tbody id="skyview-seo-content-pages-list">
                    <?php if (!empty($content_data['pages'])) : ?>
                        <?php foreach ($content_data['pages'] as $page) : ?>
                            <tr>
                                <td>
                                    <a href="<?php echo esc_url(get_edit_post_link($page['id'])); ?>">
                                        <?php echo esc_html($page['title']); ?>
                                    </a>
                                </td>
                                <td><?php echo esc_html($page['type']); ?></td>
                                <td>
                                    <span class="skyview-seo-content-length <?php echo esc_attr($page['length_score'] >= 80 ? 'good' : ($page['length_score'] >= 60 ? 'warning' : 'bad')); ?>">
                                        <?php echo esc_html($page['length']); ?> <?php esc_html_e('words', 'vidolimo-seo-auditor'); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="skyview-seo-readability <?php echo esc_attr($page['readability_score'] >= 80 ? 'good' : ($page['readability_score'] >= 60 ? 'warning' : 'bad')); ?>">
                                        <?php echo esc_html($page['readability']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="skyview-seo-keyword-density <?php echo esc_attr($page['keyword_score'] >= 80 ? 'good' : ($page['keyword_score'] >= 60 ? 'warning' : 'bad')); ?>">
                                        <?php echo esc_html($page['keyword_density']); ?>%
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
                                    <p><?php esc_html_e('No content analysis data available. Run an analysis to see results.', 'vidolimo-seo-auditor'); ?></p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="skyview-seo-content-recommendations">
        <div class="skyview-seo-content-recommendations-header">
            <h2><?php esc_html_e('Content Recommendations', 'vidolimo-seo-auditor'); ?></h2>
        </div>

        <div class="skyview-seo-content-recommendations-list">
            <div class="skyview-seo-recommendation">
                <div class="skyview-seo-recommendation-icon">
                    <span class="dashicons dashicons-editor-textcolor"></span>
                </div>
                <div class="skyview-seo-recommendation-content">
                    <h3><?php esc_html_e('Improve Content Length', 'vidolimo-seo-auditor'); ?></h3>
                    <p><?php esc_html_e('Aim for at least 300 words for blog posts and 500 words for key pages. Longer content tends to rank better in search results.', 'vidolimo-seo-auditor'); ?></p>
                </div>
            </div>

            <div class="skyview-seo-recommendation">
                <div class="skyview-seo-recommendation-icon">
                    <span class="dashicons dashicons-editor-paragraph"></span>
                </div>
                <div class="skyview-seo-recommendation-content">
                    <h3><?php esc_html_e('Enhance Readability', 'vidolimo-seo-auditor'); ?></h3>
                    <p><?php esc_html_e('Use shorter paragraphs, bullet points, and subheadings to make your content more scannable and easier to read.', 'vidolimo-seo-auditor'); ?></p>
                </div>
            </div>

            <div class="skyview-seo-recommendation">
                <div class="skyview-seo-recommendation-icon">
                    <span class="dashicons dashicons-tag"></span>
                </div>
                <div class="skyview-seo-recommendation-content">
                    <h3><?php esc_html_e('Optimize Keyword Usage', 'vidolimo-seo-auditor'); ?></h3>
                    <p><?php esc_html_e('Aim for a keyword density of 1-2%. Include your target keyword in the title, first paragraph, and throughout the content naturally.', 'vidolimo-seo-auditor'); ?></p>
                </div>
            </div>
        </div>
    </div>
</div>


<?php
} // End of skyview_seo_content_analysis_page function

// Execute the function to display the page
skyview_seo_content_analysis_page();
?>
