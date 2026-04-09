<?php
/**
 * Main analyzer class
 *
 * @package SkyView_SEO
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * SkyView SEO Analyzer class
 */
class SkyView_SEO_Analyzer {

    /**
     * Content analyzer instance
     *
     * @var SkyView_SEO_Content_Analyzer
     */
    private $content_analyzer;

    /**
     * Link analyzer instance
     *
     * @var SkyView_SEO_Link_Analyzer
     */
    private $link_analyzer;

    /**
     * Image analyzer instance
     *
     * @var SkyView_SEO_Image_Analyzer
     */
    private $image_analyzer;

    /**
     * Technical analyzer instance
     *
     * @var SkyView_SEO_Technical_Analyzer
     */
    private $technical_analyzer;

    /**
     * Performance analyzer instance
     *
     * @var SkyView_SEO_Performance_Analyzer
     */
    private $performance_analyzer;

    /**
     * Freshness analyzer instance
     *
     * @var SkyView_SEO_Freshness_Analyzer
     */
    private $freshness_analyzer;

    /**
     * Constructor
     */
    public function __construct() {
        // Initialize analyzers
        $this->content_analyzer = new SkyView_SEO_Content_Analyzer();
        $this->link_analyzer = new SkyView_SEO_Link_Analyzer();
        $this->image_analyzer = new SkyView_SEO_Image_Analyzer();
        $this->technical_analyzer = new SkyView_SEO_Technical_Analyzer();
        $this->performance_analyzer = new SkyView_SEO_Performance_Analyzer();
        $this->freshness_analyzer = new SkyView_SEO_Freshness_Analyzer();
    }

    /**
     * Initialize the analyzer
     */
    public function init() {
        // Add actions and filters
        add_action('save_post', array($this, 'analyze_post_on_save'), 10, 2);
    }

    /**
     * Analyze post on save
     *
     * @param int     $post_id Post ID
     * @param WP_Post $post    Post object
     */
    public function analyze_post_on_save($post_id, $post) {
        // Don't analyze revisions or auto-saves
        if (wp_is_post_revision($post_id) || wp_is_post_autosave($post_id)) {
            return;
        }

        // Don't analyze non-public post types
        if (!in_array($post->post_type, get_post_types(array('public' => true)), true)) {
            return;
        }

        // Analyze the post
        $this->analyze_post($post_id);
    }

    /**
     * Analyze a post
     *
     * @param int $post_id Post ID
     * @return array Analysis results
     */
    public function analyze_post($post_id) {
        // Get post
        $post = get_post($post_id);
        if (!$post) {
            return array('error' => __('Post not found', 'vidolimo-seo-auditor'));
        }

        // Get post URL
        $post_url = get_permalink($post_id);

        // Run all analyses
        $content_analysis = $this->content_analyzer->analyze($post);
        $link_analysis = $this->link_analyzer->analyze($post);
        $image_analysis = $this->image_analyzer->analyze($post);
        $technical_analysis = $this->technical_analyzer->analyze($post, $post_url);
        $performance_analysis = $this->performance_analyzer->analyze($post, $post_url);
        $freshness_analysis = $this->freshness_analyzer->analyze($post);

        // Aggregate issues across analyzers for site-wide reporting
        $issues = array();
        $category_map = array(
            'content' => $content_analysis,
            'links' => $link_analysis,
            'images' => $image_analysis,
            'technical' => $technical_analysis,
            'performance' => $performance_analysis,
            'freshness' => $freshness_analysis,
        );

        foreach ($category_map as $category => $category_analysis) {
            if (!empty($category_analysis['issues']) && is_array($category_analysis['issues'])) {
                foreach ($category_analysis['issues'] as $issue) {
                    if (!is_array($issue)) {
                        continue;
                    }
                    $issue['category'] = $category;
                    $issues[] = $issue;
                }
            }
        }

        // Combine all analyses
        $analysis = array(
            'post_id' => $post_id,
            'post_title' => $post->post_title,
            'post_url' => $post_url,
            'content' => $content_analysis,
            'links' => $link_analysis,
            'images' => $image_analysis,
            'technical' => $technical_analysis,
            'performance' => $performance_analysis,
            'freshness' => $freshness_analysis,
            'issues' => $issues,
            'timestamp' => current_time('timestamp'),
        );

        // Calculate overall score
        $analysis['overall_score'] = $this->calculate_overall_score($analysis);

        // Store analysis in post meta
        update_post_meta($post_id, '_skyview_seo_analysis', $analysis);

        return $analysis;
    }

