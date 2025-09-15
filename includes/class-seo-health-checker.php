<?php
/**
 * SEO Health Checker Class
 * 
 * Handles SEO health checking functionality including meta tags, alt text, and broken internal links
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class AdminX_SEO_Health_Checker {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('init', array($this, 'init'));
        add_action('adminx_seo_health_check', array($this, 'run_scheduled_check'));
        add_action('wp_ajax_adminx_run_seo_check', array($this, 'ajax_run_seo_check'));
        add_action('save_post', array($this, 'check_post_on_save'), 10, 2);
    }
    
    /**
     * Initialize
     */
    public function init() {
        // Hook into post save to check SEO
        add_action('post_updated', array($this, 'check_post_seo'), 10, 3);
    }
    
    /**
     * Run SEO check on post save
     */
    public function check_post_on_save($post_id, $post) {
        if (wp_is_post_revision($post_id) || wp_is_post_autosave($post_id)) {
            return;
        }
        
        if ($post->post_status === 'publish') {
            $this->check_single_post($post_id);
        }
    }
    
    /**
     * AJAX handler for running SEO check
     */
    public function ajax_run_seo_check() {
        check_ajax_referer('adminx_seo_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die(__('Insufficient permissions', 'adminx-seo-content'));
        }
        
        $post_id = intval($_POST['post_id']);
        $results = $this->check_single_post($post_id);
        
        wp_send_json_success($results);
    }
    
    /**
     * Run scheduled SEO health check
     */
    public function run_scheduled_check() {
        $posts = get_posts(array(
            'post_type' => array('post', 'page'),
            'post_status' => 'publish',
            'numberposts' => 50,
            'meta_query' => array(
                array(
                    'key' => '_adminx_last_seo_check',
                    'value' => date('Y-m-d', strtotime('-7 days')),
                    'compare' => '<',
                    'type' => 'DATE'
                )
            )
        ));
        
        foreach ($posts as $post) {
            $this->check_single_post($post->ID);
        }
    }
    
    /**
     * Check SEO for a single post
     */
    public function check_single_post($post_id) {
        $post = get_post($post_id);
        if (!$post) {
            return false;
        }
        
        $results = array();
        
        // Check meta title
        $results['meta_title'] = $this->check_meta_title($post);
        
        // Check meta description
        $results['meta_description'] = $this->check_meta_description($post);
        
        // Check headings structure
        $results['headings'] = $this->check_headings_structure($post);
        
        // Check images alt text
        $results['alt_text'] = $this->check_images_alt_text($post);
        
        // Check internal links
        $results['internal_links'] = $this->check_internal_links($post);
        
        // Check content length
        $results['content_length'] = $this->check_content_length($post);
        
        // Check keyword density
        $results['keyword_density'] = $this->check_keyword_density($post);
        
        // Save results to database
        $this->save_check_results($post_id, $results);
        
        // Update last check timestamp
        update_post_meta($post_id, '_adminx_last_seo_check', current_time('mysql'));
        
        return $results;
    }
    
    /**
     * Check meta title
     */
    private function check_meta_title($post) {
        $title = get_post_meta($post->ID, '_yoast_wpseo_title', true);
        if (empty($title)) {
            $title = $post->post_title;
        }
        
        $length = strlen($title);
        $status = 'good';
        $message = __('Meta title length is optimal', 'adminx-seo-content');
        
        if ($length < 30) {
            $status = 'warning';
            $message = __('Meta title is too short (less than 30 characters)', 'adminx-seo-content');
        } elseif ($length > 60) {
            $status = 'error';
            $message = __('Meta title is too long (more than 60 characters)', 'adminx-seo-content');
        }
        
        return array(
            'status' => $status,
            'message' => $message,
            'value' => $title,
            'length' => $length
        );
    }
    
    /**
     * Check meta description
     */
    private function check_meta_description($post) {
        $description = get_post_meta($post->ID, '_yoast_wpseo_metadesc', true);
        if (empty($description)) {
            $description = wp_trim_words($post->post_content, 25, '...');
        }
        
        $length = strlen($description);
        $status = 'good';
        $message = __('Meta description length is optimal', 'adminx-seo-content');
        
        if (empty($description)) {
            $status = 'error';
            $message = __('Meta description is missing', 'adminx-seo-content');
        } elseif ($length < 120) {
            $status = 'warning';
            $message = __('Meta description is too short (less than 120 characters)', 'adminx-seo-content');
        } elseif ($length > 160) {
            $status = 'error';
            $message = __('Meta description is too long (more than 160 characters)', 'adminx-seo-content');
        }
        
        return array(
            'status' => $status,
            'message' => $message,
            'value' => $description,
            'length' => $length
        );
    }
    
    /**
     * Check headings structure
     */
    private function check_headings_structure($post) {
        $content = $post->post_content;
        preg_match_all('/<h([1-6])[^>]*>(.*?)<\/h[1-6]>/i', $content, $matches);
        
        $headings = array();
        if (!empty($matches[1])) {
            foreach ($matches[1] as $index => $level) {
                $headings[] = array(
                    'level' => intval($level),
                    'text' => strip_tags($matches[2][$index])
                );
            }
        }
        
        $status = 'good';
        $message = __('Heading structure is good', 'adminx-seo-content');
        
        if (empty($headings)) {
            $status = 'warning';
            $message = __('No headings found in content', 'adminx-seo-content');
        } else {
            // Check if H1 exists
            $h1_count = count(array_filter($headings, function($h) { return $h['level'] === 1; }));
            if ($h1_count === 0) {
                $status = 'warning';
                $message = __('No H1 heading found', 'adminx-seo-content');
            } elseif ($h1_count > 1) {
                $status = 'warning';
                $message = __('Multiple H1 headings found', 'adminx-seo-content');
            }
        }
        
        return array(
            'status' => $status,
            'message' => $message,
            'headings' => $headings,
            'count' => count($headings)
        );
    }
    
    /**
     * Check images alt text
     */
    private function check_images_alt_text($post) {
        $content = $post->post_content;
        preg_match_all('/<img[^>]+>/i', $content, $matches);
        
        $images = $matches[0];
        $missing_alt = 0;
        $total_images = count($images);
        
        foreach ($images as $img) {
            if (!preg_match('/alt\s*=\s*["\'][^"\']*["\']/', $img)) {
                $missing_alt++;
            }
        }
        
        $status = 'good';
        $message = __('All images have alt text', 'adminx-seo-content');
        
        if ($total_images === 0) {
            $status = 'info';
            $message = __('No images found in content', 'adminx-seo-content');
        } elseif ($missing_alt > 0) {
            $status = 'error';
            $message = sprintf(__('%d out of %d images are missing alt text', 'adminx-seo-content'), $missing_alt, $total_images);
        }
        
        return array(
            'status' => $status,
            'message' => $message,
            'total_images' => $total_images,
            'missing_alt' => $missing_alt
        );
    }
    
    /**
     * Check internal links
     */
    private function check_internal_links($post) {
        $content = $post->post_content;
        $site_url = get_site_url();
        
        preg_match_all('/<a[^>]+href\s*=\s*["\']([^"\']+)["\'][^>]*>/i', $content, $matches);
        
        $internal_links = array();
        $broken_links = 0;
        
        if (!empty($matches[1])) {
            foreach ($matches[1] as $url) {
                if (strpos($url, $site_url) === 0 || strpos($url, '/') === 0) {
                    $internal_links[] = $url;
                    
                    // Check if link is broken (simplified check)
                    if (strpos($url, '/') === 0) {
                        $url = $site_url . $url;
                    }
                    
                    $response = wp_remote_head($url);
                    if (is_wp_error($response) || wp_remote_retrieve_response_code($response) >= 400) {
                        $broken_links++;
                    }
                }
            }
        }
        
        $status = 'good';
        $message = __('Internal links are working properly', 'adminx-seo-content');
        
        if (empty($internal_links)) {
            $status = 'warning';
            $message = __('No internal links found', 'adminx-seo-content');
        } elseif ($broken_links > 0) {
            $status = 'error';
            $message = sprintf(__('%d broken internal links found', 'adminx-seo-content'), $broken_links);
        }
        
        return array(
            'status' => $status,
            'message' => $message,
            'total_links' => count($internal_links),
            'broken_links' => $broken_links
        );
    }
    
    /**
     * Check content length
     */
    private function check_content_length($post) {
        $content = strip_tags($post->post_content);
        $word_count = str_word_count($content);
        
        $status = 'good';
        $message = __('Content length is optimal', 'adminx-seo-content');
        
        if ($word_count < 300) {
            $status = 'warning';
            $message = __('Content is too short (less than 300 words)', 'adminx-seo-content');
        } elseif ($word_count > 2000) {
            $status = 'info';
            $message = __('Content is very long (more than 2000 words)', 'adminx-seo-content');
        }
        
        return array(
            'status' => $status,
            'message' => $message,
            'word_count' => $word_count
        );
    }
    
    /**
     * Check keyword density
     */
    private function check_keyword_density($post) {
        $content = strtolower(strip_tags($post->post_content));
        $words = str_word_count($content, 1);
        $total_words = count($words);
        
        if ($total_words === 0) {
            return array(
                'status' => 'info',
                'message' => __('No content to analyze', 'adminx-seo-content'),
                'keywords' => array()
            );
        }
        
        $word_count = array_count_values($words);
        arsort($word_count);
        
        // Get top keywords (excluding common words)
        $common_words = array('the', 'and', 'or', 'but', 'in', 'on', 'at', 'to', 'for', 'of', 'with', 'by', 'is', 'are', 'was', 'were', 'be', 'been', 'have', 'has', 'had', 'do', 'does', 'did', 'will', 'would', 'could', 'should', 'may', 'might', 'must', 'can', 'this', 'that', 'these', 'those', 'a', 'an');
        
        $keywords = array();
        $count = 0;
        foreach ($word_count as $word => $frequency) {
            if (!in_array($word, $common_words) && strlen($word) > 3 && $count < 10) {
                $density = ($frequency / $total_words) * 100;
                $keywords[] = array(
                    'word' => $word,
                    'frequency' => $frequency,
                    'density' => round($density, 2)
                );
                $count++;
            }
        }
        
        return array(
            'status' => 'info',
            'message' => sprintf(__('Analyzed %d words', 'adminx-seo-content'), $total_words),
            'keywords' => $keywords,
            'total_words' => $total_words
        );
    }
    
    /**
     * Save check results to database
     */
    private function save_check_results($post_id, $results) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'adminx_seo_health';
        
        // Delete old results for this post
        $wpdb->delete($table_name, array('post_id' => $post_id));
        
        // Insert new results
        foreach ($results as $check_type => $result) {
            $wpdb->insert(
                $table_name,
                array(
                    'post_id' => $post_id,
                    'check_type' => $check_type,
                    'status' => $result['status'],
                    'message' => $result['message'],
                    'checked_at' => current_time('mysql')
                ),
                array('%d', '%s', '%s', '%s', '%s')
            );
        }
    }
    
    /**
     * Get SEO health results for a post
     */
    public function get_post_results($post_id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'adminx_seo_health';
        
        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table_name WHERE post_id = %d ORDER BY checked_at DESC",
            $post_id
        ));
        
        return $results;
    }
    
    /**
     * Get overall SEO health statistics
     */
    public function get_overall_stats() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'adminx_seo_health';
        
        $stats = $wpdb->get_results("
            SELECT 
                check_type,
                status,
                COUNT(*) as count
            FROM $table_name 
            WHERE checked_at > DATE_SUB(NOW(), INTERVAL 30 DAY)
            GROUP BY check_type, status
            ORDER BY check_type, status
        ");
        
        return $stats;
    }
}