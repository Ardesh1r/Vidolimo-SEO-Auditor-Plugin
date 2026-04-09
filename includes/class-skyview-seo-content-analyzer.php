<?php
/**
 * Content analyzer class
 *
 * @package SkyView_SEO
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * SkyView SEO Content Analyzer class
 */
class SkyView_SEO_Content_Analyzer {

    /**
     * Constructor
     */
    public function __construct() {
        // Nothing to do here
    }

    /**
     * Analyze post content
     *
     * @param WP_Post $post Post object
     * @return array Analysis results
     */
    public function analyze($post) {
        // Initialize results
        $results = array(
            'title' => array(
                'value' => $post->post_title,
                'length' => mb_strlen($post->post_title),
                'status' => 'good',
                'message' => '',
            ),
            'meta_description' => array(
                'value' => $this->get_meta_description($post->ID),
                'length' => 0,
                'status' => 'good',
                'message' => '',
            ),
            'word_count' => array(
                'value' => $this->count_words($post->post_content),
                'status' => 'good',
                'message' => '',
            ),
            'reading_time' => array(
                'value' => 0,
                'status' => 'good',
                'message' => '',
            ),
            'content_quality' => array(
                'value' => 0,
                'status' => 'good',
                'message' => '',
            ),
            'issues' => array(),
            'score' => 0,
        );

        // Analyze title
        $results = $this->analyze_title($results);

        // Analyze meta description
        $results = $this->analyze_meta_description($results);

        // Analyze word count
        $results = $this->analyze_word_count($results);

        // Calculate reading time
        $results = $this->calculate_reading_time($results);

        // Analyze content quality
        $results = $this->analyze_content_quality($post, $results);

        // Calculate overall score
        $results['score'] = $this->calculate_score($results);

        return $results;
    }

    /**
     * Get meta description
     *
     * @param int $post_id Post ID
     * @return string Meta description
     */
    private function get_meta_description($post_id) {
        // Check if Yoast SEO is active
        if (defined('WPSEO_VERSION')) {
            $meta_description = get_post_meta($post_id, '_yoast_wpseo_metadesc', true);
            if (!empty($meta_description)) {
                return $meta_description;
            }
        }

        // Check if All in One SEO is active
        if (class_exists('AIOSEO')) {
            $meta_description = get_post_meta($post_id, '_aioseop_description', true);
            if (!empty($meta_description)) {
                return $meta_description;
            }
        }

        // Check if Rank Math is active
        if (class_exists('RankMath')) {
            $meta_description = get_post_meta($post_id, 'rank_math_description', true);
            if (!empty($meta_description)) {
                return $meta_description;
            }
        }

        // Default to excerpt
        $post = get_post($post_id);
        if (!empty($post->post_excerpt)) {
            return wp_strip_all_tags($post->post_excerpt);
        }

        // Generate from content
        $content = wp_strip_all_tags($post->post_content);
        $content = preg_replace('/\s+/', ' ', $content);
        return substr($content, 0, 160);
    }

    /**
     * Count words in content
     *
     * @param string $content Content to count words in
     * @return int Word count
     */
    private function count_words($content) {
        // Strip shortcodes
        $content = strip_shortcodes($content);
        
        // Strip HTML tags
        $content = wp_strip_all_tags($content);
        
        // Count words
        return str_word_count($content);
    }

    /**
     * Analyze title
     *
     * @param array $results Analysis results
     * @return array Updated results
     */
    private function analyze_title($results) {
        $title_length = $results['title']['length'];
        
        // Check title length
        if ($title_length < 30) {
            $results['title']['status'] = 'bad';
            $results['title']['message'] = __('Title is too short (less than 30 characters)', 'vidolimo-seo-auditor');
            $results['issues'][] = array(
                'type' => 'title_length',
                'severity' => 'warning',
                'message' => $results['title']['message'],
            );
        } elseif ($title_length < 50) {
            $results['title']['status'] = 'warning';
            $results['title']['message'] = __('Title is a bit short (less than 50 characters)', 'vidolimo-seo-auditor');
            $results['issues'][] = array(
                'type' => 'title_length',
                'severity' => 'info',
                'message' => $results['title']['message'],
            );
        } elseif ($title_length > 60) {
            $results['title']['status'] = 'warning';
            $results['title']['message'] = __('Title is too long (more than 60 characters)', 'vidolimo-seo-auditor');
            $results['issues'][] = array(
                'type' => 'title_length',
                'severity' => 'warning',
                'message' => $results['title']['message'],
            );
        } else {
            $results['title']['status'] = 'good';
            $results['title']['message'] = __('Title length is good', 'vidolimo-seo-auditor');
        }
        
        return $results;
    }

    /**
     * Analyze meta description
     *
     * @param array $results Analysis results
     * @return array Updated results
     */
    private function analyze_meta_description($results) {
        $meta_description = $results['meta_description']['value'];
        $meta_description_length = mb_strlen($meta_description);
        
        // Update length
        $results['meta_description']['length'] = $meta_description_length;
        
        // Check meta description length
        if (empty($meta_description)) {
            $results['meta_description']['status'] = 'bad';
            $results['meta_description']['message'] = __('Meta description is missing', 'vidolimo-seo-auditor');
            $results['issues'][] = array(
                'type' => 'meta_description_missing',
                'severity' => 'critical',
                'message' => $results['meta_description']['message'],
            );
        } elseif ($meta_description_length < 120) {
            $results['meta_description']['status'] = 'warning';
            $results['meta_description']['message'] = __('Meta description is too short (less than 120 characters)', 'vidolimo-seo-auditor');
            $results['issues'][] = array(
                'type' => 'meta_description_length',
                'severity' => 'warning',
                'message' => $results['meta_description']['message'],
            );
        } elseif ($meta_description_length > 160) {
            $results['meta_description']['status'] = 'warning';
            $results['meta_description']['message'] = __('Meta description is too long (more than 160 characters)', 'vidolimo-seo-auditor');
            $results['issues'][] = array(
                'type' => 'meta_description_length',
                'severity' => 'warning',
                'message' => $results['meta_description']['message'],
            );
        } else {
            $results['meta_description']['status'] = 'good';
            $results['meta_description']['message'] = __('Meta description length is good', 'vidolimo-seo-auditor');
        }
        
        return $results;
    }

