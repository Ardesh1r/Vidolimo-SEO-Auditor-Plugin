<?php
/**
 * Admin dashboard template
 *
 * @package SkyView_SEO
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Generate and display dashboard page
 */
function skyview_seo_dashboard_page() {
    // Get site analysis data
    $analyzer = new SkyView_SEO_Analyzer();
    $site_data = $analyzer->get_site_analysis();
?>

<div class="wrap skyview-seo-wrap">
    <h1 class="skyview-seo-title">
        <img style="background: black; border-radius: 50px;" src="<?php echo esc_url(SKYVIEW_SEO_PLUGIN_URL . 'assets/logo.png'); ?>" alt="Vidolimo SEO Auditor" class="skyview-seo-logo">
        <?php esc_html_e('Vidolimo Dashboard', 'vidolimo-seo-auditor'); ?>
    </h1>

    <div class="skyview-seo-overview">
        <div class="skyview-seo-overview-header">
            <h2><?php esc_html_e('Website SEO Overview', 'vidolimo-seo-auditor'); ?></h2>
        </div>

        <div class="skyview-seo-overview-content">
            <div class="skyview-seo-overview-summary">
                <div class="skyview-seo-score-circle <?php echo esc_attr($site_data['average_score'] >= 80 ? 'good' : ($site_data['average_score'] >= 60 ? 'warning' : 'bad')); ?>">
                    <div class="skyview-seo-score-number"><?php echo esc_html($site_data['average_score']); ?></div>
                    <div class="skyview-seo-score-label"><?php esc_html_e('Score', 'vidolimo-seo-auditor'); ?></div>
                </div>
                <div class="skyview-seo-overview-stats">
                    <div class="skyview-seo-stat">
                        <span class="skyview-seo-stat-number"><?php echo esc_html($site_data['analyzed_posts']); ?>/<?php echo esc_html($site_data['total_posts']); ?></span>
                        <span class="skyview-seo-stat-label"><?php esc_html_e('Pages Analyzed', 'vidolimo-seo-auditor'); ?></span>
                    </div>
                    <?php if (!empty($site_data['issues']['critical'])) : ?>
                        <div class="skyview-seo-stat">
                            <span class="skyview-seo-stat-number"><?php echo esc_html($site_data['issues']['critical']); ?></span>
                            <span class="skyview-seo-stat-label"><?php esc_html_e('Critical Issues', 'vidolimo-seo-auditor'); ?></span>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($site_data['issues']['warning'])) : ?>
                        <div class="skyview-seo-stat">
                            <span class="skyview-seo-stat-number"><?php echo esc_html($site_data['issues']['warning']); ?></span>
                            <span class="skyview-seo-stat-label"><?php esc_html_e('Warnings', 'vidolimo-seo-auditor'); ?></span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="skyview-seo-overview-categories">
                <div class="skyview-seo-category <?php echo esc_attr($site_data['content_score'] >= 80 ? 'good' : ($site_data['content_score'] >= 60 ? 'warning' : 'bad')); ?>">
                    <div class="skyview-seo-category-icon">
                        <span class="dashicons dashicons-text-page"></span>
                    </div>
                    <div class="skyview-seo-category-details">
                        <h3>
                            <?php esc_html_e('Content', 'vidolimo-seo-auditor'); ?>
                            <span class="skyview-seo-tooltip-trigger" data-tooltip="Content score combines: keyword presence in title/H1/first paragraph, readability (Flesch-like heuristic), heading hierarchy, word count sufficiency, and duplicate title detection. Heavier weight on titles and H1s; thin pages reduce the score more aggressively.">
                                <span class="dashicons dashicons-info-outline"></span>
                            </span>
                        </h3>
                        <div class="skyview-seo-category-score">
                            <div class="skyview-seo-category-score-bar">
                                <div class="skyview-seo-category-score-fill" style="width: <?php echo esc_attr($site_data['content_score']); ?>%;"></div>
                            </div>
                            <div class="skyview-seo-category-score-number">
                                <?php echo esc_html($site_data['content_score']); ?>
                                <span class="skyview-seo-category-score-label">
                                    <?php echo esc_html($site_data['content_score'] >= 80 ? 'Good' : ($site_data['content_score'] >= 60 ? 'Needs Improvement' : 'Poor')); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="skyview-seo-category <?php echo esc_attr($site_data['links_score'] >= 80 ? 'good' : ($site_data['links_score'] >= 60 ? 'warning' : 'bad')); ?>">
                    <div class="skyview-seo-category-icon">
                        <span class="dashicons dashicons-admin-links"></span>
                    </div>
                    <div class="skyview-seo-category-details">
                        <h3>
                            <?php esc_html_e('Links', 'vidolimo-seo-auditor'); ?>
                            <span class="skyview-seo-tooltip-trigger" data-tooltip="Links score considers internal links per page, presence of orphan pages, broken links, and excessive nofollow usage. Broken links have the highest negative impact; strong internal linking improves the score.">
                                <span class="dashicons dashicons-info-outline"></span>
                            </span>
                        </h3>
                        <div class="skyview-seo-category-score">
                            <div class="skyview-seo-category-score-bar">
                                <div class="skyview-seo-category-score-fill" style="width: <?php echo esc_attr($site_data['links_score']); ?>%;"></div>
                            </div>
                            <div class="skyview-seo-category-score-number">
                                <?php echo esc_html($site_data['links_score']); ?>
                                <span class="skyview-seo-category-score-label">
                                    <?php echo esc_html($site_data['links_score'] >= 80 ? 'Good' : ($site_data['links_score'] >= 60 ? 'Needs Improvement' : 'Poor')); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="skyview-seo-category <?php echo esc_attr($site_data['images_score'] >= 80 ? 'good' : ($site_data['images_score'] >= 60 ? 'warning' : 'bad')); ?>">
                    <div class="skyview-seo-category-icon">
                        <span class="dashicons dashicons-format-image"></span>
                    </div>
                    <div class="skyview-seo-category-details">
                        <h3>
                            <?php esc_html_e('Images', 'vidolimo-seo-auditor'); ?>
                            <span class="skyview-seo-tooltip-trigger" data-tooltip="Images score evaluates: percentage of images with alt text, oversized image detection, lazy-loading usage, and descriptive filenames. Missing alts and very large files reduce the score the most.">
                                <span class="dashicons dashicons-info-outline"></span>
                            </span>
                        </h3>
                        <div class="skyview-seo-category-score">
                            <div class="skyview-seo-category-score-bar">
                                <div class="skyview-seo-category-score-fill" style="width: <?php echo esc_attr($site_data['images_score']); ?>%;"></div>
                            </div>
                            <div class="skyview-seo-category-score-number">
                                <?php echo esc_html($site_data['images_score']); ?>
                                <span class="skyview-seo-category-score-label">
                                    <?php echo esc_html($site_data['images_score'] >= 80 ? 'Good' : ($site_data['images_score'] >= 60 ? 'Needs Improvement' : 'Poor')); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="skyview-seo-category <?php echo esc_attr($site_data['technical_score'] >= 80 ? 'good' : ($site_data['technical_score'] >= 60 ? 'warning' : 'bad')); ?>">
                    <div class="skyview-seo-category-icon">
                        <span class="dashicons dashicons-admin-tools"></span>
                    </div>
                    <div class="skyview-seo-category-details">
                        <h3>
                            <?php esc_html_e('Technical', 'vidolimo-seo-auditor'); ?>
                            <span class="skyview-seo-tooltip-trigger" data-tooltip="Technical score checks: HTTPS, canonical tags, robots directives, XML sitemap presence, mobile viewport, and basic crawlability. HTTPS and robots/canonical issues are weighted as critical.">
                                <span class="dashicons dashicons-info-outline"></span>
                            </span>
                        </h3>
                        <div class="skyview-seo-category-score">
                            <div class="skyview-seo-category-score-bar">
                                <div class="skyview-seo-category-score-fill" style="width: <?php echo esc_attr($site_data['technical_score']); ?>%;"></div>
                            </div>
                            <div class="skyview-seo-category-score-number">
                                <?php echo esc_html($site_data['technical_score']); ?>
                                <span class="skyview-seo-category-score-label">
                                    <?php echo esc_html($site_data['technical_score'] >= 80 ? 'Good' : ($site_data['technical_score'] >= 60 ? 'Needs Improvement' : 'Poor')); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="skyview-seo-category <?php echo esc_attr($site_data['performance_score'] >= 80 ? 'good' : ($site_data['performance_score'] >= 60 ? 'warning' : 'bad')); ?>">
                    <div class="skyview-seo-category-icon">
                        <span class="dashicons dashicons-performance"></span>
                    </div>
                    <div class="skyview-seo-category-details">
                        <h3>
                            <?php esc_html_e('Performance', 'vidolimo-seo-auditor'); ?>
                            <span class="skyview-seo-tooltip-trigger" data-tooltip="Performance score measures page load speed, resource optimization, caching implementation, and overall site responsiveness.">
                                <span class="dashicons dashicons-info-outline"></span>
                            </span>
                        </h3>
                        <div class="skyview-seo-category-score">
                            <div class="skyview-seo-category-score-bar">
                                <div class="skyview-seo-category-score-fill" style="width: <?php echo esc_attr($site_data['performance_score']); ?>%;"></div>
                            </div>
                            <div class="skyview-seo-category-score-number">
                                <?php echo esc_html($site_data['performance_score']); ?>
                                <span class="skyview-seo-category-score-label">
                                    <?php echo esc_html($site_data['performance_score'] >= 80 ? 'Good' : ($site_data['performance_score'] >= 60 ? 'Needs Improvement' : 'Poor')); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="skyview-seo-category <?php echo esc_attr($site_data['freshness_score'] >= 80 ? 'good' : ($site_data['freshness_score'] >= 60 ? 'warning' : 'bad')); ?>">
                    <div class="skyview-seo-category-icon">
                        <span class="dashicons dashicons-calendar-alt"></span>
                    </div>
                    <div class="skyview-seo-category-details">
                        <h3>
                            <?php esc_html_e('Freshness', 'vidolimo-seo-auditor'); ?>
                            <span class="skyview-seo-tooltip-trigger" data-tooltip="Freshness score evaluates how recently your content was published or updated, with newer content receiving higher scores.">
                                <span class="dashicons dashicons-info-outline"></span>
                            </span>
                        </h3>
                        <div class="skyview-seo-category-score">
                            <div class="skyview-seo-category-score-bar">
                                <div class="skyview-seo-category-score-fill" style="width: <?php echo esc_attr($site_data['freshness_score']); ?>%;"></div>
                            </div>
                            <div class="skyview-seo-category-score-number">
                                <?php echo esc_html($site_data['freshness_score']); ?>
                                <span class="skyview-seo-category-score-label">
                                    <?php echo esc_html($site_data['freshness_score'] >= 80 ? 'Good' : ($site_data['freshness_score'] >= 60 ? 'Needs Improvement' : 'Poor')); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <div class="skyview-seo-all-pages">
        <div class="skyview-seo-section-header skyview-seo-all-pages-header">
            <h2><?php esc_html_e('All Pages Analysis', 'vidolimo-seo-auditor'); ?></h2>
            <div class="skyview-seo-section-actions">
                <div class="skyview-seo-search-box">
                    <input type="text" id="skyview-seo-page-search" class="skyview-seo-search-input" placeholder="<?php esc_attr_e('Search pages...', 'vidolimo-seo-auditor'); ?>">
                </div>
                <div class="skyview-seo-pagination-options">
                    <?php
                    $pagination_nonce = isset($_GET['_wpnonce']) ? sanitize_text_field(wp_unslash($_GET['_wpnonce'])) : '';
                    $has_valid_nonce = !empty($pagination_nonce) && wp_verify_nonce($pagination_nonce, 'skyview-seo-pagination');
                    $items_per_page = $has_valid_nonce && isset($_GET['per_page']) ? absint($_GET['per_page']) : 10;
                    $current_page = $has_valid_nonce && isset($_GET['paged']) ? max(1, absint($_GET['paged'])) : 1;
                    $pagination_nonce_value = wp_create_nonce('skyview-seo-pagination');
                    $analysis_nonce_value = wp_create_nonce('skyview-seo-analyze');
                    ?>
                    <select id="skyview-seo-items-per-page">
                        <option value="10" <?php selected($items_per_page, 10); ?>>10</option>
                        <option value="25" <?php selected($items_per_page, 25); ?>>25</option>
                        <option value="50" <?php selected($items_per_page, 50); ?>>50</option>
                    </select>
                    <label for="skyview-seo-items-per-page"><?php esc_html_e('Items per page', 'vidolimo-seo-auditor'); ?></label>
                </div>
            </div>
        </div>

        <div class="skyview-seo-all-pages-content">
            <?php
            // Get all content types
            $post_types = get_post_types(array('public' => true));
            
            // Get all published posts of all content types
            $all_posts = get_posts(array(
                'post_type' => $post_types,
                'post_status' => 'publish',
                'posts_per_page' => -1,
                'orderby' => 'date',
                'order' => 'DESC',
            ));
            
            // Pagination settings
            $items_per_page = $items_per_page > 0 ? $items_per_page : 10;
            $total_items = count($all_posts);
            $total_pages = ceil($total_items / $items_per_page);
            $current_page = $current_page > 0 ? $current_page : 1;
            $offset = ($current_page - 1) * $items_per_page;
            
            // Store all posts for JavaScript search
            $all_posts_data = array();
            foreach ($all_posts as $post) {
                $all_posts_data[] = array(
                    'id' => $post->ID,
                    'title' => $post->post_title,
                    'type' => $post->post_type
                );
            }
            
            // Get current page items
            $current_items = array_slice($all_posts, $offset, $items_per_page);
            ?>

            <?php if (!empty($all_posts)) : ?>
                <div class="skyview-seo-table-container">
                    <table class="skyview-seo-all-pages-table">
                        <thead>
                            <tr>
                                <th class="skyview-seo-th-title"><?php esc_html_e('Page Title', 'vidolimo-seo-auditor'); ?></th>
                                <th class="skyview-seo-th-type"><?php esc_html_e('Type', 'vidolimo-seo-auditor'); ?></th>
                                <th class="skyview-seo-th-score"><?php esc_html_e('Content', 'vidolimo-seo-auditor'); ?></th>
                                <th class="skyview-seo-th-score"><?php esc_html_e('Links', 'vidolimo-seo-auditor'); ?></th>
                                <th class="skyview-seo-th-score"><?php esc_html_e('Images', 'vidolimo-seo-auditor'); ?></th>
                                <th class="skyview-seo-th-score"><?php esc_html_e('Technical', 'vidolimo-seo-auditor'); ?></th>
                                <th class="skyview-seo-th-score"><?php esc_html_e('Overall', 'vidolimo-seo-auditor'); ?></th>
                                <th class="skyview-seo-th-actions"><?php esc_html_e('Actions', 'vidolimo-seo-auditor'); ?></th>
                            </tr>
                        </thead>
                        <tbody id="skyview-seo-pages-tbody">
                            <?php 
                            $analyzer = new SkyView_SEO_Analyzer();
                            foreach ($current_items as $post) : 
                                $analysis = $analyzer->get_post_analysis($post->ID);
                                $overall_score = isset($analysis['overall_score']) ? intval($analysis['overall_score']) : 0;
                            ?>
                                <tr>
                                    <td class="skyview-seo-td-title">
                                        <strong><?php echo esc_html($post->post_title); ?></strong>
                                    </td>
                                    <td class="skyview-seo-td-type">
                                        <span class="skyview-seo-post-type"><?php echo esc_html(ucfirst($post->post_type)); ?></span>
                                    </td>
                                    <td class="skyview-seo-td-score">
                                        <span class="skyview-seo-score-badge <?php echo esc_attr($analysis['content']['score'] >= 80 ? 'good' : ($analysis['content']['score'] >= 60 ? 'warning' : 'bad')); ?>">
                                            <?php echo esc_html($analysis['content']['score']); ?>
                                        </span>
                                    </td>
                                    <td class="skyview-seo-td-score">
                                        <span class="skyview-seo-score-badge <?php echo esc_attr($analysis['links']['score'] >= 80 ? 'good' : ($analysis['links']['score'] >= 60 ? 'warning' : 'bad')); ?>">
                                            <?php echo esc_html($analysis['links']['score']); ?>
                                        </span>
                                    </td>
                                    <td class="skyview-seo-td-score">
                                        <span class="skyview-seo-score-badge <?php echo esc_attr($analysis['images']['score'] >= 80 ? 'good' : ($analysis['images']['score'] >= 60 ? 'warning' : 'bad')); ?>">
                                            <?php echo esc_html($analysis['images']['score']); ?>
                                        </span>
                                    </td>
                                    <td class="skyview-seo-td-score">
                                        <span class="skyview-seo-score-badge <?php echo esc_attr($analysis['technical']['score'] >= 80 ? 'good' : ($analysis['technical']['score'] >= 60 ? 'warning' : 'bad')); ?>">
                                            <?php echo esc_html($analysis['technical']['score']); ?>
                                        </span>
                                    </td>
                                    <td class="skyview-seo-td-score">
                                        <span class="skyview-seo-score-badge overall <?php echo esc_attr($overall_score >= 80 ? 'good' : ($overall_score >= 60 ? 'warning' : 'bad')); ?>">
                                            <?php echo esc_html($overall_score); ?>
                                        </span>
                                    </td>
                                    <td class="skyview-seo-td-actions">
                                        <a href="<?php echo esc_url(add_query_arg(array(
                                            'page' => 'skyview-seo-analyze',
                                            'post_id' => $post->ID,
                                            '_wpnonce' => $analysis_nonce_value,
                                        ), admin_url('admin.php'))); ?>" class="button button-small button-primary">
                                            <?php esc_html_e('Analyze', 'vidolimo-seo-auditor'); ?>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <?php if ($total_pages > 1) : ?>
                <div class="skyview-seo-pagination">
                    <div class="skyview-seo-pagination-info">
                        <?php
                        $display_from = $offset + 1;
                        $display_to = min($offset + $items_per_page, $total_items);
                        echo sprintf(
                            /* translators: 1: first item number, 2: last item number, 3: total items */
                            esc_html__('Showing %1$d to %2$d of %3$d entries', 'vidolimo-seo-auditor'),
                            esc_html($display_from),
                            esc_html($display_to),
                            esc_html($total_items)
                        );
                        ?>
                    </div>
                    <div class="skyview-seo-pagination-links">
                        <?php if ($current_page > 1) : ?>
                            <a href="<?php echo esc_url(add_query_arg(array('paged' => $current_page - 1, '_wpnonce' => $pagination_nonce_value))); ?>" class="skyview-seo-pagination-prev">&laquo; <?php esc_html_e('Previous', 'vidolimo-seo-auditor'); ?></a>
                        <?php endif; ?>
                        
                        <?php for ($i = 1; $i <= $total_pages; $i++) : ?>
                            <?php if (
                                $i === 1 || 
                                $i === $total_pages || 
                                ($i >= $current_page - 2 && $i <= $current_page + 2)
                            ) : ?>
                                <a href="<?php echo esc_url(add_query_arg(array('paged' => $i, '_wpnonce' => $pagination_nonce_value))); ?>" class="skyview-seo-pagination-link <?php echo $i === $current_page ? 'current' : ''; ?>">
                                    <?php echo esc_html($i); ?>
                                </a>
                            <?php elseif (
                                $i === 2 || 
                                $i === $total_pages - 1 || 
                                $i === $current_page - 3 || 
                                $i === $current_page + 3
                            ) : ?>
                                <span class="skyview-seo-pagination-ellipsis">&hellip;</span>
                            <?php endif; ?>
                        <?php endfor; ?>
                        
                        <?php if ($current_page < $total_pages) : ?>
                            <a href="<?php echo esc_url(add_query_arg(array('paged' => $current_page + 1, '_wpnonce' => $pagination_nonce_value))); ?>" class="skyview-seo-pagination-next"><?php esc_html_e('Next', 'vidolimo-seo-auditor'); ?> &raquo;</a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
                
            <?php else : ?>
                <div class="skyview-seo-no-pages">
                    <p><?php esc_html_e('No pages found to analyze.', 'vidolimo-seo-auditor'); ?></p>
                </div>
            <?php endif; ?>
        </div>
    </div>

</div>

<?php
} // End of skyview_seo_dashboard_page function

// Execute the function to display the page
skyview_seo_dashboard_page();
?>
