<?php
/**
 * Technical SEO analyzer class
 *
 * @package SkyView_SEO
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * SkyView SEO Technical Analyzer class
 */
class SkyView_SEO_Technical_Analyzer {

    /**
     * Constructor
     */
    public function __construct() {
        // Nothing to do here
    }

    /**
     * Analyze technical SEO aspects
     *
     * @param WP_Post $post     Post object
     * @param string  $post_url Post URL
     * @return array Analysis results
     */
    public function analyze($post, $post_url) {
        // Initialize results
        $results = array(
            'https_status' => array(
                'value' => $this->check_https($post_url),
                'status' => 'good',
                'message' => '',
            ),
            'schema_markup' => array(
                'value' => $this->detect_schema_markup($post),
                'status' => 'good',
                'message' => '',
            ),
            'robots_txt' => array(
                'value' => $this->validate_robots_txt(),
                'status' => 'good',
                'message' => '',
            ),
            'canonical_tags' => array(
                'value' => $this->check_canonical_tags($post),
                'status' => 'good',
                'message' => '',
            ),
            'sitemap' => array(
                'value' => $this->validate_sitemap(),
                'status' => 'good',
                'message' => '',
            ),
            'issues' => array(),
            'score' => 0,
        );

        // Analyze HTTPS status
        $results = $this->analyze_https_status($results);

        // Analyze schema markup
        $results = $this->analyze_schema_markup($results);

        // Analyze robots.txt
        $results = $this->analyze_robots_txt($results);

        // Analyze canonical tags
        $results = $this->analyze_canonical_tags($results);

        // Analyze sitemap
        $results = $this->analyze_sitemap($results);

        // Calculate overall score
        $results['score'] = $this->calculate_score($results);

        return $results;
    }

    /**
     * Check HTTPS status
     *
     * @param string $url URL to check
     * @return bool True if HTTPS, false otherwise
     */
    private function check_https($url) {
        return strpos($url, 'https://') === 0;
    }

    /**
     * Detect schema markup
     *
     * @param WP_Post $post Post object
     * @return array Detected schema types
     */
    private function detect_schema_markup($post) {
        $schema_types = array();
        $content = $post->post_content;

        // Check for schema.org in content
        if (strpos($content, 'schema.org') !== false) {
            // Check for common schema types
            $schema_patterns = array(
                'Product' => '/["\']@type["\']\s*:\s*["\']Product["\']/i',
                'Article' => '/["\']@type["\']\s*:\s*["\']Article["\']/i',
                'BlogPosting' => '/["\']@type["\']\s*:\s*["\']BlogPosting["\']/i',
                'Organization' => '/["\']@type["\']\s*:\s*["\']Organization["\']/i',
                'Person' => '/["\']@type["\']\s*:\s*["\']Person["\']/i',
                'BreadcrumbList' => '/["\']@type["\']\s*:\s*["\']BreadcrumbList["\']/i',
                'WebPage' => '/["\']@type["\']\s*:\s*["\']WebPage["\']/i',
                'LocalBusiness' => '/["\']@type["\']\s*:\s*["\']LocalBusiness["\']/i',
            );

            foreach ($schema_patterns as $type => $pattern) {
                if (preg_match($pattern, $content)) {
                    $schema_types[] = $type;
                }
            }
        }

        // Check for schema in post meta (common for SEO plugins)
        $yoast_schema = get_post_meta($post->ID, '_yoast_wpseo_schema_article_type', true);
        if (!empty($yoast_schema)) {
            $schema_types[] = $yoast_schema;
        }

        // Check for Rank Math schema
        $rank_math_schema = get_post_meta($post->ID, 'rank_math_schema_type', true);
        if (!empty($rank_math_schema)) {
            $schema_types[] = $rank_math_schema;
        }

        return array_unique($schema_types);
    }

    /**
     * Validate robots.txt
     *
     * @return array Validation results
     */
    private function validate_robots_txt() {
        $home_url = get_home_url();
        $robots_url = trailingslashit($home_url) . 'robots.txt';
        
        // Try to get robots.txt content
        $response = wp_remote_get($robots_url);
        
        if (is_wp_error($response)) {
            return array(
                'exists' => false,
                'content' => '',
                'error' => $response->get_error_message(),
            );
        }
        
        $status_code = wp_remote_retrieve_response_code($response);
        $content = wp_remote_retrieve_body($response);
        
        if ($status_code !== 200) {
            return array(
                'exists' => false,
                'content' => '',
                /* translators: %d: HTTP status code */
                'error' => sprintf(__('HTTP error: %d', 'vidolimo-seo-auditor'), $status_code),
            );
        }
        
        return array(
            'exists' => true,
            'content' => $content,
            'error' => '',
        );
    }

