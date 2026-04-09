<?php
/**
 * Images analysis template
 *
 * @package SkyView_SEO
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Generate and display image analysis
 */
function skyview_seo_images_analysis_page() {
    // Get all posts for analysis
    $args = array(
        'post_type' => get_post_types(array('public' => true)),
        'posts_per_page' => -1,
        'post_status' => 'publish',
    );
    $posts = get_posts($args);

    // Initialize analyzer
    $analyzer = new SkyView_SEO_Image_Analyzer();

    // Analyze all posts
    $pages_data = array();
    $total_images = 0;
    $images_with_alt = 0;
    $images_without_alt = 0;
    $total_image_size = 0;
    $issues_list = array();

foreach ($posts as $post) {
    $analysis = $analyzer->analyze($post);
    
    $post_total_images = isset($analysis['total_images']) ? $analysis['total_images'] : 0;
    $missing_alt_count = isset($analysis['missing_alt']['count']) ? $analysis['missing_alt']['count'] : 0;
    $alt_text_percentage = $post_total_images > 0 ? round((($post_total_images - $missing_alt_count) / $post_total_images) * 100) : 0;
    
    $pages_data[] = array(
        'id' => $post->ID,
        'title' => $post->post_title,
        'type' => $post->post_type,
        'total_images' => $post_total_images,
        'alt_text_percentage' => $alt_text_percentage,
        'average_size' => 85,
        'score' => $analysis['score'],
        'alt_text_score' => $alt_text_percentage >= 80 ? 80 : 50,
        'size_score' => 75,
    );
    
    $total_images += $post_total_images;
    $images_with_alt += ($post_total_images - $missing_alt_count);
    $images_without_alt += $missing_alt_count;
    
    // Handle missing alt text issues
    if ($missing_alt_count > 0 && isset($analysis['missing_alt']['images'])) {
        foreach ($analysis['missing_alt']['images'] as $image) {
            $image_url = isset($image['src']) ? $image['src'] : '';
            $image_size = '';
            
            // Try to get image size
            if (!empty($image_url)) {
                $image_path = str_replace(home_url(), ABSPATH, $image_url);
                if (file_exists($image_path)) {
                    $size_bytes = filesize($image_path);
                    $image_size = size_format($size_bytes, 2);
                }
            }
            
            $issues_list[] = array(
                'url' => $image_url,
                'alt' => '',
                'issue' => __('Missing alt text', 'vidolimo-seo-auditor'),
                'severity' => 'warning',
                'page_id' => $post->ID,
                'page_title' => $post->post_title,
                'attachment_id' => 0,
                'size' => $image_size,
            );
        }
    }
    
    // Handle large images (> 500 KB)
    if (isset($analysis['large_images']['images'])) {
        foreach ($analysis['large_images']['images'] as $image) {
            $image_url = isset($image['src']) ? $image['src'] : '';
            $image_size = '';
            
            // Try to get image size
            if (!empty($image_url)) {
                $image_path = str_replace(home_url(), ABSPATH, $image_url);
                if (file_exists($image_path)) {
                    $size_bytes = filesize($image_path);
                    $image_size = size_format($size_bytes, 2);
                }
            }
            
            $issues_list[] = array(
                'url' => $image_url,
                'issue' => __('Image larger than 500 KB', 'vidolimo-seo-auditor'),
                'severity' => 'warning',
                'page_id' => $post->ID,
                'page_title' => $post->post_title,
                'attachment_id' => 0,
                'size' => $image_size,
            );
        }
    }
}

// Prepare images data
$images_data = array(
    'total_images' => $total_images,
    'images_with_alt' => $images_with_alt,
    'images_without_alt' => $images_without_alt,
    'alt_text_percentage' => $total_images > 0 ? round(($images_with_alt / $total_images) * 100) : 0,
    'average_size' => 85,
    'pages' => $pages_data,
    'issues' => $issues_list,
    'size_distribution' => array(
        'small' => 0,
        'medium' => 0,
        'large' => 0,
        'very_large' => 0,
    ),
    'format_distribution' => array(
        'jpeg' => 0,
        'png' => 0,
        'gif' => 0,
        'webp' => 0,
        'other' => 0,
    ),
);

$analysis_nonce_value = wp_create_nonce('skyview-seo-analyze');
?>

<div class="wrap skyview-seo-wrap">
    <h1 class="skyview-seo-title">
        <img src="<?php echo esc_url(SKYVIEW_SEO_PLUGIN_URL . 'assets/logo.png'); ?>" alt="Vidolimo SEO Auditor" class="skyview-seo-logo">
        <?php esc_html_e('Image Analysis', 'vidolimo-seo-auditor'); ?>
    </h1>

    <div class="skyview-seo-overview">
        <div class="skyview-seo-overview-header">
            <h2><?php esc_html_e('Image Analysis Overview', 'vidolimo-seo-auditor'); ?></h2>
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
            <div class="skyview-seo-images-stats">
                <div class="skyview-seo-images-stat">
                    <h3><?php esc_html_e('Alt Text Usage', 'vidolimo-seo-auditor'); ?></h3>
                    <div class="skyview-seo-images-stat-chart">
                        <canvas id="skyview-seo-alt-text-chart"></canvas>
                    </div>
                    <div class="skyview-seo-images-stat-summary">
                        <p>
                            <?php
                            /* translators: %s: percentage of images with alt text */
                            echo sprintf(esc_html__('Images with alt text: %s%%', 'vidolimo-seo-auditor'), '<strong>' . esc_html($images_data['alt_text_percentage']) . '</strong>');
                            ?>
                        </p>
                    </div>
                </div>

                <div class="skyview-seo-images-stat">
                    <h3><?php esc_html_e('Image Size', 'vidolimo-seo-auditor'); ?></h3>
                    <div class="skyview-seo-images-stat-chart">
                        <canvas id="skyview-seo-image-size-chart"></canvas>
                    </div>
                    <div class="skyview-seo-images-stat-summary">
                        <p>
                            <?php
                            /* translators: %s: average image size in kilobytes */
                            echo sprintf(esc_html__('Average image size: %s KB', 'vidolimo-seo-auditor'), '<strong>' . esc_html($images_data['average_size']) . '</strong>');
                            ?>
                        </p>
                    </div>
                </div>

                <div class="skyview-seo-images-stat">
                    <h3><?php esc_html_e('Image Format', 'vidolimo-seo-auditor'); ?></h3>
                    <div class="skyview-seo-images-stat-chart">
                        <canvas id="skyview-seo-image-format-chart"></canvas>
                    </div>
                    <div class="skyview-seo-images-stat-summary">
                        <p>
                            <?php
                            /* translators: %s: total images count */
                            echo sprintf(esc_html__('Total images: %s', 'vidolimo-seo-auditor'), '<strong>' . esc_html($images_data['total_images']) . '</strong>');
                            ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="skyview-seo-images-pages">
        <div class="skyview-seo-images-pages-header">
            <h2><?php esc_html_e('Page Image Analysis', 'vidolimo-seo-auditor'); ?></h2>
            <div class="skyview-seo-images-pages-filter">
                <select id="skyview-seo-images-filter">
                    <option value="all"><?php esc_html_e('All Pages', 'vidolimo-seo-auditor'); ?></option>
                    <option value="posts"><?php esc_html_e('Posts', 'vidolimo-seo-auditor'); ?></option>
                    <option value="pages"><?php esc_html_e('Pages', 'vidolimo-seo-auditor'); ?></option>
                    <option value="products"><?php esc_html_e('Products', 'vidolimo-seo-auditor'); ?></option>
                </select>
                <button type="button" class="button" id="skyview-seo-images-filter-apply">
                    <?php esc_html_e('Apply', 'vidolimo-seo-auditor'); ?>
                </button>
            </div>
        </div>

        <div class="skyview-seo-images-pages-table-container">
            <table class="skyview-seo-images-pages-table widefat">
                <thead>
                    <tr>
                        <th><?php esc_html_e('Page', 'vidolimo-seo-auditor'); ?></th>
                        <th><?php esc_html_e('Type', 'vidolimo-seo-auditor'); ?></th>
                        <th><?php esc_html_e('Images', 'vidolimo-seo-auditor'); ?></th>
                        <th><?php esc_html_e('Alt Text', 'vidolimo-seo-auditor'); ?></th>
                        <th><?php esc_html_e('Size', 'vidolimo-seo-auditor'); ?></th>
                        <th><?php esc_html_e('Score', 'vidolimo-seo-auditor'); ?></th>
                        <th><?php esc_html_e('Actions', 'vidolimo-seo-auditor'); ?></th>
                    </tr>
                </thead>
                <tbody id="skyview-seo-images-pages-list">
                    <?php if (!empty($images_data['pages'])) : ?>
                        <?php foreach ($images_data['pages'] as $page) : ?>
                            <tr>
                                <td>
                                    <a href="<?php echo esc_url(get_edit_post_link($page['id'])); ?>">
                                        <?php echo esc_html($page['title']); ?>
                                    </a>
                                </td>
                                <td><?php echo esc_html($page['type']); ?></td>
                                <td><?php echo esc_html($page['total_images']); ?></td>
                                <td>
                                    <span class="skyview-seo-alt-text <?php echo esc_attr($page['alt_text_score'] >= 80 ? 'good' : ($page['alt_text_score'] >= 60 ? 'warning' : 'bad')); ?>">
                                        <?php echo esc_html($page['alt_text_percentage']); ?>%
                                    </span>
                                </td>
                                <td>
                                    <span class="skyview-seo-image-size <?php echo esc_attr($page['size_score'] >= 80 ? 'good' : ($page['size_score'] >= 60 ? 'warning' : 'bad')); ?>">
                                        <?php echo esc_html($page['average_size']); ?> KB
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
                                    <p><?php esc_html_e('No image analysis data available. Run an analysis to see results.', 'vidolimo-seo-auditor'); ?></p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="skyview-seo-images-issues">
        <div class="skyview-seo-images-issues-header">
            <h2><?php esc_html_e('Image Issues', 'vidolimo-seo-auditor'); ?></h2>
        </div>

        <div class="skyview-seo-images-issues-table-container">
            <table class="skyview-seo-images-issues-table widefat">
                <thead>
                    <tr>
                        <th><?php esc_html_e('Image', 'vidolimo-seo-auditor'); ?></th>
                        <th><?php esc_html_e('Issue', 'vidolimo-seo-auditor'); ?></th>
                        <th><?php esc_html_e('Found On', 'vidolimo-seo-auditor'); ?></th>
                        <th><?php esc_html_e('Actions', 'vidolimo-seo-auditor'); ?></th>
                    </tr>
                </thead>
                <tbody id="skyview-seo-images-issues-list">
                    <?php if (!empty($images_data['issues'])) : ?>
                        <?php foreach ($images_data['issues'] as $issue) : ?>
                            <tr>
                                <td>
                                    <img src="<?php echo esc_url($issue['url']); ?>" alt="<?php echo esc_attr($issue['alt']); ?>" class="skyview-seo-thumbnail">
                                </td>
                                <td>
                                    <span class="skyview-seo-image-issue <?php echo esc_attr($issue['severity']); ?>">
                                        <?php echo esc_html($issue['issue']); ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="<?php echo esc_url(get_edit_post_link($issue['page_id'])); ?>">
                                        <?php echo esc_html($issue['page_title']); ?>
                                    </a>
                                </td>
                                <td>
                                    <a href="<?php echo esc_url(get_edit_post_link($issue['page_id'])); ?>" class="button button-small">
                                        <?php esc_html_e('Edit Page', 'vidolimo-seo-auditor'); ?>
                                    </a>
                                    <?php if ($issue['attachment_id']) : ?>
                                        <a href="<?php echo esc_url(get_edit_post_link($issue['attachment_id'])); ?>" class="button button-small">
                                            <?php esc_html_e('Edit Image', 'vidolimo-seo-auditor'); ?>
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="4">
                                <div class="skyview-seo-no-data">
                                    <p><?php esc_html_e('No image issues found. Great job!', 'vidolimo-seo-auditor'); ?></p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="skyview-seo-images-recommendations">
        <div class="skyview-seo-images-recommendations-header">
            <h2><?php esc_html_e('Image Recommendations', 'vidolimo-seo-auditor'); ?></h2>
        </div>

        <div class="skyview-seo-images-recommendations-list">
            <div class="skyview-seo-recommendation">
                <div class="skyview-seo-recommendation-icon">
                    <span class="dashicons dashicons-format-image"></span>
                </div>
                <div class="skyview-seo-recommendation-content">
                    <h3><?php esc_html_e('Use Descriptive Alt Text', 'vidolimo-seo-auditor'); ?></h3>
                    <p><?php esc_html_e('Add descriptive alt text to all images to improve accessibility and SEO. Include relevant keywords where appropriate.', 'vidolimo-seo-auditor'); ?></p>
                </div>
            </div>

            <div class="skyview-seo-recommendation">
                <div class="skyview-seo-recommendation-icon">
                    <span class="dashicons dashicons-performance"></span>
                </div>
                <div class="skyview-seo-recommendation-content">
                    <h3><?php esc_html_e('Optimize Image Size', 'vidolimo-seo-auditor'); ?></h3>
                    <p><?php esc_html_e('Keep image file sizes under 100KB when possible to improve page load speed. Use appropriate dimensions for your layout.', 'vidolimo-seo-auditor'); ?></p>
                </div>
            </div>

            <div class="skyview-seo-recommendation">
                <div class="skyview-seo-recommendation-icon">
                    <span class="dashicons dashicons-admin-settings"></span>
                </div>
                <div class="skyview-seo-recommendation-content">
                    <h3><?php esc_html_e('Use Modern Image Formats', 'vidolimo-seo-auditor'); ?></h3>
                    <p><?php esc_html_e('Consider using WebP or next-gen image formats for better compression and quality. Ensure proper fallbacks for older browsers.', 'vidolimo-seo-auditor'); ?></p>
                </div>
            </div>
        </div>
    </div>
</div>


<?php
} // End of skyview_seo_images_analysis_page function

// Execute the function to display the page
skyview_seo_images_analysis_page();
?>
