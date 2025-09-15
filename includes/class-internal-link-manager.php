<?php
/**
 * Internal Link Manager Class
 * 
 * Handles automatic internal linking functionality
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class AdminX_Internal_Link_Manager {
    
    private $link_suggestions = array();
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('init', array($this, 'init'));
        add_action('wp_ajax_adminx_get_link_suggestions', array($this, 'ajax_get_link_suggestions'));
        add_action('wp_ajax_adminx_auto_link_content', array($this, 'ajax_auto_link_content'));
        add_filter('the_content', array($this, 'auto_add_internal_links'), 20);
    }
    
    /**
     * Initialize
     */
    public function init() {
        // Build link suggestions cache
        add_action('save_post', array($this, 'update_link_cache'), 10, 2);
        add_action('wp_loaded', array($this, 'build_link_cache'));
    }
    
    /**
     * Build internal link cache
     */
    public function build_link_cache() {
        $cache_key = 'adminx_internal_links_cache';
        $cached_links = get_transient($cache_key);
        
        if ($cached_links === false) {
            $this->link_suggestions = $this->generate_link_suggestions();
            set_transient($cache_key, $this->link_suggestions, 12 * HOUR_IN_SECONDS);
        } else {
            $this->link_suggestions = $cached_links;
        }
    }
    
    /**
     * Generate link suggestions
     */
    private function generate_link_suggestions() {
        $posts = get_posts(array(
            'post_type' => array('post', 'page'),
            'post_status' => 'publish',
            'numberposts' => 500,
            'meta_query' => array(
                array(
                    'key' => '_adminx_exclude_auto_linking',
                    'compare' => 'NOT EXISTS'
                )
            )
        ));
        
        $suggestions = array();
        
        foreach ($posts as $post) {
            $keywords = $this->extract_keywords($post);
            
            foreach ($keywords as $keyword) {
                if (strlen($keyword) > 3) {
                    $suggestions[$keyword] = array(
                        'post_id' => $post->ID,
                        'title' => $post->post_title,
                        'url' => get_permalink($post->ID),
                        'keyword' => $keyword,
                        'priority' => $this->calculate_keyword_priority($keyword, $post)
                    );
                }
            }
        }
        
        // Sort by priority
        uasort($suggestions, function($a, $b) {
            return $b['priority'] - $a['priority'];
        });
        
        return $suggestions;
    }
    
    /**
     * Extract keywords from post
     */
    private function extract_keywords($post) {
        $content = strtolower(strip_tags($post->post_content));
        $title = strtolower($post->post_title);
        
        // Get keywords from title
        $title_words = explode(' ', $title);
        
        // Get keywords from content
        $words = str_word_count($content, 1);
        $word_count = array_count_values($words);
        arsort($word_count);
        
        // Combine and prioritize
        $keywords = array();
        
        // Add title words with high priority
        foreach ($title_words as $word) {
            if (strlen($word) > 3) {
                $keywords[] = $word;
            }
        }
        
        // Add frequent content words
        $count = 0;
        foreach ($word_count as $word => $frequency) {
            if ($frequency > 2 && strlen($word) > 3 && $count < 10) {
                $keywords[] = $word;
                $count++;
            }
        }
        
        // Add multi-word phrases from title
        $title_phrases = $this->extract_phrases($title);
        $keywords = array_merge($keywords, $title_phrases);
        
        return array_unique($keywords);
    }
    
    /**
     * Extract phrases from text
     */
    private function extract_phrases($text) {
        $words = explode(' ', $text);
        $phrases = array();
        
        // 2-word phrases
        for ($i = 0; $i < count($words) - 1; $i++) {
            $phrase = $words[$i] . ' ' . $words[$i + 1];
            if (strlen($phrase) > 6) {
                $phrases[] = $phrase;
            }
        }
        
        // 3-word phrases
        for ($i = 0; $i < count($words) - 2; $i++) {
            $phrase = $words[$i] . ' ' . $words[$i + 1] . ' ' . $words[$i + 2];
            if (strlen($phrase) > 10) {
                $phrases[] = $phrase;
            }
        }
        
        return $phrases;
    }
    
    /**
     * Calculate keyword priority
     */
    private function calculate_keyword_priority($keyword, $post) {
        $priority = 0;
        
        // Higher priority for title words
        if (stripos($post->post_title, $keyword) !== false) {
            $priority += 10;
        }
        
        // Higher priority for longer keywords
        $priority += strlen($keyword);
        
        // Higher priority for newer posts
        $post_age = (time() - strtotime($post->post_date)) / DAY_IN_SECONDS;
        $priority += max(0, 30 - $post_age);
        
        return $priority;
    }
    
    /**
     * Auto add internal links to content
     */
    public function auto_add_internal_links($content) {
        if (!is_single() && !is_page()) {
            return $content;
        }
        
        global $post;
        
        // Check if auto-linking is disabled for this post
        if (get_post_meta($post->ID, '_adminx_disable_auto_linking', true)) {
            return $content;
        }
        
        // Get auto-linking settings
        $max_links = get_option('adminx_max_auto_links', 3);
        $same_post_linking = get_option('adminx_same_post_linking', false);
        
        $linked_count = 0;
        $already_linked = array();
        
        foreach ($this->link_suggestions as $keyword => $suggestion) {
            if ($linked_count >= $max_links) {
                break;
            }
            
            // Skip if linking to same post
            if (!$same_post_linking && $suggestion['post_id'] == $post->ID) {
                continue;
            }
            
            // Skip if already linked to this post
            if (in_array($suggestion['post_id'], $already_linked)) {
                continue;
            }
            
            // Check if keyword exists in content and not already linked
            if (stripos($content, $keyword) !== false && stripos($content, $suggestion['url']) === false) {
                $pattern = '/\b' . preg_quote($keyword, '/') . '\b/i';
                $replacement = '<a href="' . esc_url($suggestion['url']) . '" title="' . esc_attr($suggestion['title']) . '">' . $keyword . '</a>';
                
                $content = preg_replace($pattern, $replacement, $content, 1);
                $already_linked[] = $suggestion['post_id'];
                $linked_count++;
            }
        }
        
        return $content;
    }
    
    /**
     * Update link cache when post is saved
     */
    public function update_link_cache($post_id, $post) {
        if (wp_is_post_revision($post_id) || wp_is_post_autosave($post_id)) {
            return;
        }
        
        if ($post->post_status === 'publish') {
            // Clear cache to force rebuild
            delete_transient('adminx_internal_links_cache');
        }
    }
    
    /**
     * AJAX handler for getting link suggestions
     */
    public function ajax_get_link_suggestions() {
        check_ajax_referer('adminx_seo_nonce', 'nonce');
        
        if (!current_user_can('edit_posts')) {
            wp_die(__('Insufficient permissions', 'adminx-seo-content'));
        }
        
        $post_id = intval($_POST['post_id']);
        $content = sanitize_textarea_field($_POST['content']);
        
        $suggestions = $this->get_link_suggestions_for_content($content, $post_id);
        
        wp_send_json_success($suggestions);
    }
    
    /**
     * Get link suggestions for specific content
     */
    public function get_link_suggestions_for_content($content, $exclude_post_id = 0) {
        $suggestions = array();
        $content_lower = strtolower(strip_tags($content));
        
        foreach ($this->link_suggestions as $keyword => $suggestion) {
            if ($suggestion['post_id'] == $exclude_post_id) {
                continue;
            }
            
            if (stripos($content_lower, $keyword) !== false) {
                $suggestions[] = array(
                    'keyword' => $keyword,
                    'title' => $suggestion['title'],
                    'url' => $suggestion['url'],
                    'post_id' => $suggestion['post_id'],
                    'priority' => $suggestion['priority']
                );
            }
        }
        
        // Sort by priority
        usort($suggestions, function($a, $b) {
            return $b['priority'] - $a['priority'];
        });
        
        return array_slice($suggestions, 0, 10);
    }
    
    /**
     * AJAX handler for auto-linking content
     */
    public function ajax_auto_link_content() {
        check_ajax_referer('adminx_seo_nonce', 'nonce');
        
        if (!current_user_can('edit_posts')) {
            wp_die(__('Insufficient permissions', 'adminx-seo-content'));
        }
        
        $post_id = intval($_POST['post_id']);
        $content = wp_kses_post($_POST['content']);
        $max_links = intval($_POST['max_links']) ?: 3;
        
        $linked_content = $this->add_links_to_content($content, $post_id, $max_links);
        
        wp_send_json_success(array(
            'content' => $linked_content,
            'links_added' => $this->count_links_added($content, $linked_content)
        ));
    }
    
    /**
     * Add links to content
     */
    private function add_links_to_content($content, $exclude_post_id, $max_links) {
        $linked_count = 0;
        $already_linked = array();
        
        foreach ($this->link_suggestions as $keyword => $suggestion) {
            if ($linked_count >= $max_links) {
                break;
            }
            
            if ($suggestion['post_id'] == $exclude_post_id) {
                continue;
            }
            
            if (in_array($suggestion['post_id'], $already_linked)) {
                continue;
            }
            
            if (stripos($content, $keyword) !== false && stripos($content, $suggestion['url']) === false) {
                $pattern = '/\b' . preg_quote($keyword, '/') . '\b/i';
                $replacement = '<a href="' . esc_url($suggestion['url']) . '" title="' . esc_attr($suggestion['title']) . '">' . $keyword . '</a>';
                
                $content = preg_replace($pattern, $replacement, $content, 1);
                $already_linked[] = $suggestion['post_id'];
                $linked_count++;
            }
        }
        
        return $content;
    }
    
    /**
     * Count links added
     */
    private function count_links_added($original, $modified) {
        $original_links = substr_count($original, '<a ');
        $modified_links = substr_count($modified, '<a ');
        
        return $modified_links - $original_links;
    }
    
    /**
     * Get internal link statistics
     */
    public function get_link_statistics() {
        global $wpdb;
        
        $stats = array();
        
        // Get posts with most internal links
        $posts_with_links = $wpdb->get_results("
            SELECT p.ID, p.post_title, 
                   (LENGTH(p.post_content) - LENGTH(REPLACE(p.post_content, '<a ', ''))) as link_count
            FROM {$wpdb->posts} p
            WHERE p.post_status = 'publish'
            AND p.post_type IN ('post', 'page')
            ORDER BY link_count DESC
            LIMIT 10
        ");
        
        $stats['top_linked_posts'] = $posts_with_links;
        
        // Get posts with no internal links
        $posts_without_links = $wpdb->get_results("
            SELECT p.ID, p.post_title
            FROM {$wpdb->posts} p
            WHERE p.post_status = 'publish'
            AND p.post_type IN ('post', 'page')
            AND p.post_content NOT LIKE '%<a %'
            ORDER BY p.post_date DESC
            LIMIT 10
        ");
        
        $stats['posts_without_links'] = $posts_without_links;
        
        // Get total statistics
        $total_posts = $wpdb->get_var("
            SELECT COUNT(*)
            FROM {$wpdb->posts}
            WHERE post_status = 'publish'
            AND post_type IN ('post', 'page')
        ");
        
        $posts_with_internal_links = $wpdb->get_var("
            SELECT COUNT(*)
            FROM {$wpdb->posts}
            WHERE post_status = 'publish'
            AND post_type IN ('post', 'page')
            AND post_content LIKE '%<a %'
        ");
        
        $stats['total_posts'] = $total_posts;
        $stats['posts_with_links'] = $posts_with_internal_links;
        $stats['posts_without_links_count'] = $total_posts - $posts_with_internal_links;
        $stats['link_percentage'] = $total_posts > 0 ? round(($posts_with_internal_links / $total_posts) * 100, 2) : 0;
        
        return $stats;
    }
}