    /**
     * Check canonical tags
     *
     * @param WP_Post $post Post object
     * @return array Canonical tag info
     */
    private function check_canonical_tags($post) {
        $canonical_url = '';
        
        // Check if Yoast SEO is active
        if (defined('WPSEO_VERSION')) {
            $canonical_url = get_post_meta($post->ID, '_yoast_wpseo_canonical', true);
        }
        
        // Check if All in One SEO is active
        if (empty($canonical_url) && class_exists('AIOSEO')) {
            $canonical_url = get_post_meta($post->ID, '_aioseop_canonical_url', true);
        }
        
        // Check if Rank Math is active
        if (empty($canonical_url) && class_exists('RankMath')) {
            $canonical_url = get_post_meta($post->ID, 'rank_math_canonical_url', true);
        }
        
        // If no canonical URL found, use permalink
        if (empty($canonical_url)) {
            $canonical_url = get_permalink($post->ID);
        }
        
        return array(
            'url' => $canonical_url,
            'is_set' => !empty($canonical_url),
        );
    }

    /**
     * Validate XML sitemap
     *
     * @return array Validation results
     */
    private function validate_sitemap() {
        $home = get_home_url();

        // Build candidate sitemap URLs in priority order
        $candidates = array();

        // Popular SEO plugins (if active)
        if (defined('WPSEO_VERSION')) {
            $candidates[] = $home . '/sitemap_index.xml';
        }
        if (class_exists('AIOSEO')) {
            $candidates[] = $home . '/sitemap.xml';
        }
        if (class_exists('RankMath')) {
            $candidates[] = $home . '/sitemap_index.xml';
        }

        // WordPress core sitemap (since WP 5.5)
        $candidates[] = $home . '/wp-sitemap.xml';

        // Common fallbacks
        $candidates[] = $home . '/sitemap_index.xml';
        $candidates[] = $home . '/sitemap.xml';

        // De-duplicate while preserving order
        $candidates = array_values(array_unique($candidates));

        $last_error = '';
        $last_url = '';

        foreach ($candidates as $url) {
            $last_url = $url;
            $response = wp_remote_get($url, array('timeout' => 5));

            if (is_wp_error($response)) {
                $last_error = $response->get_error_message();
                continue;
            }

            $status_code = (int) wp_remote_retrieve_response_code($response);
            if ($status_code === 200) {
                return array(
                    'exists' => true,
                    'url' => $url,
                    'error' => '',
                );
            }

            /* translators: %d: HTTP status code */
            $last_error = sprintf(__('HTTP error: %d', 'vidolimo-seo-auditor'), $status_code);
        }

        return array(
            'exists' => false,
            'url' => $last_url ?: ($home . '/wp-sitemap.xml'),
            'error' => $last_error,
        );
    }

    /**
     * Analyze HTTPS status
     *
     * @param array $results Analysis results
     * @return array Updated results
     */
    private function analyze_https_status($results) {
        if (!$results['https_status']['value']) {
            $results['https_status']['status'] = 'bad';
            $results['https_status']['message'] = __('Site is not using HTTPS', 'vidolimo-seo-auditor');
            $results['issues'][] = array(
                'type' => 'no_https',
                'severity' => 'critical',
                'message' => $results['https_status']['message'],
            );
        } else {
            $results['https_status']['status'] = 'good';
            $results['https_status']['message'] = __('Site is using HTTPS', 'vidolimo-seo-auditor');
        }
        
        return $results;
    }

    /**
     * Analyze schema markup
     *
     * @param array $results Analysis results
     * @return array Updated results
     */
    private function analyze_schema_markup($results) {
        $schema_types = $results['schema_markup']['value'];
        
        // Always set status to neutral to avoid showing in issues
        $results['schema_markup']['status'] = 'neutral';
        
        if (empty($schema_types)) {
            $results['schema_markup']['message'] = __('Not Found', 'vidolimo-seo-auditor');
            // Do not add to issues list as requested
        } else {
            $results['schema_markup']['message'] = __('Detected', 'vidolimo-seo-auditor');
            // Do not add to issues list as requested
        }
        
        // Return results without adding to issues
        return $results;
    }
    
    /**
     * Original analyze_schema_markup function (disabled)
     * @param array $results Analysis results
     * @return array Updated results
     */
    private function _disabled_analyze_schema_markup($results) {
        $schema_types = $results['schema_markup']['value'];
        
        if (empty($schema_types)) {
            $results['schema_markup']['status'] = 'warning';
            $results['schema_markup']['message'] = __('No schema markup detected', 'vidolimo-seo-auditor');
            // Do not add to issues list as requested
        } else {
            $results['schema_markup']['status'] = 'good';
            $results['schema_markup']['message'] = sprintf(
                /* translators: %s: comma-separated schema types */
                _n(
                    'Schema markup detected: %s',
                    'Schema markup detected: %s',
                    count($schema_types),
                    'vidolimo-seo-auditor'
                ),
                implode(', ', $schema_types)
            );
        }
        
        return $results;
    }

