<?php
/**
 * Performance analyzer class
 *
 * @package SkyView_SEO
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * SkyView SEO Performance Analyzer class
 */
class SkyView_SEO_Performance_Analyzer {

    /**
     * Constructor
     */
    public function __construct() {
        // Nothing to do here
    }

    /**
     * Analyze performance
     *
     * @param WP_Post $post     Post object
     * @param string  $post_url Post URL
     * @return array Analysis results
     */
    public function analyze($post, $post_url) {
        // Initialize results
        $results = array(
            'page_load_speed' => array(
                'value' => $this->estimate_page_load_speed($post),
                'status' => 'good',
                'message' => '',
            ),
            'core_web_vitals' => array(
                'lcp' => array(
                    'value' => 0,
                    'status' => 'unknown',
                    'message' => '',
                ),
                'fid' => array(
                    'value' => 0,
                    'status' => 'unknown',
                    'message' => '',
                ),
                'cls' => array(
                    'value' => 0,
                    'status' => 'unknown',
                    'message' => '',
                ),
                'status' => 'unknown',
                'message' => '',
            ),
            'mobile_responsiveness' => array(
                'value' => $this->check_mobile_responsiveness($post),
                'status' => 'good',
                'message' => '',
            ),
            'issues' => array(),
            'score' => 0,
        );

        // Analyze page load speed
        $results = $this->analyze_page_load_speed($results);

        // Analyze mobile responsiveness
        $results = $this->analyze_mobile_responsiveness($results);

        // Calculate overall score
        $results['score'] = $this->calculate_score($results);

        return $results;
    }

    /**
     * Estimate page load speed
     *
     * @param WP_Post $post Post object
     * @return int Estimated load time in ms
     */
    private function estimate_page_load_speed($post) {
        // This is a simplified estimation based on content size and complexity
        $content = $post->post_content;
        $content_size = strlen($content);
        
        // Base load time (ms)
        $load_time = 500;
        
        // Add time based on content size
        $load_time += $content_size / 100;
        
        // Add time for images
        $image_count = substr_count($content, '<img');
        $load_time += $image_count * 200;
        
        // Add time for scripts
        $script_count = substr_count($content, '<script');
        $load_time += $script_count * 300;
        
        // Add time for iframes
        $iframe_count = substr_count($content, '<iframe');
        $load_time += $iframe_count * 500;
        
        // Add time for shortcodes
        $shortcode_count = substr_count($content, '[');
        $load_time += $shortcode_count * 100;
        
        return round($load_time);
    }

    /**
     * Check mobile responsiveness
     *
     * @param WP_Post $post Post object
     * @return bool True if responsive, false otherwise
     */
    private function check_mobile_responsiveness($post) {
        // Check theme support
        $theme_support = current_theme_supports('responsive-embeds');
        
        // Check for viewport meta tag
        $viewport_meta = false;
        $theme_head = get_option('theme_mods_' . get_stylesheet());
        if ($theme_head && isset($theme_head['custom_header_meta'])) {
            $viewport_meta = strpos($theme_head['custom_header_meta'], 'viewport') !== false;
        }
        
        // Check for responsive CSS in content
        $responsive_css = false;
        $content = $post->post_content;
        if (strpos($content, '@media') !== false || strpos($content, 'max-width') !== false) {
            $responsive_css = true;
        }
        
        // Check for non-responsive elements
        $non_responsive_elements = false;
        if (strpos($content, 'width=') !== false && strpos($content, '%') === false) {
            $non_responsive_elements = true;
        }
        
        // Determine overall responsiveness
        return ($theme_support || $viewport_meta || $responsive_css) && !$non_responsive_elements;
    }

    /**
     * Analyze page load speed
     *
     * @param array $results Analysis results
     * @return array Updated results
     */
    private function analyze_page_load_speed($results) {
        $load_time = $results['page_load_speed']['value'];
        
        // Set status based on load time
        if ($load_time < 1000) {
            $results['page_load_speed']['status'] = 'good';
            $results['page_load_speed']['message'] = sprintf(
                /* translators: %d: estimated page load time in milliseconds */
                __('Estimated page load time is fast: %d ms', 'vidolimo-seo-auditor'),
                absint($load_time)
            );
        } elseif ($load_time < 3000) {
            $results['page_load_speed']['status'] = 'warning';
            $results['page_load_speed']['message'] = sprintf(
                /* translators: %d: estimated page load time in milliseconds */
                __('Estimated page load time is moderate: %d ms', 'vidolimo-seo-auditor'),
                absint($load_time)
            );
            $results['issues'][] = array(
                'type' => 'moderate_load_time',
                'severity' => 'info',
                'message' => $results['page_load_speed']['message'],
            );
        } else {
            $results['page_load_speed']['status'] = 'bad';
            $results['page_load_speed']['message'] = sprintf(
                /* translators: %d: estimated page load time in milliseconds */
                __('Estimated page load time is slow: %d ms', 'vidolimo-seo-auditor'),
                absint($load_time)
            );
            $results['issues'][] = array(
                'type' => 'slow_load_time',
                'severity' => 'warning',
                'message' => $results['page_load_speed']['message'],
            );
        }
        
        return $results;
    }

    /**
     * Analyze mobile responsiveness
     *
     * @param array $results Analysis results
     * @return array Updated results
     */
    private function analyze_mobile_responsiveness($results) {
        $is_responsive = $results['mobile_responsiveness']['value'];
        
        if ($is_responsive) {
            $results['mobile_responsiveness']['status'] = 'good';
            $results['mobile_responsiveness']['message'] = __('Page appears to be mobile-responsive', 'vidolimo-seo-auditor');
        } else {
            $results['mobile_responsiveness']['status'] = 'bad';
            $results['mobile_responsiveness']['message'] = __('Page may not be mobile-responsive', 'vidolimo-seo-auditor');
            $results['issues'][] = array(
                'type' => 'not_mobile_responsive',
                'severity' => 'critical',
                'message' => $results['mobile_responsiveness']['message'],
            );
        }
        
        return $results;
    }

    /**
     * Calculate overall score
     *
     * @param array $results Analysis results
     * @return int Score (0-100)
     */
    private function calculate_score($results) {
        // Define weights
        $weights = array(
            'page_load_speed' => 0.6,
            'mobile_responsiveness' => 0.4,
        );
        
        // Calculate score
        $score = 0;
        
        // Page load speed score
        if ($results['page_load_speed']['status'] === 'good') {
            $score += 100 * $weights['page_load_speed'];
        } elseif ($results['page_load_speed']['status'] === 'warning') {
            $score += 60 * $weights['page_load_speed'];
        } else {
            $score += 30 * $weights['page_load_speed'];
        }
        
        // Mobile responsiveness score
        if ($results['mobile_responsiveness']['status'] === 'good') {
            $score += 100 * $weights['mobile_responsiveness'];
        } else {
            $score += 0 * $weights['mobile_responsiveness'];
        }
        
        return round($score);
    }
}