    /**
     * Analyze word count
     *
     * @param array $results Analysis results
     * @return array Updated results
     */
    private function analyze_word_count($results) {
        $word_count = $results['word_count']['value'];
        
        // Check word count
        if ($word_count < 300) {
            $results['word_count']['status'] = 'bad';
            $results['word_count']['message'] = __('Content is too short (less than 300 words)', 'vidolimo-seo-auditor');
            $results['issues'][] = array(
                'type' => 'word_count',
                'severity' => 'warning',
                'message' => $results['word_count']['message'],
            );
        } elseif ($word_count < 600) {
            $results['word_count']['status'] = 'warning';
            $results['word_count']['message'] = __('Content is a bit short (less than 600 words)', 'vidolimo-seo-auditor');
            $results['issues'][] = array(
                'type' => 'word_count',
                'severity' => 'info',
                'message' => $results['word_count']['message'],
            );
        } else {
            $results['word_count']['status'] = 'good';
            $results['word_count']['message'] = __('Content length is good', 'vidolimo-seo-auditor');
        }
        
        return $results;
    }

    /**
     * Calculate reading time
     *
     * @param array $results Analysis results
     * @return array Updated results
     */
    private function calculate_reading_time($results) {
        $word_count = $results['word_count']['value'];
        
        // Calculate reading time (average reading speed is 225 words per minute)
        $reading_time = ceil($word_count / 225);
        
        // Update reading time
        $results['reading_time']['value'] = $reading_time;
        
        return $results;
    }

    /**
     * Analyze content quality
     *
     * @param WP_Post $post    Post object
     * @param array   $results Analysis results
     * @return array Updated results
     */
    private function analyze_content_quality($post, $results) {
        // Get content
        $content = $post->post_content;
        
        // Initialize quality score
        $quality_score = 0;
        
        // Check for headings
        $has_headings = preg_match('/<h[1-6][^>]*>.*?<\/h[1-6]>/i', $content);
        if ($has_headings) {
            $quality_score += 20;
        } else {
            $results['issues'][] = array(
                'type' => 'no_headings',
                'severity' => 'warning',
                'message' => __('Content has no headings', 'vidolimo-seo-auditor'),
            );
        }
        
        // Check for images
        $has_images = preg_match('/<img[^>]+>/i', $content);
        if ($has_images) {
            $quality_score += 20;
        } else {
            $results['issues'][] = array(
                'type' => 'no_images',
                'severity' => 'info',
                'message' => __('Content has no images', 'vidolimo-seo-auditor'),
            );
        }
        
        // Check for lists
        $has_lists = preg_match('/<[ou]l[^>]*>.*?<\/[ou]l>/is', $content);
        if ($has_lists) {
            $quality_score += 15;
        }
        
        // Check for links
        $has_links = preg_match('/<a[^>]+href=[^>]+>/i', $content);
        if ($has_links) {
            $quality_score += 15;
        }
        
        // Check for paragraphs
        $paragraphs = preg_match_all('/<p[^>]*>.*?<\/p>/is', $content);
        if ($paragraphs > 5) {
            $quality_score += 15;
        }
        
        // Check for tables
        $has_tables = preg_match('/<table[^>]*>.*?<\/table>/is', $content);
        if ($has_tables) {
            $quality_score += 15;
        }
        
        // Set quality score
        $results['content_quality']['value'] = $quality_score;
        
        // Set status based on score
        if ($quality_score < 30) {
            $results['content_quality']['status'] = 'bad';
            $results['content_quality']['message'] = __('Content quality is poor', 'vidolimo-seo-auditor');
            $results['issues'][] = array(
                'type' => 'content_quality',
                'severity' => 'warning',
                'message' => $results['content_quality']['message'],
            );
        } elseif ($quality_score < 60) {
            $results['content_quality']['status'] = 'warning';
            $results['content_quality']['message'] = __('Content quality could be improved', 'vidolimo-seo-auditor');
            $results['issues'][] = array(
                'type' => 'content_quality',
                'severity' => 'info',
                'message' => $results['content_quality']['message'],
            );
        } else {
            $results['content_quality']['status'] = 'good';
            $results['content_quality']['message'] = __('Content quality is good', 'vidolimo-seo-auditor');
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
            'title' => 0.2,
            'meta_description' => 0.2,
            'word_count' => 0.3,
            'content_quality' => 0.3,
        );
        
        // Calculate score
        $score = 0;
        
        // Title score
        if ($results['title']['status'] === 'good') {
            $score += 100 * $weights['title'];
        } elseif ($results['title']['status'] === 'warning') {
            $score += 50 * $weights['title'];
        }
        
        // Meta description score
        if ($results['meta_description']['status'] === 'good') {
            $score += 100 * $weights['meta_description'];
        } elseif ($results['meta_description']['status'] === 'warning') {
            $score += 50 * $weights['meta_description'];
        }
        
        // Word count score
        if ($results['word_count']['status'] === 'good') {
            $score += 100 * $weights['word_count'];
        } elseif ($results['word_count']['status'] === 'warning') {
            $score += 50 * $weights['word_count'];
        }
        
        // Content quality score
        $score += $results['content_quality']['value'] * $weights['content_quality'];
        
        return round($score);
    }
}
