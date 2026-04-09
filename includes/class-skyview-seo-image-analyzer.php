<?php
/**
 * Image analyzer class
 *
 * @package SkyView_SEO
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * SkyView SEO Image Analyzer class
 */
class SkyView_SEO_Image_Analyzer {

    /**
     * Constructor
     */
    public function __construct() {
        // Nothing to do here
    }

    /**
     * Analyze post images
     *
     * @param WP_Post $post Post object
     * @return array Analysis results
     */
    public function analyze($post) {
        // Initialize results
        $results = array(
            'total_images' => 0,
            'images' => array(),
            'missing_alt' => array(
                'count' => 0,
                'images' => array(),
                'status' => 'good',
                'message' => '',
            ),
            'large_images' => array(
                'count' => 0,
                'images' => array(),
                'status' => 'good',
                'message' => '',
            ),
            'lazy_loading' => array(
                'count' => 0,
                'images' => array(),
                'status' => 'good',
                'message' => '',
            ),
            'filename_optimization' => array(
                'count' => 0,
                'images' => array(),
                'status' => 'good',
                'message' => '',
            ),
            'issues' => array(),
            'score' => 0,
            'processed_images' => array(), // Track processed images to prevent duplicates
        );

        // Extract images from content
        $images = $this->extract_images($post->post_content);
        
        // Remove duplicate images by URL
        $unique_images = array();
        $image_urls = array();
        
        foreach ($images as $image) {
            if (!empty($image['src']) && !in_array($image['src'], $image_urls)) {
                $image_urls[] = $image['src'];
                $unique_images[] = $image;
            }
        }
        
        $results['total_images'] = count($unique_images);
        $results['images'] = $unique_images;

        // Check for missing alt tags
        $results = $this->check_missing_alt_tags($unique_images, $results);

        // Check for large images
        $results = $this->check_large_images($unique_images, $results);

        // Check for lazy loading
        $results = $this->check_lazy_loading($unique_images, $results);

        // Check for optimized filenames
        $results = $this->check_filename_optimization($unique_images, $results);

        // Calculate overall score
        $results['score'] = $this->calculate_score($results);

        return $results;
    }

    /**
     * Extract images from content
     *
     * @param string $content Post content
     * @return array Images found in content
     */
    private function extract_images($content) {
        $images = array();

        // Extract all img tags
        preg_match_all('/<img[^>]+>/i', $content, $img_tags);

        if (!empty($img_tags[0])) {
            foreach ($img_tags[0] as $img_tag) {
                // Extract src
                preg_match('/src=[\'"]([^\'"]+)[\'"]/i', $img_tag, $src);
                $src = isset($src[1]) ? $src[1] : '';

                // Extract alt
                preg_match('/alt=[\'"]([^\'"]*)[\'"]|alt=([^\s>]*)/i', $img_tag, $alt);
                $alt = isset($alt[1]) ? $alt[1] : (isset($alt[2]) ? $alt[2] : '');

                // Extract width and height
                preg_match('/width=[\'"]([^\'"]+)[\'"]/i', $img_tag, $width);
                $width = isset($width[1]) ? intval($width[1]) : 0;

                preg_match('/height=[\'"]([^\'"]+)[\'"]/i', $img_tag, $height);
                $height = isset($height[1]) ? intval($height[1]) : 0;

                // Extract loading attribute
                preg_match('/loading=[\'"]([^\'"]+)[\'"]/i', $img_tag, $loading);
                $loading = isset($loading[1]) ? $loading[1] : '';

                // Skip if no src
                if (empty($src)) {
                    continue;
                }

                // Get image size if it's a local image
                $file_size = 0;
                $is_local = false;

                if (strpos($src, get_site_url()) === 0) {
                    $is_local = true;
                    $file_path = str_replace(get_site_url(), ABSPATH, $src);
                    
                    if (file_exists($file_path)) {
                        $file_size = filesize($file_path);
                    }
                }

                // Add image to array
                $images[] = array(
                    'src' => $src,
                    'alt' => $alt,
                    'width' => $width,
                    'height' => $height,
                    'loading' => $loading,
                    'file_size' => $file_size,
                    'is_local' => $is_local,
                    'filename' => basename($src),
                );
            }
        }

        return $images;
    }

