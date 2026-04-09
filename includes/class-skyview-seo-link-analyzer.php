<?php
/**
 * Link analyzer class
 *
 * @package SkyView_SEO
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * SkyView SEO Link Analyzer class
 */
class SkyView_SEO_Link_Analyzer {

    /**
     * Constructor
     */
    public function __construct() {
        // Nothing to do here
    }

    /**
     * Analyze post links
     *
     * @param WP_Post $post Post object
     * @return array Analysis results
     */
    public function analyze($post) {
        // Initialize results
        $results = array(
            'internal_links' => array(
                'count' => 0,
                'urls' => array(),
                'status' => 'good',
                'message' => '',
            ),
            'external_links' => array(
                'count' => 0,
                'urls' => array(),
                'status' => 'good',
                'message' => '',
            ),
            'broken_links' => array(
                'count' => 0,
                'urls' => array(),
                'status' => 'good',
                'message' => '',
            ),
            'orphan_pages' => array(
                'status' => 'good',
                'message' => '',
            ),
            'issues' => array(),
            'score' => 0,
        );

        // Extract links from content
        $links = $this->extract_links($post->post_content);

        // Analyze internal links
        $results = $this->analyze_internal_links($links, $results);

        // Analyze external links
        $results = $this->analyze_external_links($links, $results);

        // Check for broken links
        $results = $this->check_broken_links($links, $results);

        // Check for orphan pages
        $results = $this->check_orphan_pages($post, $results);

        // Calculate overall score
        $results['score'] = $this->calculate_score($results);

        return $results;
    }

    /**
     * Extract links from content
     *
     * @param string $content Post content
     * @return array Links found in content
     */
    private function extract_links($content) {
        $links = array(
            'internal' => array(),
            'external' => array(),
        );

        // Extract all links
        preg_match_all('/<a[^>]+href=([\'"])(?<href>.+?)\1[^>]*>/i', $content, $matches);

        if (!empty($matches['href'])) {
            $site_url = get_site_url();
            $site_host = wp_parse_url($site_url, PHP_URL_HOST);

            foreach ($matches['href'] as $url) {
                // Skip anchor links and javascript
                if (strpos($url, '#') === 0 || strpos($url, 'javascript:') === 0) {
                    continue;
                }

                // Check if link is internal or external
                $host = wp_parse_url($url, PHP_URL_HOST);
                if (empty($host) || $host === $site_host) {
                    $links['internal'][] = $url;
                } else {
                    $links['external'][] = $url;
                }
            }
        }

        return $links;
    }

    /**
     * Analyze internal links
     *
     * @param array $links   All links
     * @param array $results Analysis results
     * @return array Updated results
     */
    private function analyze_internal_links($links, $results) {
        $internal_links = $links['internal'];
        $count = count($internal_links);

        // Update count and URLs
        $results['internal_links']['count'] = $count;
        $results['internal_links']['urls'] = $internal_links;

        // Check internal link count
        if ($count === 0) {
            $results['internal_links']['status'] = 'bad';
            $results['internal_links']['message'] = __('No internal links found', 'vidolimo-seo-auditor');
            $results['issues'][] = array(
                'type' => 'no_internal_links',
                'severity' => 'warning',
                'message' => $results['internal_links']['message'],
            );
        } elseif ($count < 3) {
            $results['internal_links']['status'] = 'warning';
            $results['internal_links']['message'] = __('Few internal links found', 'vidolimo-seo-auditor');
            $results['issues'][] = array(
                'type' => 'few_internal_links',
                'severity' => 'info',
                'message' => $results['internal_links']['message'],
            );
        } else {
            $results['internal_links']['status'] = 'good';
            $results['internal_links']['message'] = __('Good number of internal links', 'vidolimo-seo-auditor');
        }

        return $results;
    }