    /**
     * Analyze robots.txt
     *
     * @param array $results Analysis results
     * @return array Updated results
     */
    private function analyze_robots_txt($results) {
        $robots_txt = $results['robots_txt']['value'];
        
        if (!$robots_txt['exists']) {
            $results['robots_txt']['status'] = 'warning';
            $results['robots_txt']['message'] = __('robots.txt file not found', 'vidolimo-seo-auditor');
            $results['issues'][] = array(
                'type' => 'no_robots_txt',
                'severity' => 'warning',
                'message' => $results['robots_txt']['message'],
            );
        } else {
            // Check for common issues in robots.txt
            $content = $robots_txt['content'];
            
            // Check if site is blocked from indexing
            if (strpos($content, 'Disallow: /') !== false && strpos($content, 'Disallow: /wp-') === false) {
                $results['robots_txt']['status'] = 'bad';
                $results['robots_txt']['message'] = __('robots.txt may be blocking search engines from indexing the site', 'vidolimo-seo-auditor');
                $results['issues'][] = array(
                    'type' => 'robots_blocking_indexing',
                    'severity' => 'critical',
                    'message' => $results['robots_txt']['message'],
                );
            } else {
                $results['robots_txt']['status'] = 'good';
                $results['robots_txt']['message'] = __('robots.txt file is valid', 'vidolimo-seo-auditor');
            }
        }
        
        return $results;
    }

    /**
     * Analyze canonical tags
     *
     * @param array $results Analysis results
     * @return array Updated results
     */
    private function analyze_canonical_tags($results) {
        $canonical = $results['canonical_tags']['value'];
        
        if (!$canonical['is_set']) {
            $results['canonical_tags']['status'] = 'warning';
            $results['canonical_tags']['message'] = __('No canonical tag set', 'vidolimo-seo-auditor');
            $results['issues'][] = array(
                'type' => 'no_canonical',
                'severity' => 'warning',
                'message' => $results['canonical_tags']['message'],
            );
        } else {
            $results['canonical_tags']['status'] = 'good';
            $results['canonical_tags']['message'] = __('Canonical tag is set', 'vidolimo-seo-auditor');
        }
        
        return $results;
    }

    /**
     * Analyze sitemap
     *
     * @param array $results Analysis results
     * @return array Updated results
     */
    private function analyze_sitemap($results) {
        $sitemap = $results['sitemap']['value'];
        
        if (!$sitemap['exists']) {
            $results['sitemap']['status'] = 'warning';
            $results['sitemap']['message'] = __('XML sitemap not found', 'vidolimo-seo-auditor');
            $results['issues'][] = array(
                'type' => 'no_sitemap',
                'severity' => 'warning',
                'message' => $results['sitemap']['message'],
            );
        } else {
            $results['sitemap']['status'] = 'good';
            $results['sitemap']['message'] = __('XML sitemap is valid', 'vidolimo-seo-auditor');
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
            'https_status' => 0.3,
            'schema_markup' => 0.2,
            'robots_txt' => 0.15,
            'canonical_tags' => 0.15,
            'sitemap' => 0.2,
        );
        
        // Calculate score
        $score = 0;
        
        // HTTPS status score
        if ($results['https_status']['status'] === 'good') {
            $score += 100 * $weights['https_status'];
        }
        
        // Schema markup score
        if ($results['schema_markup']['status'] === 'good') {
            $score += 100 * $weights['schema_markup'];
        } elseif ($results['schema_markup']['status'] === 'warning') {
            $score += 50 * $weights['schema_markup'];
        }
        
        // Robots.txt score
        if ($results['robots_txt']['status'] === 'good') {
            $score += 100 * $weights['robots_txt'];
        } elseif ($results['robots_txt']['status'] === 'warning') {
            $score += 50 * $weights['robots_txt'];
        }
        
        // Canonical tags score
        if ($results['canonical_tags']['status'] === 'good') {
            $score += 100 * $weights['canonical_tags'];
        } elseif ($results['canonical_tags']['status'] === 'warning') {
            $score += 50 * $weights['canonical_tags'];
        }
        
        // Sitemap score
        if ($results['sitemap']['status'] === 'good') {
            $score += 100 * $weights['sitemap'];
        } elseif ($results['sitemap']['status'] === 'warning') {
            $score += 50 * $weights['sitemap'];
        }
        
        return round($score);
    }
}