    /**
     * Check for missing alt tags
     *
     * @param array $images  All images
     * @param array $results Analysis results
     * @return array Updated results
     */
    private function check_missing_alt_tags($images, $results) {
        $missing_alt = array();

        foreach ($images as $image) {
            if (empty($image['alt'])) {
                $missing_alt[] = $image;
            }
        }

        $count = count($missing_alt);
        $results['missing_alt']['count'] = $count;
        $results['missing_alt']['images'] = $missing_alt;

        // Check missing alt count
        if ($count > 0) {
            $percentage = ($count / $results['total_images']) * 100;
            
            if ($percentage > 50) {
                $results['missing_alt']['status'] = 'bad';
                $results['missing_alt']['message'] = sprintf(
                    /* translators: 1: number of images missing alt text, 2: percentage of total images */
                    __('%1$d images (%2$d%%) are missing alt tags', 'vidolimo-seo-auditor'),
                    $count,
                    round($percentage)
                );
                $results['issues'][] = array(
                    'type' => 'missing_alt_tags',
                    'severity' => 'critical',
                    'message' => $results['missing_alt']['message'],
                );
            } else {
                $results['missing_alt']['status'] = 'warning';
                $results['missing_alt']['message'] = sprintf(
                    /* translators: 1: number of images missing alt text, 2: percentage of total images */
                    __('%1$d images (%2$d%%) are missing alt tags', 'vidolimo-seo-auditor'),
                    $count,
                    round($percentage)
                );
                $results['issues'][] = array(
                    'type' => 'missing_alt_tags',
                    'severity' => 'warning',
                    'message' => $results['missing_alt']['message'],
                );
            }
        } else {
            $results['missing_alt']['status'] = 'good';
            $results['missing_alt']['message'] = __('All images have alt tags', 'vidolimo-seo-auditor');
        }

        return $results;
    }

    /**
     * Check for large images
     *
     * @param array $images  All images
     * @param array $results Analysis results
     * @return array Updated results
     */
    private function check_large_images($images, $results) {
        $large_images = array();
        $size_threshold = 500 * 1024; // 500KB

        foreach ($images as $image) {
            if ($image['is_local'] && $image['file_size'] > $size_threshold) {
                $large_images[] = $image;
            }
        }

        $count = count($large_images);
        $results['large_images']['count'] = $count;
        $results['large_images']['images'] = $large_images;

        // Check large image count
        if ($count > 0) {
            $percentage = ($count / $results['total_images']) * 100;
            
            if ($percentage > 50) {
                $results['large_images']['status'] = 'bad';
                $results['large_images']['message'] = sprintf(
                    /* translators: 1: number of large images, 2: percentage of total images */
                    __('%1$d images (%2$d%%) are larger than 500KB', 'vidolimo-seo-auditor'),
                    $count,
                    round($percentage)
                );
                $results['issues'][] = array(
                    'type' => 'large_images',
                    'severity' => 'warning',
                    'message' => $results['large_images']['message'],
                );
            } else {
                $results['large_images']['status'] = 'warning';
                $results['large_images']['message'] = sprintf(
                    /* translators: 1: number of large images, 2: percentage of total images */
                    __('%1$d images (%2$d%%) are larger than 500KB', 'vidolimo-seo-auditor'),
                    $count,
                    round($percentage)
                );
                $results['issues'][] = array(
                    'type' => 'large_images',
                    'severity' => 'info',
                    'message' => $results['large_images']['message'],
                );
            }
        } else {
            $results['large_images']['status'] = 'good';
            $results['large_images']['message'] = __('All images are optimized for size', 'vidolimo-seo-auditor');
        }

        return $results;
    }

    /**
     * Check for lazy loading
     *
     * @param array $images  All images
     * @param array $results Analysis results
     * @return array Updated results
     */
    private function check_lazy_loading($images, $results) {
        $non_lazy_images = array();

        foreach ($images as $image) {
            if ($image['loading'] !== 'lazy') {
                $non_lazy_images[] = $image;
            }
        }

        $count = count($non_lazy_images);
        $results['lazy_loading']['count'] = $count;
        $results['lazy_loading']['images'] = $non_lazy_images;

        // Check non-lazy image count
        if ($count > 0) {
            $percentage = ($count / $results['total_images']) * 100;
            
            if ($percentage > 50) {
                $results['lazy_loading']['status'] = 'bad';
                $results['lazy_loading']['message'] = sprintf(
                    /* translators: 1: number of images without lazy loading, 2: percentage of total images */
                    __('%1$d images (%2$d%%) are not using lazy loading', 'vidolimo-seo-auditor'),
                    $count,
                    round($percentage)
                );
                $results['issues'][] = array(
                    'type' => 'non_lazy_images',
                    'severity' => 'warning',
                    'message' => $results['lazy_loading']['message'],
                );
            } else {
                $results['lazy_loading']['status'] = 'warning';
                $results['lazy_loading']['message'] = sprintf(
                    /* translators: 1: number of images without lazy loading, 2: percentage of total images */
                    __('%1$d images (%2$d%%) are not using lazy loading', 'vidolimo-seo-auditor'),
                    $count,
                    round($percentage)
                );
                $results['issues'][] = array(
                    'type' => 'non_lazy_images',
                    'severity' => 'info',
                    'message' => $results['lazy_loading']['message'],
                );
            }
        } else {
            $results['lazy_loading']['status'] = 'good';
            $results['lazy_loading']['message'] = __('All images are using lazy loading', 'vidolimo-seo-auditor');
        }

        return $results;
    }

