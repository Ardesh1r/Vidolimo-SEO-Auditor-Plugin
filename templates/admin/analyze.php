<?php
/**
 * Individual page analysis template
 *
 * @package SkyView_SEO
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Generate and display page analysis
 */
function skyview_seo_analyze_page() {
    // Validate request nonce.
    $analysis_nonce = isset($_GET['_wpnonce']) ? sanitize_text_field(wp_unslash($_GET['_wpnonce'])) : '';
    if (empty($analysis_nonce) || !wp_verify_nonce($analysis_nonce, 'skyview-seo-analyze')) {
        wp_die(esc_html__('Security check failed.', 'vidolimo-seo-auditor'));
    }

    // Get post ID from URL
    $post_id = isset($_GET['post_id']) ? absint($_GET['post_id']) : 0;

    if (!$post_id) {
        wp_die(esc_html__('No post ID provided.', 'vidolimo-seo-auditor'));
    }

    // Get post
    $post = get_post($post_id);

    if (!$post) {
        wp_die(esc_html__('Post not found.', 'vidolimo-seo-auditor'));
    }

// Initialize analyzers
$content_analyzer = new SkyView_SEO_Content_Analyzer();
$link_analyzer = new SkyView_SEO_Link_Analyzer();
$image_analyzer = new SkyView_SEO_Image_Analyzer();
$technical_analyzer = new SkyView_SEO_Technical_Analyzer();

// Run analyses
$content_analysis = $content_analyzer->analyze($post);
$link_analysis = $link_analyzer->analyze($post);
$image_analysis = $image_analyzer->analyze($post);
$technical_analysis = $technical_analyzer->analyze($post, get_permalink($post->ID));

// Convert link URLs to proper format with anchor text
if (!empty($link_analysis['internal_links']['urls'])) {
    $link_analysis['internal_links']['links'] = array();
    foreach ($link_analysis['internal_links']['urls'] as $url) {
        $link_analysis['internal_links']['links'][] = array(
            'url' => $url,
            'anchor_text' => '',
        );
    }
}

if (!empty($link_analysis['external_links']['urls'])) {
    $link_analysis['external_links']['links'] = array();
    foreach ($link_analysis['external_links']['urls'] as $url) {
        $link_analysis['external_links']['links'][] = array(
            'url' => $url,
            'anchor_text' => '',
        );
    }
}

if (!empty($link_analysis['broken_links']['urls'])) {
    $link_analysis['broken_links']['links'] = array();
    foreach ($link_analysis['broken_links']['urls'] as $url) {
        $link_analysis['broken_links']['links'][] = array(
            'url' => $url,
            'anchor_text' => '',
        );
    }
}

// Enhance image analysis with file sizes
if (isset($image_analysis['missing_alt']['images'])) {
    foreach ($image_analysis['missing_alt']['images'] as &$image) {
        if (!empty($image['src'])) {
            $image_path = str_replace(home_url(), ABSPATH, $image['src']);
            if (file_exists($image_path)) {
                $size_bytes = filesize($image_path);
                $image['size'] = size_format($size_bytes, 2);
            }
        }
    }
}

if (isset($image_analysis['large_images']['images'])) {
    foreach ($image_analysis['large_images']['images'] as &$image) {
        if (!empty($image['src'])) {
            $image_path = str_replace(home_url(), ABSPATH, $image['src']);
            if (file_exists($image_path)) {
                $size_bytes = filesize($image_path);
                $image['size'] = size_format($size_bytes, 2);
            }
        }
    }
}

// Prepare issues list with image data
$image_issues = array();
$missing_alt_urls = array_column($image_analysis['missing_alt']['images'], 'src');
$large_image_urls = array_column($image_analysis['large_images']['images'], 'src');

// Consolidate all unique image URLs with issues
$all_issue_urls = array_unique(array_merge($missing_alt_urls, $large_image_urls));

foreach ($all_issue_urls as $url) {
    $messages = [];
    $is_missing_alt = in_array($url, $missing_alt_urls);
    $is_large = in_array($url, $large_image_urls);
    $size = '';

    if ($is_missing_alt) {
        $messages[] = __('Missing alt text', 'vidolimo-seo-auditor');
    }

    if ($is_large) {
        $messages[] = __('Image larger than 500 KB', 'vidolimo-seo-auditor');
        // Find the image to get its size
        foreach ($image_analysis['large_images']['images'] as $large_image) {
            if ($large_image['src'] === $url) {
                $size = isset($large_image['size']) ? $large_image['size'] : '';
                break;
            }
        }
    }

    if (!empty($messages)) {
        $image_issues[] = array(
            'url' => $url,
            'message' => implode(' and ', $messages),
            'severity' => (count($messages) > 1) ? 'critical' : 'warning',
            'size' => $size,
        );
    }
}

$image_analysis['issues'] = $image_issues;

// Calculate overall score
$overall_score = round(
    ($content_analysis['score'] + $link_analysis['score'] + $image_analysis['score'] + $technical_analysis['score']) / 4
);
?>

<div class="wrap skyview-seo-wrap">
    <h1 class="skyview-seo-title">
        <img style="background: black; border-radius: 50px;" src="<?php echo esc_url(SKYVIEW_SEO_PLUGIN_URL . 'assets/logo.png'); ?>" alt="Vidolimo SEO Auditor" class="skyview-seo-logo">
        <?php esc_html_e('Vidolimo Analysis', 'vidolimo-seo-auditor'); ?>
    </h1>

    <div class="skyview-seo-analyze-header">
        <div class="skyview-seo-analyze-title">
            <h2><?php echo esc_html($post->post_title); ?></h2>
            <p class="skyview-seo-analyze-url">
                <a href="<?php echo esc_url(get_permalink($post->ID)); ?>" target="_blank">
                    <?php echo esc_html(get_permalink($post->ID)); ?>
                </a>
            </p>
        </div>
        <div class="skyview-seo-analyze-score">
            <div class="skyview-seo-score-circle <?php echo esc_attr($overall_score >= 80 ? 'good' : ($overall_score >= 60 ? 'warning' : 'bad')); ?>">
                <div class="skyview-seo-score-number"><?php echo esc_html($overall_score); ?></div>
                <div class="skyview-seo-score-label"><?php esc_html_e('Overall Score', 'vidolimo-seo-auditor'); ?></div>
            </div>
        </div>
    </div>

    <div class="skyview-seo-analyze-tabs">
        <ul class="skyview-seo-tabs-nav">
            <li><a href="#content-tab" class="skyview-seo-tab-link active"><?php esc_html_e('Content', 'vidolimo-seo-auditor'); ?></a></li>
            <li><a href="#links-tab" class="skyview-seo-tab-link"><?php esc_html_e('Links', 'vidolimo-seo-auditor'); ?></a></li>
            <li><a href="#images-tab" class="skyview-seo-tab-link"><?php esc_html_e('Images', 'vidolimo-seo-auditor'); ?></a></li>
            <li><a href="#technical-tab" class="skyview-seo-tab-link"><?php esc_html_e('Technical', 'vidolimo-seo-auditor'); ?></a></li>
        </ul>

        <!-- Content Tab -->
        <div id="content-tab" class="skyview-seo-tab-content active">
            <div class="skyview-seo-analysis-section">
                <h3><?php esc_html_e('Content Analysis', 'vidolimo-seo-auditor'); ?></h3>
                
                <div class="skyview-seo-analysis-item">
                    <div class="skyview-seo-analysis-label">
                        <?php esc_html_e('Title', 'vidolimo-seo-auditor'); ?>
                        <span class="skyview-seo-help-icon">?<span class="skyview-seo-tooltip"><?php esc_html_e('Title should be 50-60 characters long and include your main keyword', 'vidolimo-seo-auditor'); ?></span></span>
                    </div>
                    <div class="skyview-seo-analysis-value">
                        <span class="skyview-seo-status <?php echo esc_attr($content_analysis['title']['status']); ?>">
                            <?php echo esc_html($content_analysis['title']['value']); ?>
                        </span>
                        <p class="skyview-seo-analysis-message"><?php echo esc_html($content_analysis['title']['message']); ?></p>
                        <p class="skyview-seo-analysis-detail">
                            <?php
                            /* translators: %d: number of characters in the title */
                            echo sprintf(esc_html__('Length: %d characters', 'vidolimo-seo-auditor'), absint($content_analysis['title']['length']));
                            ?>
                        </p>
                    </div>
                </div>

                <div class="skyview-seo-analysis-item">
                    <div class="skyview-seo-analysis-label">
                        <?php esc_html_e('Meta Description', 'vidolimo-seo-auditor'); ?>
                        <span class="skyview-seo-help-icon">?<span class="skyview-seo-tooltip"><?php esc_html_e('Meta description should be 120-160 characters and summarize your page content', 'vidolimo-seo-auditor'); ?></span></span>
                    </div>
                    <div class="skyview-seo-analysis-value">
                        <span class="skyview-seo-status <?php echo esc_attr($content_analysis['meta_description']['status']); ?>">
                            <?php echo esc_html(substr($content_analysis['meta_description']['value'], 0, 100)); ?>...
                        </span>
                        <p class="skyview-seo-analysis-message"><?php echo esc_html($content_analysis['meta_description']['message']); ?></p>
                        <p class="skyview-seo-analysis-detail">
                            <?php
                            /* translators: %d: number of characters in the meta description */
                            echo sprintf(esc_html__('Length: %d characters', 'vidolimo-seo-auditor'), absint($content_analysis['meta_description']['length']));
                            ?>
                        </p>
                    </div>
                </div>

                <div class="skyview-seo-analysis-item">
                    <div class="skyview-seo-analysis-label">
                        <?php esc_html_e('Word Count', 'vidolimo-seo-auditor'); ?>
                        <span class="skyview-seo-help-icon">?<span class="skyview-seo-tooltip"><?php esc_html_e('Aim for at least 300 words for blog posts and 500+ for key pages', 'vidolimo-seo-auditor'); ?></span></span>
                    </div>
                    <div class="skyview-seo-analysis-value">
                        <span class="skyview-seo-status <?php echo esc_attr($content_analysis['word_count']['status']); ?>">
                            <?php echo esc_html($content_analysis['word_count']['value']); ?> <?php esc_html_e('words', 'vidolimo-seo-auditor'); ?>
                        </span>
                        <p class="skyview-seo-analysis-message"><?php echo esc_html($content_analysis['word_count']['message']); ?></p>
                    </div>
                </div>

                <div class="skyview-seo-analysis-item">
                    <div class="skyview-seo-analysis-label">
                        <?php esc_html_e('Reading Time', 'vidolimo-seo-auditor'); ?>
                        <span class="skyview-seo-help-icon">?<span class="skyview-seo-tooltip"><?php esc_html_e('Estimated reading time based on average reading speed of 225 words per minute', 'vidolimo-seo-auditor'); ?></span></span>
                    </div>
                    <div class="skyview-seo-analysis-value">
                        <span class="skyview-seo-status <?php echo esc_attr($content_analysis['reading_time']['status']); ?>">
                            <?php echo esc_html($content_analysis['reading_time']['value']); ?> <?php esc_html_e('minutes', 'vidolimo-seo-auditor'); ?>
                        </span>
                    </div>
                </div>

                <div class="skyview-seo-analysis-item">
                    <div class="skyview-seo-analysis-label">
                        <?php esc_html_e('Content Quality Score', 'vidolimo-seo-auditor'); ?>
                        <span class="skyview-seo-help-icon">?<span class="skyview-seo-tooltip"><?php esc_html_e('Measures content structure and formatting. Considers: proper heading hierarchy (H1-H6), presence of images, bullet points/lists, internal links, paragraph length, and overall readability. Higher scores indicate better structured content.', 'vidolimo-seo-auditor'); ?></span></span>
                    </div>
                    <div class="skyview-seo-analysis-value">
                        <span class="skyview-seo-status <?php echo esc_attr($content_analysis['content_quality']['status']); ?>">
                            <?php echo esc_html($content_analysis['content_quality']['value']); ?>/100
                        </span>
                        <p class="skyview-seo-analysis-message"><?php echo esc_html($content_analysis['content_quality']['message']); ?></p>
                        <div class="skyview-seo-quality-breakdown">
                            <strong><?php esc_html_e('To Improve Your Score:', 'vidolimo-seo-auditor'); ?></strong>
                            <ul>
                                <li><?php echo ($content_analysis['content_quality']['value'] >= 100) ? '✓' : '○'; ?> <?php esc_html_e('Use proper heading structure (H1, H2, H3) throughout content', 'vidolimo-seo-auditor'); ?></li>
                                <li><?php echo ($content_analysis['content_quality']['value'] >= 100) ? '✓' : '○'; ?> <?php esc_html_e('Add relevant images with descriptive alt text', 'vidolimo-seo-auditor'); ?></li>
                                <li><?php echo ($content_analysis['content_quality']['value'] >= 100) ? '✓' : '○'; ?> <?php esc_html_e('Include bullet points and numbered lists', 'vidolimo-seo-auditor'); ?></li>
                                <li><?php echo ($content_analysis['content_quality']['value'] >= 100) ? '✓' : '○'; ?> <?php esc_html_e('Add 3-5 internal links to related content', 'vidolimo-seo-auditor'); ?></li>
                                <li><?php echo ($content_analysis['content_quality']['value'] >= 100) ? '✓' : '○'; ?> <?php esc_html_e('Keep paragraphs short (2-4 sentences)', 'vidolimo-seo-auditor'); ?></li>
                                <li><?php echo ($content_analysis['content_quality']['value'] >= 100) ? '✓' : '○'; ?> <?php esc_html_e('Vary sentence length for better readability', 'vidolimo-seo-auditor'); ?></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <?php if (!empty($content_analysis['issues'])) : ?>
                <div class="skyview-seo-issues-section">
                    <h3><?php esc_html_e('Issues Found', 'vidolimo-seo-auditor'); ?></h3>
                    <div class="skyview-seo-issues-list">
                        <?php foreach ($content_analysis['issues'] as $issue) : ?>
                            <div class="skyview-seo-issue-item <?php echo esc_attr($issue['severity']); ?>">
                                <div class="skyview-seo-issue-header">
                                    <span class="skyview-seo-issue-icon">!</span>
                                    <span class="skyview-seo-issue-message"><?php echo esc_html($issue['message']); ?></span>
                                </div>
                                <?php if (!empty($issue['details'])) : ?>
                                    <div class="skyview-seo-issue-detail">
                                        <strong><?php esc_html_e('Details:', 'vidolimo-seo-auditor'); ?></strong>
                                        <p><?php echo esc_html($issue['details']); ?></p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Links Tab -->
        <div id="links-tab" class="skyview-seo-tab-content">
            <div class="skyview-seo-analysis-section">
                <h3><?php esc_html_e('Link Analysis', 'vidolimo-seo-auditor'); ?></h3>
                
                <div class="skyview-seo-analysis-item">
                    <div class="skyview-seo-analysis-label">
                        <?php esc_html_e('Internal Links', 'vidolimo-seo-auditor'); ?>
                        <span class="skyview-seo-help-icon">?<span class="skyview-seo-tooltip"><?php esc_html_e('Links to other pages on your site. Aim for 3-5 internal links per page', 'vidolimo-seo-auditor'); ?></span></span>
                    </div>
                    <div class="skyview-seo-analysis-value">
                        <span class="skyview-seo-status <?php echo esc_attr($link_analysis['internal_links']['status']); ?>">
                            <?php echo esc_html($link_analysis['internal_links']['count']); ?>
                        </span>
                        <p class="skyview-seo-analysis-message"><?php echo esc_html($link_analysis['internal_links']['message']); ?></p>
                        <div class="skyview-seo-links-list">
                            <?php if (!empty($link_analysis['internal_links']['links'])) : ?>
                                <strong><?php esc_html_e('Internal Links Found:', 'vidolimo-seo-auditor'); ?></strong>
                                <ul>
                                    <?php foreach ($link_analysis['internal_links']['links'] as $link) : ?>
                                        <li>
                                            <a href="<?php echo esc_url($link['url']); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html($link['url']); ?></a>
                                            <?php if (!empty($link['anchor_text'])) : ?>
                                                <span class="skyview-seo-anchor-text">(Anchor: <?php echo esc_html($link['anchor_text']); ?>)</span>
                                            <?php endif; ?>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php else: ?>
                                <p><?php esc_html_e('No internal links were found on this page.', 'vidolimo-seo-auditor'); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="skyview-seo-analysis-item">
                    <div class="skyview-seo-analysis-label">
                        <?php esc_html_e('External Links', 'vidolimo-seo-auditor'); ?>
                        <span class="skyview-seo-help-icon">?<span class="skyview-seo-tooltip"><?php esc_html_e('Links to external websites. Keep to 2-4 authoritative sources', 'vidolimo-seo-auditor'); ?></span></span>
                    </div>
                    <div class="skyview-seo-analysis-value">
                        <span class="skyview-seo-status <?php echo esc_attr($link_analysis['external_links']['status']); ?>">
                            <?php echo esc_html($link_analysis['external_links']['count']); ?>
                        </span>
                        <p class="skyview-seo-analysis-message"><?php echo esc_html($link_analysis['external_links']['message']); ?></p>
                        <div class="skyview-seo-links-list">
                            <?php if (!empty($link_analysis['external_links']['links'])) : ?>
                                <strong><?php esc_html_e('External Links Found:', 'vidolimo-seo-auditor'); ?></strong>
                                <ul>
                                    <?php foreach ($link_analysis['external_links']['links'] as $link) : ?>
                                        <li>
                                            <a href="<?php echo esc_url($link['url']); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html($link['url']); ?></a>
                                            <?php if (!empty($link['anchor_text'])) : ?>
                                                <span class="skyview-seo-anchor-text">(Anchor: <?php echo esc_html($link['anchor_text']); ?>)</span>
                                            <?php endif; ?>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php else: ?>
                                <p><?php esc_html_e('No external links were found on this page.', 'vidolimo-seo-auditor'); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="skyview-seo-analysis-item">
                    <div class="skyview-seo-analysis-label">
                        <?php esc_html_e('Broken Links', 'vidolimo-seo-auditor'); ?>
                        <span class="skyview-seo-help-icon">?<span class="skyview-seo-tooltip"><?php esc_html_e('Links that point to non-existent pages. Should be zero', 'vidolimo-seo-auditor'); ?></span></span>
                    </div>
                    <div class="skyview-seo-analysis-value">
                        <span class="skyview-seo-status <?php echo esc_attr($link_analysis['broken_links']['status']); ?>">
                            <?php echo esc_html($link_analysis['broken_links']['count']); ?>
                        </span>
                        <p class="skyview-seo-analysis-message"><?php echo esc_html($link_analysis['broken_links']['message']); ?></p>
                        <div class="skyview-seo-links-list">
                            <?php if (!empty($link_analysis['broken_links']['links'])) : ?>
                                <strong><?php esc_html_e('Broken Links Found:', 'vidolimo-seo-auditor'); ?></strong>
                                <ul>
                                    <?php foreach ($link_analysis['broken_links']['links'] as $link) : ?>
                                        <li>
                                            <a href="<?php echo esc_url($link['url']); ?>" target="_blank" rel="noopener noreferrer" class="skyview-seo-broken-link"><?php echo esc_html($link['url']); ?></a>
                                            <?php if (!empty($link['anchor_text'])) : ?>
                                                <span class="skyview-seo-anchor-text">(Anchor: <?php echo esc_html($link['anchor_text']); ?>)</span>
                                            <?php endif; ?>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php else: ?>
                                <p><?php esc_html_e('No broken links were found on this page.', 'vidolimo-seo-auditor'); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

            </div>

            <?php if (!empty($link_analysis['issues'])) : ?>
                <div class="skyview-seo-issues-section">
                    <h3><?php esc_html_e('Issues Found', 'vidolimo-seo-auditor'); ?></h3>
                    <div class="skyview-seo-issues-list">
                        <?php foreach ($link_analysis['issues'] as $issue) : ?>
                            <div class="skyview-seo-issue-item <?php echo esc_attr($issue['severity']); ?>">
                                <div class="skyview-seo-issue-header">
                                    <span class="skyview-seo-issue-icon">!</span>
                                    <span class="skyview-seo-issue-message"><?php echo esc_html($issue['message']); ?></span>
                                </div>
                                <?php if (!empty($issue['url'])) : ?>
                                    <div class="skyview-seo-issue-detail">
                                        <strong><?php esc_html_e('Link:', 'vidolimo-seo-auditor'); ?></strong>
                                        <a href="<?php echo esc_url($issue['url']); ?>" target="_blank" rel="noopener noreferrer" class="skyview-seo-issue-link">
                                            <?php echo esc_html($issue['url']); ?>
                                        </a>
                                    </div>
                                <?php endif; ?>
                                <?php if (!empty($issue['anchor_text'])) : ?>
                                    <div class="skyview-seo-issue-detail">
                                        <strong><?php esc_html_e('Anchor Text:', 'vidolimo-seo-auditor'); ?></strong>
                                        <span><?php echo esc_html($issue['anchor_text']); ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Images Tab -->
        <div id="images-tab" class="skyview-seo-tab-content">
            <div class="skyview-seo-analysis-section">
                <h3><?php esc_html_e('Image Analysis', 'vidolimo-seo-auditor'); ?></h3>
                
                <div class="skyview-seo-analysis-item">
                    <div class="skyview-seo-analysis-label">
                        <?php esc_html_e('Total Images', 'vidolimo-seo-auditor'); ?>
                        <span class="skyview-seo-help-icon">?<span class="skyview-seo-tooltip"><?php esc_html_e('Total number of images on this page', 'vidolimo-seo-auditor'); ?></span></span>
                    </div>
                    <div class="skyview-seo-analysis-value">
                        <span class="skyview-seo-status good">
                            <?php echo esc_html($image_analysis['total_images']); ?>
                        </span>
                    </div>
                </div>

                <div class="skyview-seo-analysis-item">
                    <div class="skyview-seo-analysis-label">
                        <?php esc_html_e('Missing Alt Text', 'vidolimo-seo-auditor'); ?>
                        <span class="skyview-seo-help-icon">?<span class="skyview-seo-tooltip"><?php esc_html_e('Images without alt text. Alt text helps with accessibility and SEO', 'vidolimo-seo-auditor'); ?></span></span>
                    </div>
                    <div class="skyview-seo-analysis-value">
                        <span class="skyview-seo-status <?php echo esc_attr($image_analysis['missing_alt']['status']); ?>">
                            <?php echo esc_html($image_analysis['missing_alt']['count']); ?>
                        </span>
                        <p class="skyview-seo-analysis-message"><?php echo esc_html($image_analysis['missing_alt']['message']); ?></p>
                        
                        <?php if (!empty($image_analysis['missing_alt']['images'])) : ?>
                            <div class="skyview-seo-images-preview">
                                <?php foreach ($image_analysis['missing_alt']['images'] as $image) : ?>
                                    <div class="skyview-seo-image-preview-item">
                                        <div class="skyview-seo-image-thumbnail">
                                            <img src="<?php echo esc_url($image['src']); ?>" alt="<?php esc_attr_e('Image missing alt text', 'vidolimo-seo-auditor'); ?>" loading="lazy">
                                        </div>
                                        <div class="skyview-seo-image-details">
                                            <div class="skyview-seo-image-url">
                                                <a href="<?php echo esc_url($image['src']); ?>" target="_blank" rel="noopener noreferrer" title="<?php echo esc_attr($image['src']); ?>">
                                                    <?php echo esc_html(basename($image['src'])); ?>
                                                </a>
                                            </div>
                                            <?php if (!empty($image['size'])) : ?>
                                                <div class="skyview-seo-image-size"><?php echo esc_html($image['size']); ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="skyview-seo-analysis-item">
                    <div class="skyview-seo-analysis-label">
                        <?php esc_html_e('Large Images', 'vidolimo-seo-auditor'); ?>
                        <span class="skyview-seo-help-icon">?<span class="skyview-seo-tooltip"><?php esc_html_e('Images larger than 500 KB. Compress to improve page speed', 'vidolimo-seo-auditor'); ?></span></span>
                    </div>
                    <div class="skyview-seo-analysis-value">
                        <span class="skyview-seo-status <?php echo esc_attr($image_analysis['large_images']['status']); ?>">
                            <?php echo esc_html($image_analysis['large_images']['count']); ?>
                        </span>
                        <p class="skyview-seo-analysis-message"><?php echo esc_html($image_analysis['large_images']['message']); ?></p>
                        
                        <?php if (!empty($image_analysis['large_images']['images'])) : ?>
                            <div class="skyview-seo-images-preview">
                                <?php foreach ($image_analysis['large_images']['images'] as $image) : ?>
                                    <div class="skyview-seo-image-preview-item">
                                        <div class="skyview-seo-image-thumbnail">
                                            <img src="<?php echo esc_url($image['src']); ?>" alt="<?php esc_attr_e('Large image', 'vidolimo-seo-auditor'); ?>" loading="lazy">
                                        </div>
                                        <div class="skyview-seo-image-details">
                                            <div class="skyview-seo-image-url">
                                                <a href="<?php echo esc_url($image['src']); ?>" target="_blank" rel="noopener noreferrer" title="<?php echo esc_attr($image['src']); ?>">
                                                    <?php echo esc_html(basename($image['src'])); ?>
                                                </a>
                                            </div>
                                            <div class="skyview-seo-image-size">
                                                <strong><?php esc_html_e('Size:', 'vidolimo-seo-auditor'); ?></strong> <?php echo !empty($image['size']) ? esc_html($image['size']) : 'Unknown'; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <?php if (!empty($image_analysis['issues'])) : ?>
                <div class="skyview-seo-issues-section">
                    <h3><?php esc_html_e('Issues Found', 'vidolimo-seo-auditor'); ?></h3>
                    <div class="skyview-seo-issues-grid">
                        <?php foreach ($image_analysis['issues'] as $issue) : ?>
                            <div class="skyview-seo-issue-card <?php echo esc_attr($issue['severity']); ?>">
                                <div class="skyview-seo-issue-header">
                                    <span class="skyview-seo-issue-icon">!</span>
                                    <span class="skyview-seo-issue-message"><?php echo esc_html($issue['message']); ?></span>
                                </div>
                                <?php if (!empty($issue['url'])) : ?>
                                    <div class="skyview-seo-issue-image">
                                        <img src="<?php echo esc_url($issue['url']); ?>" alt="<?php esc_attr_e('Issue image', 'vidolimo-seo-auditor'); ?>" loading="lazy" />
                                    </div>
                                    <div class="skyview-seo-issue-url">
                                        <strong><?php esc_html_e('Image URL:', 'vidolimo-seo-auditor'); ?></strong><br>
                                        <a href="<?php echo esc_url($issue['url']); ?>" target="_blank" rel="noopener noreferrer" style="word-break: break-all; font-size: 12px;">
                                            <?php echo esc_html($issue['url']); ?>
                                        </a>
                                    </div>
                                <?php endif; ?>
                                <?php if (!empty($issue['size'])) : ?>
                                    <div class="skyview-seo-issue-size">
                                        <strong><?php esc_html_e('File Size:', 'vidolimo-seo-auditor'); ?></strong> <?php echo esc_html($issue['size']); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php else : ?>
                <div class="skyview-seo-no-issues">
                    <p><?php esc_html_e('No image issues found. All images are optimized!', 'vidolimo-seo-auditor'); ?></p>
                </div>
                <br>
            <?php endif; ?>
        </div>

        <!-- Technical Tab -->
        <div id="technical-tab" class="skyview-seo-tab-content">
            <div class="skyview-seo-analysis-section">
                <h3><?php esc_html_e('Technical SEO', 'vidolimo-seo-auditor'); ?></h3>
                
                <div class="skyview-seo-analysis-item">
                    <div class="skyview-seo-analysis-label">
                        <?php esc_html_e('HTTPS Status', 'vidolimo-seo-auditor'); ?>
                        <span class="skyview-seo-help-icon">?<span class="skyview-seo-tooltip"><?php esc_html_e('Your site should use HTTPS for security', 'vidolimo-seo-auditor'); ?></span></span>
                    </div>
                    <div class="skyview-seo-analysis-value">
                        <span class="skyview-seo-status <?php echo esc_attr($technical_analysis['https_status']['status']); ?>">
                            <?php echo $technical_analysis['https_status']['value'] ? esc_html__('Yes', 'vidolimo-seo-auditor') : esc_html__('No', 'vidolimo-seo-auditor'); ?>
                        </span>
                        <p class="skyview-seo-analysis-message"><?php echo esc_html($technical_analysis['https_status']['message']); ?></p>
                    </div>
                <div class="skyview-seo-analysis-item">
                   <div class="skyview-seo-analysis-label">
                       <?php esc_html_e('Schema Markup', 'vidolimo-seo-auditor'); ?>
                       <span class="skyview-seo-help-icon">?<span class="skyview-seo-tooltip"><?php esc_html_e('Use the following tools to validate your structured data. Structured data (JSON-LD) helps search engines understand your content and can enable rich snippets in search results.', 'vidolimo-seo-auditor'); ?></span></span>
                   </div>
                   <div class="skyview-seo-analysis-value">
                       <div class="skyview-seo-schema-tools">
                           <p><?php esc_html_e('Use these tools to validate your page’s schema markup:', 'vidolimo-seo-auditor'); ?></p>
                           <ul>
                               <li>
                                   <a href="https://validator.schema.org/" target="_blank" rel="noopener noreferrer">
                                       <?php esc_html_e('Schema.org Validator', 'vidolimo-seo-auditor'); ?>
                                   </a>
                                   <span class="skyview-seo-tool-desc"><?php esc_html_e('Official schema validation', 'vidolimo-seo-auditor'); ?></span>
                               </li>
                               <li>
                                   <a href="https://search.google.com/test/rich-results" target="_blank" rel="noopener noreferrer">
                                       <?php esc_html_e('Google Rich Results Test', 'vidolimo-seo-auditor'); ?>
                                   </a>
                                   <span class="skyview-seo-tool-desc"><?php esc_html_e('Test rich snippets eligibility', 'vidolimo-seo-auditor'); ?></span>
                               </li>
                               <li>
                                   <a href="https://schema.org/docs/schemas.html" target="_blank" rel="noopener noreferrer">
                                       <?php esc_html_e('Schema.org Documentation', 'vidolimo-seo-auditor'); ?>
                                   </a>
                                   <span class="skyview-seo-tool-desc"><?php esc_html_e('Complete schema reference', 'vidolimo-seo-auditor'); ?></span>
                               </li>
                           </ul>
                       </div>
                   </div>
               </div>

                <div class="skyview-seo-analysis-item">
                    <div class="skyview-seo-analysis-label">
                        <?php esc_html_e('Canonical Tag', 'vidolimo-seo-auditor'); ?>
                        <span class="skyview-seo-help-icon">?<span class="skyview-seo-tooltip"><?php esc_html_e('Canonical tags prevent duplicate content issues', 'vidolimo-seo-auditor'); ?></span></span>
                    </div>
                    <div class="skyview-seo-analysis-value">
                        <span class="skyview-seo-status <?php echo esc_attr($technical_analysis['canonical_tags']['status']); ?>">
                            <?php echo $technical_analysis['canonical_tags']['value'] ? esc_html__('Present', 'vidolimo-seo-auditor') : esc_html__('Missing', 'vidolimo-seo-auditor'); ?>
                        </span>
                        <p class="skyview-seo-analysis-message"><?php echo esc_html($technical_analysis['canonical_tags']['message']); ?></p>
                    </div>
                </div>
            </div>

            <?php 
            // Filter out schema markup issues
            $filtered_issues = array();
            if (!empty($technical_analysis['issues'])) {
                foreach ($technical_analysis['issues'] as $issue) {
                    // Skip schema markup issues
                    if (isset($issue['type']) && $issue['type'] === 'no_schema') {
                        continue;
                    }
                    // Skip issues with 'schema markup' in the message
                    if (isset($issue['message']) && strpos(strtolower($issue['message']), 'schema markup') !== false) {
                        continue;
                    }
                    $filtered_issues[] = $issue;
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
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="skyview-seo-analyze-actions">
        <a href="<?php echo esc_url(get_edit_post_link($post->ID)); ?>" class="button button-primary">
            <?php esc_html_e('Edit Page', 'vidolimo-seo-auditor'); ?>
        </a>
        <a href="<?php echo esc_url(admin_url('admin.php?page=skyview-seo-content')); ?>" class="button">
            <?php esc_html_e('Back to Content Analysis', 'vidolimo-seo-auditor'); ?>
        </a>
    </div>
</div>



<?php
} // End of skyview_seo_analyze_page function

// Execute the function to display the page
skyview_seo_analyze_page();
?>
