<?php
/**
 * Auto Tagger Class
 * 
 * Handles automatic tagging of posts by keywords
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class AdminX_Auto_Tagger {
    
    private $keyword_rules = array();
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('init', array($this, 'init'));
        add_action('save_post', array($this, 'auto_tag_post'), 20, 2);
        add_action('wp_ajax_adminx_add_tag_rule', array($this, 'ajax_add_tag_rule'));
        add_action('wp_ajax_adminx_delete_tag_rule', array($this, 'ajax_delete_tag_rule'));
        add_action('wp_ajax_adminx_bulk_retag', array($this, 'ajax_bulk_retag'));
    }
    
    /**
     * Initialize
     */
    public function init() {
        $this->load_keyword_rules();
        
        // Add meta box for manual tagging
        add_action('add_meta_boxes', array($this, 'add_auto_tagger_meta_box'));
    }
    
    /**
     * Load keyword rules from options
     */
    private function load_keyword_rules() {
        $this->keyword_rules = get_option('adminx_auto_tag_rules', array());
    }
    
    /**
     * Add auto tagger meta box
     */
    public function add_auto_tagger_meta_box() {
        add_meta_box(
            'adminx-auto-tagger',
            __('Auto Tagger', 'adminx-seo-content'),
            array($this, 'auto_tagger_meta_box_callback'),
            array('post', 'page'),
            'side',
            'default'
        );
    }
    
    /**
     * Auto tagger meta box callback
     */
    public function auto_tagger_meta_box_callback($post) {
        wp_nonce_field('adminx_auto_tagger_meta', 'adminx_auto_tagger_nonce');
        
        $suggested_tags = $this->get_suggested_tags($post);
        $auto_tagging_enabled = get_post_meta($post->ID, '_adminx_auto_tagging_enabled', true) !== '0';
        
        include ADMINX_SEO_CONTENT_PLUGIN_DIR . 'templates/auto-tagger-meta-box.php';
    }
    
    /**
     * Auto tag post on save
     */
    public function auto_tag_post($post_id, $post) {
        if (wp_is_post_revision($post_id) || wp_is_post_autosave($post_id)) {
            return;
        }
        
        // Check if auto tagging is enabled for this post
        $auto_tagging_enabled = get_post_meta($post_id, '_adminx_auto_tagging_enabled', true);
        if ($auto_tagging_enabled === '0') {
            return;
        }
        
        // Only auto-tag published posts
        if ($post->post_status !== 'publish') {
            return;
        }
        
        $this->apply_auto_tags($post_id);
    }
    
    /**
     * Apply auto tags to a post
     */
    public function apply_auto_tags($post_id) {
        $post = get_post($post_id);
        if (!$post) {
            return false;
        }
        
        $suggested_tags = $this->get_suggested_tags($post);
        $current_tags = wp_get_post_tags($post_id, array('fields' => 'names'));
        
        $new_tags = array();
        foreach ($suggested_tags as $tag) {
            if (!in_array($tag['name'], $current_tags)) {
                $new_tags[] = $tag['name'];
            }
        }
        
        if (!empty($new_tags)) {
            $all_tags = array_merge($current_tags, $new_tags);
            wp_set_post_tags($post_id, $all_tags);
            
            // Log the auto-tagging action
            $this->log_auto_tag_action($post_id, $new_tags);
            
            return $new_tags;
        }
        
        return false;
    }
    
    /**
     * Get suggested tags for a post
     */
    public function get_suggested_tags($post) {
        $content = strtolower($post->post_title . ' ' . strip_tags($post->post_content));
        $suggested_tags = array();
        
        // Apply keyword rules
        foreach ($this->keyword_rules as $rule) {
            if ($this->content_matches_rule($content, $rule)) {
                $suggested_tags[] = array(
                    'name' => $rule['tag'],
                    'source' => 'rule',
                    'rule_id' => $rule['id'],
                    'confidence' => $rule['priority']
                );
            }
        }
        
        // Extract tags from content analysis
        $content_tags = $this->extract_tags_from_content($post);
        $suggested_tags = array_merge($suggested_tags, $content_tags);
        
        // Remove duplicates and sort by confidence
        $suggested_tags = $this->deduplicate_tags($suggested_tags);
        usort($suggested_tags, function($a, $b) {
            return $b['confidence'] - $a['confidence'];
        });
        
        return array_slice($suggested_tags, 0, 10);
    }
    
    /**
     * Check if content matches a rule
     */
    private function content_matches_rule($content, $rule) {
        $keywords = explode(',', strtolower($rule['keywords']));
        $match_count = 0;
        
        foreach ($keywords as $keyword) {
            $keyword = trim($keyword);
            if (strpos($content, $keyword) !== false) {
                $match_count++;
            }
        }
        
        // Require at least one keyword match for OR logic, all for AND logic
        if ($rule['logic'] === 'AND') {
            return $match_count === count($keywords);
        } else {
            return $match_count > 0;
        }
    }
    
    /**
     * Extract tags from content analysis
     */
    private function extract_tags_from_content($post) {
        $content = strtolower(strip_tags($post->post_content));
        $title = strtolower($post->post_title);
        
        $tags = array();
        
        // Extract from title (high confidence)
        $title_words = $this->extract_meaningful_words($title);
        foreach ($title_words as $word) {
            if (strlen($word) > 3) {
                $tags[] = array(
                    'name' => ucfirst($word),
                    'source' => 'title',
                    'confidence' => 90
                );
            }
        }
        
        // Extract from content frequency analysis
        $content_words = $this->extract_meaningful_words($content);
        $word_frequency = array_count_values($content_words);
        arsort($word_frequency);
        
        $total_words = count($content_words);
        $count = 0;
        
        foreach ($word_frequency as $word => $frequency) {
            if ($count >= 15 || strlen($word) <= 3) {
                continue;
            }
            
            $density = ($frequency / $total_words) * 100;
            if ($density > 0.5 && $frequency > 2) {
                $confidence = min(80, $density * 10 + $frequency * 5);
                $tags[] = array(
                    'name' => ucfirst($word),
                    'source' => 'content',
                    'confidence' => $confidence
                );
                $count++;
            }
        }
        
        // Extract phrases from title
        $title_phrases = $this->extract_phrases($title);
        foreach ($title_phrases as $phrase) {
            $tags[] = array(
                'name' => ucwords($phrase),
                'source' => 'phrase',
                'confidence' => 85
            );
        }
        
        return $tags;
    }
    
    /**
     * Extract meaningful words (excluding common words)
     */
    private function extract_meaningful_words($text) {
        $common_words = array(
            'the', 'and', 'or', 'but', 'in', 'on', 'at', 'to', 'for', 'of', 'with', 'by',
            'is', 'are', 'was', 'were', 'be', 'been', 'have', 'has', 'had', 'do', 'does',
            'did', 'will', 'would', 'could', 'should', 'may', 'might', 'must', 'can',
            'this', 'that', 'these', 'those', 'a', 'an', 'as', 'if', 'then', 'than',
            'when', 'where', 'why', 'how', 'what', 'who', 'which', 'whose', 'whom',
            'very', 'really', 'quite', 'just', 'only', 'also', 'even', 'still', 'yet',
            'more', 'most', 'much', 'many', 'some', 'any', 'all', 'each', 'every',
            'both', 'either', 'neither', 'not', 'no', 'yes', 'well', 'good', 'better',
            'best', 'bad', 'worse', 'worst', 'big', 'small', 'large', 'little', 'long',
            'short', 'high', 'low', 'new', 'old', 'young', 'first', 'last', 'next',
            'previous', 'same', 'different', 'other', 'another', 'such', 'own', 'right',
            'left', 'here', 'there', 'now', 'then', 'today', 'tomorrow', 'yesterday'
        );
        
        $words = str_word_count($text, 1);
        $meaningful_words = array();
        
        foreach ($words as $word) {
            if (!in_array(strtolower($word), $common_words) && strlen($word) > 2) {
                $meaningful_words[] = strtolower($word);
            }
        }
        
        return $meaningful_words;
    }
    
    /**
     * Extract phrases from text
     */
    private function extract_phrases($text) {
        $words = explode(' ', $text);
        $phrases = array();
        
        // 2-word phrases
        for ($i = 0; $i < count($words) - 1; $i++) {
            $phrase = trim($words[$i] . ' ' . $words[$i + 1]);
            if (strlen($phrase) > 6 && !$this->is_common_phrase($phrase)) {
                $phrases[] = $phrase;
            }
        }
        
        return $phrases;
    }
    
    /**
     * Check if phrase is common/meaningless
     */
    private function is_common_phrase($phrase) {
        $common_phrases = array(
            'in the', 'on the', 'at the', 'to the', 'for the', 'of the', 'with the',
            'by the', 'is a', 'are a', 'was a', 'were a', 'this is', 'that is',
            'it is', 'there is', 'there are', 'you can', 'we can', 'i can'
        );
        
        return in_array(strtolower($phrase), $common_phrases);
    }
    
    /**
     * Remove duplicate tags
     */
    private function deduplicate_tags($tags) {
        $unique_tags = array();
        $seen_names = array();
        
        foreach ($tags as $tag) {
            $name_lower = strtolower($tag['name']);
            if (!in_array($name_lower, $seen_names)) {
                $unique_tags[] = $tag;
                $seen_names[] = $name_lower;
            }
        }
        
        return $unique_tags;
    }
    
    /**
     * Log auto-tag action
     */
    private function log_auto_tag_action($post_id, $tags) {
        $log_entry = array(
            'post_id' => $post_id,
            'tags' => $tags,
            'timestamp' => current_time('mysql'),
            'user_id' => get_current_user_id()
        );
        
        $log = get_option('adminx_auto_tag_log', array());
        array_unshift($log, $log_entry);
        
        // Keep only last 100 entries
        $log = array_slice($log, 0, 100);
        
        update_option('adminx_auto_tag_log', $log);
    }
    
    /**
     * AJAX handler for adding tag rule
     */
    public function ajax_add_tag_rule() {
        check_ajax_referer('adminx_seo_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die(__('Insufficient permissions', 'adminx-seo-content'));
        }
        
        $keywords = sanitize_text_field($_POST['keywords']);
        $tag = sanitize_text_field($_POST['tag']);
        $logic = sanitize_text_field($_POST['logic']);
        $priority = intval($_POST['priority']);
        
        $rule = array(
            'id' => uniqid(),
            'keywords' => $keywords,
            'tag' => $tag,
            'logic' => $logic,
            'priority' => $priority,
            'created' => current_time('mysql')
        );
        
        $this->keyword_rules[] = $rule;
        update_option('adminx_auto_tag_rules', $this->keyword_rules);
        
        wp_send_json_success(array(
            'message' => __('Tag rule added successfully', 'adminx-seo-content'),
            'rule' => $rule
        ));
    }
    
    /**
     * AJAX handler for deleting tag rule
     */
    public function ajax_delete_tag_rule() {
        check_ajax_referer('adminx_seo_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die(__('Insufficient permissions', 'adminx-seo-content'));
        }
        
        $rule_id = sanitize_text_field($_POST['rule_id']);
        
        $this->keyword_rules = array_filter($this->keyword_rules, function($rule) use ($rule_id) {
            return $rule['id'] !== $rule_id;
        });
        
        update_option('adminx_auto_tag_rules', array_values($this->keyword_rules));
        
        wp_send_json_success(array(
            'message' => __('Tag rule deleted successfully', 'adminx-seo-content')
        ));
    }
    
    /**
     * AJAX handler for bulk retagging
     */
    public function ajax_bulk_retag() {
        check_ajax_referer('adminx_seo_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die(__('Insufficient permissions', 'adminx-seo-content'));
        }
        
        $post_type = sanitize_text_field($_POST['post_type']);
        $limit = intval($_POST['limit']) ?: 50;
        
        $posts = get_posts(array(
            'post_type' => $post_type,
            'post_status' => 'publish',
            'numberposts' => $limit,
            'meta_query' => array(
                array(
                    'key' => '_adminx_auto_tagging_enabled',
                    'value' => '0',
                    'compare' => '!='
                )
            )
        ));
        
        $processed = 0;
        $tagged = 0;
        
        foreach ($posts as $post) {
            $new_tags = $this->apply_auto_tags($post->ID);
            $processed++;
            
            if ($new_tags) {
                $tagged++;
            }
        }
        
        wp_send_json_success(array(
            'message' => sprintf(__('Processed %d posts, added tags to %d posts', 'adminx-seo-content'), $processed, $tagged),
            'processed' => $processed,
            'tagged' => $tagged
        ));
    }
    
    /**
     * Get auto-tagging statistics
     */
    public function get_auto_tag_statistics() {
        $stats = array();
        
        // Total rules
        $stats['total_rules'] = count($this->keyword_rules);
        
        // Recent auto-tag actions
        $log = get_option('adminx_auto_tag_log', array());
        $stats['recent_actions'] = array_slice($log, 0, 10);
        
        // Posts with auto-tagging enabled
        global $wpdb;
        $stats['posts_with_auto_tagging'] = $wpdb->get_var("
            SELECT COUNT(*)
            FROM {$wpdb->posts} p
            LEFT JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = '_adminx_auto_tagging_enabled'
            WHERE p.post_status = 'publish'
            AND p.post_type IN ('post', 'page')
            AND (pm.meta_value IS NULL OR pm.meta_value != '0')
        ");
        
        // Most used auto-generated tags
        $auto_tag_log = get_option('adminx_auto_tag_log', array());
        $tag_usage = array();
        
        foreach ($auto_tag_log as $entry) {
            foreach ($entry['tags'] as $tag) {
                if (!isset($tag_usage[$tag])) {
                    $tag_usage[$tag] = 0;
                }
                $tag_usage[$tag]++;
            }
        }
        
        arsort($tag_usage);
        $stats['popular_auto_tags'] = array_slice($tag_usage, 0, 10, true);
        
        return $stats;
    }
    
    /**
     * Get tag rules
     */
    public function get_tag_rules() {
        return $this->keyword_rules;
    }
}