    /**
     * Analyze external links
     *
     * @param array $links   All links
     * @param array $results Analysis results
     * @return array Updated results
     */
    private function analyze_external_links($links, $results) {
        $external_links = $links['external'];
        $count = count($external_links);

        // Update count and URLs
        $results['external_links']['count'] = $count;
        $results['external_links']['urls'] = $external_links;

        // External links are optional but can be good for SEO
        if ($count === 0) {
            $results['external_links']['status'] = 'warning';
            $results['external_links']['message'] = __('No external links found', 'vidolimo-seo-auditor');
            $results['issues'][] = array(
                'type' => 'no_external_links',
                'severity' => 'info',
                'message' => $results['external_links']['message'],
            );
        } elseif ($count > 10) {
            $results['external_links']['status'] = 'warning';
            $results['external_links']['message'] = __('Too many external links found', 'vidolimo-seo-auditor');
            $results['issues'][] = array(
                'type' => 'too_many_external_links',
                'severity' => 'info',
                'message' => $results['external_links']['message'],
            );
        } else {
            $results['external_links']['status'] = 'good';
            $results['external_links']['message'] = __('Good number of external links', 'vidolimo-seo-auditor');
        }

        return $results;
    }

    /**
     * Check for broken links
     *
     * @param array $links   All links
     * @param array $results Analysis results
     * @return array Updated results
     */
    private function check_broken_links($links, $results) {
        // Initialize broken links
        $broken_links = array();

        // Check internal links
        foreach ($links['internal'] as $url) {
            // Only check internal links that point to actual pages
            if (strpos($url, get_site_url()) === 0) {
                $path = str_replace(get_site_url(), '', $url);
                
                // Skip if empty path or just a slash
                if (empty($path) || $path === '/') {
                    continue;
                }
                
                // Check if page exists
                $page = get_page_by_path($path);
                if (!$page) {
                    $broken_links[] = $url;
                }
            }
        }

        // We don't check external links here as it would require HTTP requests
        // This could be implemented with a background process or cron job

        // Update broken links
        $count = count($broken_links);
        $results['broken_links']['count'] = $count;
        $results['broken_links']['urls'] = $broken_links;

        // Check broken link count
        if ($count > 0) {
            $results['broken_links']['status'] = 'bad';
            $results['broken_links']['message'] = sprintf(
                /* translators: %d: number of broken links */
                _n(
                    '%d broken link found',
                    '%d broken links found',
                    $count,
                    'vidolimo-seo-auditor'
                ),
                $count
            );
            $results['issues'][] = array(
                'type' => 'broken_links',
                'severity' => 'critical',
                'message' => $results['broken_links']['message'],
            );
        } else {
            $results['broken_links']['status'] = 'good';
            $results['broken_links']['message'] = __('No broken links found', 'vidolimo-seo-auditor');
        }

        return $results;
    }

    /**
     * Check for orphan pages
     *
     * @param WP_Post $post    Post object
     * @param array   $results Analysis results
     * @return array Updated results
     */
    private function check_orphan_pages($post, $results) {
        // Orphan page detection disabled
        $results['orphan_pages']['status'] = 'good';
        $results['orphan_pages']['message'] = __('Orphan page detection disabled', 'vidolimo-seo-auditor');

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
            'internal_links' => 0.4,
            'external_links' => 0.2,
            'broken_links' => 0.3,
            'orphan_pages' => 0.1,
        );
        
        // Calculate score
        $score = 0;
        
        // Internal links score
        if ($results['internal_links']['status'] === 'good') {
            $score += 100 * $weights['internal_links'];
        } elseif ($results['internal_links']['status'] === 'warning') {
            $score += 50 * $weights['internal_links'];
        }
        
        // External links score
        if ($results['external_links']['status'] === 'good') {
            $score += 100 * $weights['external_links'];
        } elseif ($results['external_links']['status'] === 'warning') {
            $score += 50 * $weights['external_links'];
        }
        
        // Broken links score
        if ($results['broken_links']['status'] === 'good') {
            $score += 100 * $weights['broken_links'];
        } elseif ($results['broken_links']['status'] === 'warning') {
            $score += 50 * $weights['broken_links'];
        }
        
        // Orphan pages score
        if ($results['orphan_pages']['status'] === 'good') {
            $score += 100 * $weights['orphan_pages'];
        } elseif ($results['orphan_pages']['status'] === 'warning') {
            $score += 50 * $weights['orphan_pages'];
        }
        
        return round($score);
    }
}