    /**
     * Get post analysis
     *
     * @param int $post_id Post ID
     * @return array Analysis results
     */
    public function get_post_analysis($post_id) {
        // Get analysis from post meta
        $analysis = get_post_meta($post_id, '_skyview_seo_analysis', true);

        // If no analysis exists, or stored analysis is missing required keys, run it now
        if (empty($analysis) || !is_array($analysis) || !isset($analysis['overall_score']) || !isset($analysis['issues'])) {
            $analysis = $this->analyze_post($post_id);
        }

        return $analysis;
    }

    /**
     * Calculate overall score
     *
     * @param array $analysis Analysis data
     * @return int Overall score (0-100)
     */
    private function calculate_overall_score($analysis) {
        // Define weights for each category
        $weights = array(
            'content' => 0.25,
            'links' => 0.15,
            'images' => 0.15,
            'technical' => 0.20,
            'performance' => 0.15,
            'freshness' => 0.10,
        );

        // Calculate weighted score
        $score = 0;
        foreach ($weights as $category => $weight) {
            if (isset($analysis[$category]['score'])) {
                $score += $analysis[$category]['score'] * $weight;
            }
        }

        // Round to nearest integer
        return round($score);
    }

    /**
     * Get site analysis
     *
     * @return array Site analysis data
     */
    public function get_site_analysis() {
        // Get recent posts
        $posts = get_posts(array(
            'post_type' => get_post_types(array('public' => true)),
            'posts_per_page' => 100,
            'post_status' => 'publish',
        ));

        // Initialize data
        $site_data = array(
            'total_posts' => count($posts),
            'analyzed_posts' => 0,
            'average_score' => 0,
            'content_score' => 0,
            'links_score' => 0,
            'images_score' => 0,
            'technical_score' => 0,
            'performance_score' => 0,
            'freshness_score' => 0,
            'issues' => array(
                'critical' => 0,
                'warning' => 0,
                'info' => 0,
            ),
            'top_issues' => array(),
        );

        // Analyze each post
        $total_score = 0;
        $category_scores = array(
            'content' => 0,
            'links' => 0,
            'images' => 0,
            'technical' => 0,
            'performance' => 0,
            'freshness' => 0,
        );

        $issues = array();

        foreach ($posts as $post) {
            // Get analysis
            $analysis = $this->get_post_analysis($post->ID);
            
            // Skip if no analysis
            if (empty($analysis) || isset($analysis['error'])) {
                continue;
            }

            // Count analyzed posts
            $site_data['analyzed_posts']++;

            // Add to total score
            $total_score += $analysis['overall_score'];

            // Add to category scores
            foreach ($category_scores as $category => $score) {
                if (isset($analysis[$category]['score'])) {
                    $category_scores[$category] += $analysis[$category]['score'];
                }
            }

            // Count issues
            if (isset($analysis['issues'])) {
                foreach ($analysis['issues'] as $issue) {
                    $site_data['issues'][$issue['severity']]++;
                    
                    // Add to issues array for top issues
                    $issues[] = array(
                        'post_id' => $post->ID,
                        'post_title' => $post->post_title,
                        'issue' => $issue['message'],
                        'severity' => $issue['severity'],
                    );
                }
            }
        }

        // Calculate averages
        if ($site_data['analyzed_posts'] > 0) {
            $site_data['average_score'] = round($total_score / $site_data['analyzed_posts']);
            
            foreach ($category_scores as $category => $score) {
                $site_data[$category . '_score'] = round($score / $site_data['analyzed_posts']);
            }
        }

        // Get top issues
        usort($issues, function($a, $b) {
            $severity_order = array('critical' => 0, 'warning' => 1, 'info' => 2);
            return $severity_order[$a['severity']] - $severity_order[$b['severity']];
        });
        
        $site_data['top_issues'] = array_slice($issues, 0, 10);

        return $site_data;
    }
}