    /**
     * Check for optimized filenames
     *
     * @param array $images  All images
     * @param array $results Analysis results
     * @return array Updated results
     */
    private function check_filename_optimization($images, $results) {
        $non_optimized_images = array();

        foreach ($images as $image) {
            $filename = $image['filename'];
            
            // Check if filename contains keywords (lowercase, no special chars except hyphens)
            if (preg_match('/^[0-9]+$/', $filename) || // Only numbers
                preg_match('/^IMG_[0-9]+/', $filename) || // Camera default
                preg_match('/^DSC[0-9]+/', $filename) || // Camera default
                preg_match('/^image[0-9]*\.(jpg|jpeg|png|gif)$/i', $filename) || // Generic name
                strpos($filename, ' ') !== false || // Contains spaces
                preg_match('/[^a-z0-9\-\.]/', $filename) // Contains special chars
            ) {
                $non_optimized_images[] = $image;
            }
        }

        $count = count($non_optimized_images);
        $results['filename_optimization']['count'] = $count;
        $results['filename_optimization']['images'] = $non_optimized_images;

        // Check non-optimized filename count
        if ($count > 0) {
            $percentage = ($count / $results['total_images']) * 100;
            
            if ($percentage > 50) {
                $results['filename_optimization']['status'] = 'warning';
                $results['filename_optimization']['message'] = sprintf(
                    /* translators: 1: number of images with non-optimized filenames, 2: percentage of total images */
                    __('%1$d images (%2$d%%) have non-optimized filenames', 'vidolimo-seo-auditor'),
                    $count,
                    round($percentage)
                );
                $results['issues'][] = array(
                    'type' => 'non_optimized_filenames',
                    'severity' => 'info',
                    'message' => $results['filename_optimization']['message'],
                );
            } else {
                $results['filename_optimization']['status'] = 'warning';
                $results['filename_optimization']['message'] = sprintf(
                    /* translators: 1: number of images with non-optimized filenames, 2: percentage of total images */
                    __('%1$d images (%2$d%%) have non-optimized filenames', 'vidolimo-seo-auditor'),
                    $count,
                    round($percentage)
                );
                $results['issues'][] = array(
                    'type' => 'non_optimized_filenames',
                    'severity' => 'info',
                    'message' => $results['filename_optimization']['message'],
                );
            }
        } else {
            $results['filename_optimization']['status'] = 'good';
            $results['filename_optimization']['message'] = __('All image filenames are optimized', 'vidolimo-seo-auditor');
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
        // If no images, return perfect score
        if ($results['total_images'] === 0) {
            return 100;
        }

        // Define weights
        $weights = array(
            'missing_alt' => 0.4,
            'large_images' => 0.3,
            'lazy_loading' => 0.2,
            'filename_optimization' => 0.1,
        );
        
        // Calculate score
        $score = 0;
        
        // Missing alt score
        if ($results['missing_alt']['status'] === 'good') {
            $score += 100 * $weights['missing_alt'];
        } elseif ($results['missing_alt']['status'] === 'warning') {
            $score += 50 * $weights['missing_alt'];
        }
        
        // Large images score
        if ($results['large_images']['status'] === 'good') {
            $score += 100 * $weights['large_images'];
        } elseif ($results['large_images']['status'] === 'warning') {
            $score += 50 * $weights['large_images'];
        }
        
        // Lazy loading score
        if ($results['lazy_loading']['status'] === 'good') {
            $score += 100 * $weights['lazy_loading'];
        } elseif ($results['lazy_loading']['status'] === 'warning') {
            $score += 50 * $weights['lazy_loading'];
        }
        
        // Filename optimization score
        if ($results['filename_optimization']['status'] === 'good') {
            $score += 100 * $weights['filename_optimization'];
        } elseif ($results['filename_optimization']['status'] === 'warning') {
            $score += 50 * $weights['filename_optimization'];
        }
        
        return round($score);
    }
}
