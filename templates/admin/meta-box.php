<?php
/**
 * Meta box template - DEPRECATED
 * 
 * This template is no longer used as the metabox functionality has been disabled.
 * SEO analysis is only available through the dedicated plugin page.
 *
 * @package SkyView_SEO
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Display meta box content for post analysis - DEPRECATED
 *
 * @param WP_Post $post The post object
 * @param array   $analysis Optional analysis data
 */
function skyview_seo_meta_box_content($post, $analysis = array()) {
    // Get post ID
    $post_id = $post->ID;
    
    // Get analysis data
    $analysis = isset($analysis) ? $analysis : array();

    // Calculate overall score
    $overall_score = isset($analysis['overall_score']) ? $analysis['overall_score'] : 0;

    // Determine score class
    $score_class = 'bad';
    if ($overall_score >= 80) {
        $score_class = 'good';
    } elseif ($overall_score >= 60) {
        $score_class = 'warning';
    }
?>

<div class="skyview-seo-meta-box">
    <div class="skyview-seo-meta-box-header">
        <div class="skyview-seo-meta-box-score">
            <div class="skyview-seo-meta-box-score-circle <?php echo esc_attr($score_class); ?>">
                <?php echo esc_html($overall_score); ?>
            </div>
            <div class="skyview-seo-meta-box-score-label">
                <?php esc_html_e('SEO Score', 'vidolimo-seo-auditor'); ?>
            </div>
        </div>
        <div class="skyview-seo-meta-box-actions">
            <button type="button" class="button" id="skyview-seo-analyze-button" data-post-id="<?php echo esc_attr($post_id); ?>">
                <span class="dashicons dashicons-update"></span>
                <?php esc_html_e('Analyze', 'vidolimo-seo-auditor'); ?>
            </button>
        </div>
    </div>

    <div class="skyview-seo-meta-box-content">
        <div class="skyview-seo-meta-box-tabs">
            <div class="skyview-seo-meta-box-tab active" data-tab="content">
                <?php esc_html_e('Content', 'vidolimo-seo-auditor'); ?>
            </div>
            <div class="skyview-seo-meta-box-tab" data-tab="links">
                <?php esc_html_e('Links', 'vidolimo-seo-auditor'); ?>
            </div>
            <div class="skyview-seo-meta-box-tab" data-tab="images">
                <?php esc_html_e('Images', 'vidolimo-seo-auditor'); ?>
            </div>
            <div class="skyview-seo-meta-box-tab" data-tab="technical">
                <?php esc_html_e('Technical', 'vidolimo-seo-auditor'); ?>
            </div>
        </div>

        <div id="skyview-seo-loading" style="display: none;">
            <div class="spinner is-active" style="float: none; margin: 0 auto; display: block;"></div>
            <p style="text-align: center;"><?php esc_html_e('Analyzing...', 'vidolimo-seo-auditor'); ?></p>
        </div>

        <div id="skyview-seo-results">
            <?php if (empty($analysis)) : ?>
                <div class="skyview-seo-meta-box-notice">
                    <p><?php esc_html_e('Click "Analyze" to check your content for SEO issues.', 'vidolimo-seo-auditor'); ?></p>
                </div>
            <?php else : ?>
                <!-- Content Analysis Panel -->
                <div class="skyview-seo-meta-box-panel active" data-panel="content">
                    <?php if (isset($analysis['content'])) : ?>
                        <?php $content = $analysis['content']; ?>
                        
                        <!-- Title -->
                        <div class="skyview-seo-meta-box-item">
                            <div class="skyview-seo-meta-box-item-header">
                                <div class="skyview-seo-meta-box-item-title">
                                    <?php esc_html_e('Page Title', 'vidolimo-seo-auditor'); ?>
                                </div>
                                <div class="skyview-seo-meta-box-item-status">
                                    <span class="skyview-seo-meta-box-item-status-icon <?php echo esc_attr($content['title']['status']); ?>">
                                        <?php if ($content['title']['status'] === 'good') : ?>
                                            <span class="dashicons dashicons-yes-alt"></span>
                                        <?php elseif ($content['title']['status'] === 'warning') : ?>
                                            <span class="dashicons dashicons-warning"></span>
                                        <?php else : ?>
                                            <span class="dashicons dashicons-dismiss"></span>
                                        <?php endif; ?>
                                    </span>
                                </div>
                            </div>
                            <div class="skyview-seo-meta-box-item-value">
                                <?php echo esc_html($content['title']['value']); ?>
                                <span class="skyview-seo-meta-box-item-length">
                                    (<?php echo esc_html($content['title']['length']); ?> <?php esc_html_e('characters', 'vidolimo-seo-auditor'); ?>)
                                </span>
                            </div>
                            <div class="skyview-seo-meta-box-item-message">
                                <?php echo esc_html($content['title']['message']); ?>
                            </div>
                        </div>
                        
                        <!-- Meta Description -->
                        <div class="skyview-seo-meta-box-item">
                            <div class="skyview-seo-meta-box-item-header">
                                <div class="skyview-seo-meta-box-item-title">
                                    <?php esc_html_e('Meta Description', 'vidolimo-seo-auditor'); ?>
                                </div>
                                <div class="skyview-seo-meta-box-item-status">
                                    <span class="skyview-seo-meta-box-item-status-icon <?php echo esc_attr($content['meta_description']['status']); ?>">
                                        <?php if ($content['meta_description']['status'] === 'good') : ?>
                                            <span class="dashicons dashicons-yes-alt"></span>
                                        <?php elseif ($content['meta_description']['status'] === 'warning') : ?>
                                            <span class="dashicons dashicons-warning"></span>
                                        <?php else : ?>
                                            <span class="dashicons dashicons-dismiss"></span>
                                        <?php endif; ?>
                                    </span>
                                </div>
                            </div>
                            <div class="skyview-seo-meta-box-item-value">
                                <?php echo esc_html($content['meta_description']['value']); ?>
                                <span class="skyview-seo-meta-box-item-length">
                                    (<?php echo esc_html($content['meta_description']['length']); ?> <?php esc_html_e('characters', 'vidolimo-seo-auditor'); ?>)
                                </span>
                            </div>
                            <div class="skyview-seo-meta-box-item-message">
                                <?php echo esc_html($content['meta_description']['message']); ?>
                            </div>
                        </div>
                        
                        <!-- Word Count -->
                        <div class="skyview-seo-meta-box-item">
                            <div class="skyview-seo-meta-box-item-header">
                                <div class="skyview-seo-meta-box-item-title">
                                    <?php esc_html_e('Word Count', 'vidolimo-seo-auditor'); ?>
                                </div>
                                <div class="skyview-seo-meta-box-item-status">
                                    <span class="skyview-seo-meta-box-item-status-icon <?php echo esc_attr($content['word_count']['status']); ?>">
                                        <?php if ($content['word_count']['status'] === 'good') : ?>
                                            <span class="dashicons dashicons-yes-alt"></span>
                                        <?php elseif ($content['word_count']['status'] === 'warning') : ?>
                                            <span class="dashicons dashicons-warning"></span>
                                        <?php else : ?>
                                            <span class="dashicons dashicons-dismiss"></span>
                                        <?php endif; ?>
                                    </span>
                                </div>
                            </div>
                            <div class="skyview-seo-meta-box-item-value">
                                <?php echo esc_html($content['word_count']['value']); ?> <?php esc_html_e('words', 'vidolimo-seo-auditor'); ?>
                            </div>
                            <div class="skyview-seo-meta-box-item-message">
                                <?php echo esc_html($content['word_count']['message']); ?>
                            </div>
                        </div>
                        
                        <!-- Reading Time -->
                        <div class="skyview-seo-meta-box-item">
                            <div class="skyview-seo-meta-box-item-header">
                                <div class="skyview-seo-meta-box-item-title">
                                    <?php esc_html_e('Reading Time', 'vidolimo-seo-auditor'); ?>
                                </div>
                            </div>
                            <div class="skyview-seo-meta-box-item-value">
                                <?php echo esc_html($content['reading_time']['value']); ?> <?php esc_html_e('minutes', 'vidolimo-seo-auditor'); ?>
                            </div>
                        </div>
                        
                        <!-- Content Quality -->
                        <div class="skyview-seo-meta-box-item">
                            <div class="skyview-seo-meta-box-item-header">
                                <div class="skyview-seo-meta-box-item-title">
                                    <?php esc_html_e('Content Quality', 'vidolimo-seo-auditor'); ?>
                                </div>
                                <div class="skyview-seo-meta-box-item-status">
                                    <span class="skyview-seo-meta-box-item-status-icon <?php echo esc_attr($content['content_quality']['status']); ?>">
                                        <?php if ($content['content_quality']['status'] === 'good') : ?>
                                            <span class="dashicons dashicons-yes-alt"></span>
                                        <?php elseif ($content['content_quality']['status'] === 'warning') : ?>
                                            <span class="dashicons dashicons-warning"></span>
                                        <?php else : ?>
                                            <span class="dashicons dashicons-dismiss"></span>
                                        <?php endif; ?>
                                    </span>
                                </div>
                            </div>
                            <div class="skyview-seo-meta-box-item-value">
                                <?php echo esc_html($content['content_quality']['value']); ?>/100
                            </div>
                            <div class="skyview-seo-meta-box-item-message">
                                <?php echo esc_html($content['content_quality']['message']); ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Links Analysis Panel -->
                <div class="skyview-seo-meta-box-panel" data-panel="links">
                    <?php if (isset($analysis['links'])) : ?>
                        <?php $links = $analysis['links']; ?>
                        
                        <!-- Internal Links -->
                        <div class="skyview-seo-meta-box-item">
                            <div class="skyview-seo-meta-box-item-header">
                                <div class="skyview-seo-meta-box-item-title">
                                    <?php esc_html_e('Internal Links', 'vidolimo-seo-auditor'); ?>
                                </div>
                                <div class="skyview-seo-meta-box-item-status">
                                    <span class="skyview-seo-meta-box-item-status-icon <?php echo esc_attr($links['internal_links']['status']); ?>">
                                        <?php if ($links['internal_links']['status'] === 'good') : ?>
                                            <span class="dashicons dashicons-yes-alt"></span>
                                        <?php elseif ($links['internal_links']['status'] === 'warning') : ?>
                                            <span class="dashicons dashicons-warning"></span>
                                        <?php else : ?>
                                            <span class="dashicons dashicons-dismiss"></span>
                                        <?php endif; ?>
                                    </span>
                                </div>
                            </div>
                            <div class="skyview-seo-meta-box-item-value">
                                <?php echo esc_html($links['internal_links']['count']); ?> <?php esc_html_e('links', 'vidolimo-seo-auditor'); ?>
                            </div>
                            <div class="skyview-seo-meta-box-item-message">
                                <?php echo esc_html($links['internal_links']['message']); ?>
                            </div>
                        </div>
                        
                        <!-- External Links -->
                        <div class="skyview-seo-meta-box-item">
                            <div class="skyview-seo-meta-box-item-header">
                                <div class="skyview-seo-meta-box-item-title">
                                    <?php esc_html_e('External Links', 'vidolimo-seo-auditor'); ?>
                                </div>
                                <div class="skyview-seo-meta-box-item-status">
                                    <span class="skyview-seo-meta-box-item-status-icon <?php echo esc_attr($links['external_links']['status']); ?>">
                                        <?php if ($links['external_links']['status'] === 'good') : ?>
                                            <span class="dashicons dashicons-yes-alt"></span>
                                        <?php elseif ($links['external_links']['status'] === 'warning') : ?>
                                            <span class="dashicons dashicons-warning"></span>
                                        <?php else : ?>
                                            <span class="dashicons dashicons-dismiss"></span>
                                        <?php endif; ?>
                                    </span>
                                </div>
                            </div>
                            <div class="skyview-seo-meta-box-item-value">
                                <?php echo esc_html($links['external_links']['count']); ?> <?php esc_html_e('links', 'vidolimo-seo-auditor'); ?>
                            </div>
                            <div class="skyview-seo-meta-box-item-message">
                                <?php echo esc_html($links['external_links']['message']); ?>
                            </div>
                        </div>
                        
                        <!-- Broken Links -->
                        <div class="skyview-seo-meta-box-item">
                            <div class="skyview-seo-meta-box-item-header">
                                <div class="skyview-seo-meta-box-item-title">
                                    <?php esc_html_e('Broken Links', 'vidolimo-seo-auditor'); ?>
                                </div>
                                <div class="skyview-seo-meta-box-item-status">
                                    <span class="skyview-seo-meta-box-item-status-icon <?php echo esc_attr($links['broken_links']['status']); ?>">
                                        <?php if ($links['broken_links']['status'] === 'good') : ?>
                                            <span class="dashicons dashicons-yes-alt"></span>
                                        <?php elseif ($links['broken_links']['status'] === 'warning') : ?>
                                            <span class="dashicons dashicons-warning"></span>
                                        <?php else : ?>
                                            <span class="dashicons dashicons-dismiss"></span>
                                        <?php endif; ?>
                                    </span>
                                </div>
                            </div>
                            <div class="skyview-seo-meta-box-item-value">
                                <?php echo esc_html($links['broken_links']['count']); ?> <?php esc_html_e('broken links', 'vidolimo-seo-auditor'); ?>
                            </div>
                            <div class="skyview-seo-meta-box-item-message">
                                <?php echo esc_html($links['broken_links']['message']); ?>
                            </div>
                            <?php if ($links['broken_links']['count'] > 0 && !empty($links['broken_links']['urls'])) : ?>
                                <div class="skyview-seo-meta-box-item-details">
                                    <ul>
                                        <?php foreach ($links['broken_links']['urls'] as $url) : ?>
                                            <li><?php echo esc_html($url); ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Orphan Pages -->
                        <div class="skyview-seo-meta-box-item">
                            <div class="skyview-seo-meta-box-item-header">
                                <div class="skyview-seo-meta-box-item-title">
                                    <?php esc_html_e('Orphan Page', 'vidolimo-seo-auditor'); ?>
                                </div>
                                <div class="skyview-seo-meta-box-item-status">
                                    <span class="skyview-seo-meta-box-item-status-icon <?php echo esc_attr($links['orphan_pages']['status']); ?>">
                                        <?php if ($links['orphan_pages']['status'] === 'good') : ?>
                                            <span class="dashicons dashicons-yes-alt"></span>
                                        <?php elseif ($links['orphan_pages']['status'] === 'warning') : ?>
                                            <span class="dashicons dashicons-warning"></span>
                                        <?php else : ?>
                                            <span class="dashicons dashicons-dismiss"></span>
                                        <?php endif; ?>
                                    </span>
                                </div>
                            </div>
                            <div class="skyview-seo-meta-box-item-message">
                                <?php echo esc_html($links['orphan_pages']['message']); ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Images Analysis Panel -->
                <div class="skyview-seo-meta-box-panel" data-panel="images">
                    <?php if (isset($analysis['images'])) : ?>
                        <?php $images = $analysis['images']; ?>
                        
                        <!-- Total Images -->
                        <div class="skyview-seo-meta-box-item">
                            <div class="skyview-seo-meta-box-item-header">
                                <div class="skyview-seo-meta-box-item-title">
                                    <?php esc_html_e('Total Images', 'vidolimo-seo-auditor'); ?>
                                </div>
                            </div>
                            <div class="skyview-seo-meta-box-item-value">
                                <?php echo esc_html($images['total_images']); ?> <?php esc_html_e('images', 'vidolimo-seo-auditor'); ?>
                            </div>
                        </div>
                        
                        <!-- Missing Alt Tags -->
                        <div class="skyview-seo-meta-box-item">
                            <div class="skyview-seo-meta-box-item-header">
                                <div class="skyview-seo-meta-box-item-title">
                                    <?php esc_html_e('Missing Alt Tags', 'vidolimo-seo-auditor'); ?>
                                </div>
                                <div class="skyview-seo-meta-box-item-status">
                                    <span class="skyview-seo-meta-box-item-status-icon <?php echo esc_attr($images['missing_alt']['status']); ?>">
                                        <?php if ($images['missing_alt']['status'] === 'good') : ?>
                                            <span class="dashicons dashicons-yes-alt"></span>
                                        <?php elseif ($images['missing_alt']['status'] === 'warning') : ?>
                                            <span class="dashicons dashicons-warning"></span>
                                        <?php else : ?>
                                            <span class="dashicons dashicons-dismiss"></span>
                                        <?php endif; ?>
                                    </span>
                                </div>
                            </div>
                            <div class="skyview-seo-meta-box-item-value">
                                <?php echo esc_html($images['missing_alt']['count']); ?> <?php esc_html_e('images', 'vidolimo-seo-auditor'); ?>
                            </div>
                            <div class="skyview-seo-meta-box-item-message">
                                <?php echo esc_html($images['missing_alt']['message']); ?>
                            </div>
                        </div>
                        
                        <!-- Large Images -->
                        <div class="skyview-seo-meta-box-item">
                            <div class="skyview-seo-meta-box-item-header">
                                <div class="skyview-seo-meta-box-item-title">
                                    <?php esc_html_e('Large Images', 'vidolimo-seo-auditor'); ?>
                                </div>
                                <div class="skyview-seo-meta-box-item-status">
                                    <span class="skyview-seo-meta-box-item-status-icon <?php echo esc_attr($images['large_images']['status']); ?>">
                                        <?php if ($images['large_images']['status'] === 'good') : ?>
                                            <span class="dashicons dashicons-yes-alt"></span>
                                        <?php elseif ($images['large_images']['status'] === 'warning') : ?>
                                            <span class="dashicons dashicons-warning"></span>
                                        <?php else : ?>
                                            <span class="dashicons dashicons-dismiss"></span>
                                        <?php endif; ?>
                                    </span>
                                </div>
                            </div>
                            <div class="skyview-seo-meta-box-item-value">
                                <?php echo esc_html($images['large_images']['count']); ?> <?php esc_html_e('images', 'vidolimo-seo-auditor'); ?>
                            </div>
                            <div class="skyview-seo-meta-box-item-message">
                                <?php echo esc_html($images['large_images']['message']); ?>
                            </div>
                        </div>
                        
                        <!-- Lazy Loading -->
                        <div class="skyview-seo-meta-box-item">
                            <div class="skyview-seo-meta-box-item-header">
                                <div class="skyview-seo-meta-box-item-title">
                                    <?php esc_html_e('Lazy Loading', 'vidolimo-seo-auditor'); ?>
                                </div>
                                <div class="skyview-seo-meta-box-item-status">
                                    <span class="skyview-seo-meta-box-item-status-icon <?php echo esc_attr($images['lazy_loading']['status']); ?>">
                                        <?php if ($images['lazy_loading']['status'] === 'good') : ?>
                                            <span class="dashicons dashicons-yes-alt"></span>
                                        <?php elseif ($images['lazy_loading']['status'] === 'warning') : ?>
                                            <span class="dashicons dashicons-warning"></span>
                                        <?php else : ?>
                                            <span class="dashicons dashicons-dismiss"></span>
                                        <?php endif; ?>
                                    </span>
                                </div>
                            </div>
                            <div class="skyview-seo-meta-box-item-value">
                                <?php echo esc_html($images['lazy_loading']['count']); ?> <?php esc_html_e('images not using lazy loading', 'vidolimo-seo-auditor'); ?>
                            </div>
                            <div class="skyview-seo-meta-box-item-message">
                                <?php echo esc_html($images['lazy_loading']['message']); ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Technical Analysis Panel -->
                <div class="skyview-seo-meta-box-panel" data-panel="technical">
                    <?php if (isset($analysis['technical'])) : ?>
                        <?php $technical = $analysis['technical']; ?>
                        
                        <!-- HTTPS Status -->
                        <div class="skyview-seo-meta-box-item">
                            <div class="skyview-seo-meta-box-item-header">
                                <div class="skyview-seo-meta-box-item-title">
                                    <?php esc_html_e('HTTPS Status', 'vidolimo-seo-auditor'); ?>
                                </div>
                                <div class="skyview-seo-meta-box-item-status">
                                    <span class="skyview-seo-meta-box-item-status-icon <?php echo esc_attr($technical['https_status']['status']); ?>">
                                        <?php if ($technical['https_status']['status'] === 'good') : ?>
                                            <span class="dashicons dashicons-yes-alt"></span>
                                        <?php elseif ($technical['https_status']['status'] === 'warning') : ?>
                                            <span class="dashicons dashicons-warning"></span>
                                        <?php else : ?>
                                            <span class="dashicons dashicons-dismiss"></span>
                                        <?php endif; ?>
                                    </span>
                                </div>
                            </div>
                            <div class="skyview-seo-meta-box-item-message">
                                <?php echo esc_html($technical['https_status']['message']); ?>
                            </div>
                        </div>
                        
                        <!-- Schema Markup -->
                        <div class="skyview-seo-meta-box-item">
                            <div class="skyview-seo-meta-box-item-header">
                                <div class="skyview-seo-meta-box-item-title">
                                    <?php esc_html_e('Schema Markup', 'vidolimo-seo-auditor'); ?>
                                </div>
                                <div class="skyview-seo-meta-box-item-status">
                                    <span class="skyview-seo-meta-box-item-status-icon <?php echo esc_attr($technical['schema_markup']['status']); ?>">
                                        <?php if ($technical['schema_markup']['status'] === 'good') : ?>
                                            <span class="dashicons dashicons-yes-alt"></span>
                                        <?php elseif ($technical['schema_markup']['status'] === 'warning') : ?>
                                            <span class="dashicons dashicons-warning"></span>
                                        <?php else : ?>
                                            <span class="dashicons dashicons-dismiss"></span>
                                        <?php endif; ?>
                                    </span>
                                </div>
                            </div>
                            <div class="skyview-seo-meta-box-item-message">
                                <?php echo esc_html($technical['schema_markup']['message']); ?>
                            </div>
                        </div>
                        
                        <!-- Canonical Tags -->
                        <div class="skyview-seo-meta-box-item">
                            <div class="skyview-seo-meta-box-item-header">
                                <div class="skyview-seo-meta-box-item-title">
                                    <?php esc_html_e('Canonical Tags', 'vidolimo-seo-auditor'); ?>
                                </div>
                                <div class="skyview-seo-meta-box-item-status">
                                    <span class="skyview-seo-meta-box-item-status-icon <?php echo esc_attr($technical['canonical_tags']['status']); ?>">
                                        <?php if ($technical['canonical_tags']['status'] === 'good') : ?>
                                            <span class="dashicons dashicons-yes-alt"></span>
                                        <?php elseif ($technical['canonical_tags']['status'] === 'warning') : ?>
                                            <span class="dashicons dashicons-warning"></span>
                                        <?php else : ?>
                                            <span class="dashicons dashicons-dismiss"></span>
                                        <?php endif; ?>
                                    </span>
                                </div>
                            </div>
                            <div class="skyview-seo-meta-box-item-message">
                                <?php echo esc_html($technical['canonical_tags']['message']); ?>
                            </div>
                            <?php if (!empty($technical['canonical_tags']['value']['url'])) : ?>
                                <div class="skyview-seo-meta-box-item-details">
                                    <code><?php echo esc_html($technical['canonical_tags']['value']['url']); ?></code>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
} // End of skyview_seo_meta_box_content function

// Execute the function to display the meta box when context is available.
if (isset($post) && is_object($post)) {
    skyview_seo_meta_box_content($post, isset($analysis) ? $analysis : array());
}
?>
