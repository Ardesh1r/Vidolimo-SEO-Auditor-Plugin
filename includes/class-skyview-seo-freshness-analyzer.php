<?php
/**
 * Content freshness analyzer class
 *
 * @package SkyView_SEO
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * SkyView SEO Freshness Analyzer class
 */
class SkyView_SEO_Freshness_Analyzer {

    /**
     * Constructor
     */
    public function __construct() {
        // Nothing to do here
    }

    /**
     * Analyze content freshness
     *
     * @param WP_Post $post Post object
     * @return array Analysis results
     */
    public function analyze($post) {
        // Initialize results
        $results = array(
            'last_updated' => array(
                'value' => $this->get_last_updated_date($post),
                'status' => 'good',
                'message' => '',
            ),
            'content_age' => array(
                'value' => $this->calculate_content_age($post),
                'status' => 'good',
                'message' => '',
            ),
            'staleness_warning' => array(
                'value' => false,
                'status' => 'good',
                'message' => '',
            ),
            'issues' => array(),
            'score' => 0,
        );

        // Analyze last updated date
        $results = $this->analyze_last_updated($results);

        // Analyze content age
        $results = $this->analyze_content_age($results);

        // Check for content staleness
        $results = $this->check_content_staleness($post, $results);

        // Calculate overall score
        $results['score'] = $this->calculate_score($results);

        return $results;
    }

    /**
     * Get last updated date
     *
     * @param WP_Post $post Post object
     * @return string Last updated date
     */
    private function get_last_updated_date($post) {
        // Check if post was modified
        if ($post->post_modified !== $post->post_date) {
            return $post->post_modified;
        }
        
        return $post->post_date;
    }

    /**
     * Calculate content age in days
     *
     * @param WP_Post $post Post object
     * @return int Content age in days
     */
    private function calculate_content_age($post) {
        $last_updated = strtotime($this->get_last_updated_date($post));
        $now = current_time('timestamp');
        
        return floor(($now - $last_updated) / DAY_IN_SECONDS);
    }

    /**
     * Analyze last updated date
     *
     * @param array $results Analysis results
     * @return array Updated results
     */
    private function analyze_last_updated($results) {
        $last_updated = $results['last_updated']['value'];
        
        // Format the date for display
        $formatted_date = date_i18n(get_option('date_format'), strtotime($last_updated));
        $results['last_updated']['formatted'] = $formatted_date;
        
        return $results;
    }

    /**
     * Analyze content age
     *
     * @param array $results Analysis results
     * @return array Updated results
     */
    private function analyze_content_age($results) {
        $age_in_days = $results['content_age']['value'];
        
        // Set status based on age
        if ($age_in_days < 30) {
            $results['content_age']['status'] = 'good';
            $results['content_age']['message'] = __('Content is very fresh (less than 30 days old)', 'vidolimo-seo-auditor');
        } elseif ($age_in_days < 90) {
            $results['content_age']['status'] = 'good';
            $results['content_age']['message'] = __('Content is fresh (less than 90 days old)', 'vidolimo-seo-auditor');
        } elseif ($age_in_days < 180) {
            $results['content_age']['status'] = 'warning';
            $results['content_age']['message'] = __('Content is starting to age (3-6 months old)', 'vidolimo-seo-auditor');
            $results['issues'][] = array(
                'type' => 'aging_content',
                'severity' => 'info',
                'message' => $results['content_age']['message'],
            );
        } elseif ($age_in_days < 365) {
            $results['content_age']['status'] = 'warning';
            $results['content_age']['message'] = __('Content is aging (6-12 months old)', 'vidolimo-seo-auditor');
            $results['issues'][] = array(
                'type' => 'aging_content',
                'severity' => 'warning',
                'message' => $results['content_age']['message'],
            );
        } else {
            $results['content_age']['status'] = 'bad';
            $results['content_age']['message'] = __('Content is old (more than 1 year old)', 'vidolimo-seo-auditor');
            $results['issues'][] = array(
                'type' => 'old_content',
                'severity' => 'warning',
                'message' => $results['content_age']['message'],
            );
        }
        
        return $results;
    }

    /**
     * Check for content staleness
     *
     * @param WP_Post $post    Post object
     * @param array   $results Analysis results
     * @return array Updated results
     */
    private function check_content_staleness($post, $results) {
        $age_in_days = $results['content_age']['value'];
        $content_type = $this->determine_content_type($post);
        $is_stale = false;
        
        // Different staleness thresholds based on content type
        switch ($content_type) {
            case 'news':
                $is_stale = $age_in_days > 7;
                break;
            case 'blog':
                $is_stale = $age_in_days > 180;
                break;
            case 'evergreen':
                $is_stale = $age_in_days > 365;
                break;
            default:
                $is_stale = $age_in_days > 180;
        }
        
        $results['staleness_warning']['value'] = $is_stale;
        
        if ($is_stale) {
            $results['staleness_warning']['status'] = 'warning';
            $results['staleness_warning']['message'] = sprintf(
                /* translators: %s: content type label */
                __('Content may be stale for its type (%s)', 'vidolimo-seo-auditor'),
                $content_type
            );
            $results['issues'][] = array(
                'type' => 'stale_content',
                'severity' => 'warning',
                'message' => $results['staleness_warning']['message'],
            );
        } else {
            $results['staleness_warning']['status'] = 'good';
            $results['staleness_warning']['message'] = sprintf(
                /* translators: %s: content type label */
                __('Content is still fresh for its type (%s)', 'vidolimo-seo-auditor'),
                $content_type
            );
        }
        
        return $results;
    }

    /**
     * Determine content type
     *
     * @param WP_Post $post Post object
     * @return string Content type (news, blog, evergreen)
     */
    private function determine_content_type($post) {
        // Check categories
        $categories = wp_get_post_categories($post->ID, array('fields' => 'names'));
        
        // Check for news-related categories
        $news_keywords = array('news', 'updates', 'announcements', 'press');
        foreach ($categories as $category) {
            foreach ($news_keywords as $keyword) {
                if (stripos($category, $keyword) !== false) {
                    return 'news';
                }
            }
        }
        
        // Check for evergreen content indicators
        $evergreen_indicators = array(
            'guide', 'tutorial', 'how to', 'tips', 'best practices',
            'ultimate guide', 'complete guide', 'definitive'
        );
        
        foreach ($evergreen_indicators as $indicator) {
            if (stripos($post->post_title, $indicator) !== false || 
                stripos($post->post_content, $indicator) !== false) {
                return 'evergreen';
            }
        }
        
        // Default to blog
        return 'blog';
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
            'content_age' => 0.7,
            'staleness_warning' => 0.3,
        );
        
        // Calculate score
        $score = 0;
        
        // Content age score
        if ($results['content_age']['status'] === 'good') {
            $score += 100 * $weights['content_age'];
        } elseif ($results['content_age']['status'] === 'warning') {
            $score += 60 * $weights['content_age'];
        } else {
            $score += 30 * $weights['content_age'];
        }
        
        // Staleness warning score
        if ($results['staleness_warning']['status'] === 'good') {
            $score += 100 * $weights['staleness_warning'];
        } else {
            $score += 0 * $weights['staleness_warning'];
        }
        
        return round($score);
    }
}
