<?php
/**
 * Content Scheduler Class
 * 
 * Handles content scheduling functionality including publish/unpublish and expiry
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class AdminX_Content_Scheduler {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('init', array($this, 'init'));
        add_action('adminx_content_scheduler_check', array($this, 'process_scheduled_actions'));
        add_action('wp_ajax_adminx_schedule_content', array($this, 'ajax_schedule_content'));
        add_action('wp_ajax_adminx_get_scheduled_content', array($this, 'ajax_get_scheduled_content'));
        add_action('add_meta_boxes', array($this, 'add_scheduler_meta_box'));
        add_action('save_post', array($this, 'save_scheduler_meta'), 10, 2);
    }
    
    /**
     * Initialize
     */
    public function init() {
        // Add custom post statuses
        $this->register_post_statuses();
        
        // Add admin columns
        add_filter('manage_posts_columns', array($this, 'add_scheduler_column'));
        add_filter('manage_pages_columns', array($this, 'add_scheduler_column'));
        add_action('manage_posts_custom_column', array($this, 'display_scheduler_column'), 10, 2);
        add_action('manage_pages_custom_column', array($this, 'display_scheduler_column'), 10, 2);
    }
    
    /**
     * Register custom post statuses
     */
    private function register_post_statuses() {
        register_post_status('scheduled_unpublish', array(
            'label' => __('Scheduled to Unpublish', 'adminx-seo-content'),
            'public' => true,
            'exclude_from_search' => false,
            'show_in_admin_all_list' => true,
            'show_in_admin_status_list' => true,
            'label_count' => _n_noop('Scheduled to Unpublish <span class="count">(%s)</span>', 'Scheduled to Unpublish <span class="count">(%s)</span>', 'adminx-seo-content')
        ));
        
        register_post_status('expired', array(
            'label' => __('Expired', 'adminx-seo-content'),
            'public' => false,
            'exclude_from_search' => true,
            'show_in_admin_all_list' => true,
            'show_in_admin_status_list' => true,
            'label_count' => _n_noop('Expired <span class="count">(%s)</span>', 'Expired <span class="count">(%s)</span>', 'adminx-seo-content')
        ));
    }
    
    /**
     * Add scheduler meta box
     */
    public function add_scheduler_meta_box() {
        add_meta_box(
            'adminx-content-scheduler',
            __('Content Scheduler', 'adminx-seo-content'),
            array($this, 'scheduler_meta_box_callback'),
            array('post', 'page'),
            'side',
            'default'
        );
    }
    
    /**
     * Scheduler meta box callback
     */
    public function scheduler_meta_box_callback($post) {
        wp_nonce_field('adminx_scheduler_meta', 'adminx_scheduler_nonce');
        
        $scheduled_actions = $this->get_post_scheduled_actions($post->ID);
        $expiry_date = get_post_meta($post->ID, '_adminx_expiry_date', true);
        $auto_unpublish = get_post_meta($post->ID, '_adminx_auto_unpublish', true);
        
        include ADMINX_SEO_CONTENT_PLUGIN_DIR . 'templates/scheduler-meta-box.php';
    }
    
    /**
     * Save scheduler meta
     */
    public function save_scheduler_meta($post_id, $post) {
        if (!isset($_POST['adminx_scheduler_nonce']) || !wp_verify_nonce($_POST['adminx_scheduler_nonce'], 'adminx_scheduler_meta')) {
            return;
        }
        
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
        
        // Save expiry date
        if (isset($_POST['adminx_expiry_date']) && !empty($_POST['adminx_expiry_date'])) {
            $expiry_date = sanitize_text_field($_POST['adminx_expiry_date']);
            update_post_meta($post_id, '_adminx_expiry_date', $expiry_date);
            
            // Schedule expiry action
            $this->schedule_action($post_id, 'expire', $expiry_date);
        } else {
            delete_post_meta($post_id, '_adminx_expiry_date');
        }
        
        // Save auto unpublish setting
        $auto_unpublish = isset($_POST['adminx_auto_unpublish']) ? 1 : 0;
        update_post_meta($post_id, '_adminx_auto_unpublish', $auto_unpublish);
        
        // Save scheduled unpublish date
        if (isset($_POST['adminx_unpublish_date']) && !empty($_POST['adminx_unpublish_date'])) {
            $unpublish_date = sanitize_text_field($_POST['adminx_unpublish_date']);
            $this->schedule_action($post_id, 'unpublish', $unpublish_date);
        }
        
        // Save scheduled republish date
        if (isset($_POST['adminx_republish_date']) && !empty($_POST['adminx_republish_date'])) {
            $republish_date = sanitize_text_field($_POST['adminx_republish_date']);
            $this->schedule_action($post_id, 'republish', $republish_date);
        }
    }
    
    /**
     * Schedule an action
     */
    public function schedule_action($post_id, $action_type, $scheduled_date) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'adminx_content_scheduler';
        
        // Remove existing scheduled action of same type
        $wpdb->delete(
            $table_name,
            array(
                'post_id' => $post_id,
                'action_type' => $action_type,
                'status' => 'pending'
            )
        );
        
        // Insert new scheduled action
        $wpdb->insert(
            $table_name,
            array(
                'post_id' => $post_id,
                'action_type' => $action_type,
                'scheduled_date' => $scheduled_date,
                'status' => 'pending',
                'created_at' => current_time('mysql')
            ),
            array('%d', '%s', '%s', '%s', '%s')
        );
    }
    
    /**
     * Get scheduled actions for a post
     */
    public function get_post_scheduled_actions($post_id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'adminx_content_scheduler';
        
        $actions = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table_name WHERE post_id = %d AND status = 'pending' ORDER BY scheduled_date ASC",
            $post_id
        ));
        
        return $actions;
    }
    
    /**
     * Process scheduled actions
     */
    public function process_scheduled_actions() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'adminx_content_scheduler';
        $current_time = current_time('mysql');
        
        // Get actions that are due
        $due_actions = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table_name WHERE scheduled_date <= %s AND status = 'pending' ORDER BY scheduled_date ASC",
            $current_time
        ));
        
        foreach ($due_actions as $action) {
            $result = $this->execute_action($action);
            
            // Update action status
            $wpdb->update(
                $table_name,
                array(
                    'status' => $result ? 'completed' : 'failed',
                    'executed_at' => current_time('mysql')
                ),
                array('id' => $action->id),
                array('%s', '%s'),
                array('%d')
            );
        }
    }
    
    /**
     * Execute a scheduled action
     */
    private function execute_action($action) {
        $post = get_post($action->post_id);
        if (!$post) {
            return false;
        }
        
        switch ($action->action_type) {
            case 'publish':
                return $this->publish_post($action->post_id);
                
            case 'unpublish':
                return $this->unpublish_post($action->post_id);
                
            case 'republish':
                return $this->republish_post($action->post_id);
                
            case 'expire':
                return $this->expire_post($action->post_id);
                
            default:
                return false;
        }
    }
    
    /**
     * Publish a post
     */
    private function publish_post($post_id) {
        $result = wp_update_post(array(
            'ID' => $post_id,
            'post_status' => 'publish'
        ));
        
        if ($result && !is_wp_error($result)) {
            do_action('adminx_post_scheduled_published', $post_id);
            return true;
        }
        
        return false;
    }
    
    /**
     * Unpublish a post
     */
    private function unpublish_post($post_id) {
        $result = wp_update_post(array(
            'ID' => $post_id,
            'post_status' => 'draft'
        ));
        
        if ($result && !is_wp_error($result)) {
            do_action('adminx_post_scheduled_unpublished', $post_id);
            return true;
        }
        
        return false;
    }
    
    /**
     * Republish a post
     */
    private function republish_post($post_id) {
        $result = wp_update_post(array(
            'ID' => $post_id,
            'post_status' => 'publish'
        ));
        
        if ($result && !is_wp_error($result)) {
            do_action('adminx_post_scheduled_republished', $post_id);
            return true;
        }
        
        return false;
    }
    
    /**
     * Expire a post
     */
    private function expire_post($post_id) {
        $auto_unpublish = get_post_meta($post_id, '_adminx_auto_unpublish', true);
        
        if ($auto_unpublish) {
            $result = wp_update_post(array(
                'ID' => $post_id,
                'post_status' => 'expired'
            ));
        } else {
            // Just mark as expired without changing status
            update_post_meta($post_id, '_adminx_expired', true);
            $result = true;
        }
        
        if ($result && !is_wp_error($result)) {
            do_action('adminx_post_expired', $post_id);
            return true;
        }
        
        return false;
    }
    
    /**
     * Add scheduler column to post list
     */
    public function add_scheduler_column($columns) {
        $columns['adminx_scheduler'] = __('Scheduled Actions', 'adminx-seo-content');
        return $columns;
    }
    
    /**
     * Display scheduler column content
     */
    public function display_scheduler_column($column, $post_id) {
        if ($column === 'adminx_scheduler') {
            $actions = $this->get_post_scheduled_actions($post_id);
            $expiry_date = get_post_meta($post_id, '_adminx_expiry_date', true);
            
            if (!empty($actions)) {
                foreach ($actions as $action) {
                    $date = date('M j, Y H:i', strtotime($action->scheduled_date));
                    echo '<div><strong>' . ucfirst($action->action_type) . ':</strong> ' . $date . '</div>';
                }
            } elseif ($expiry_date) {
                $date = date('M j, Y H:i', strtotime($expiry_date));
                echo '<div><strong>Expires:</strong> ' . $date . '</div>';
            } else {
                echo '—';
            }
        }
    }
    
    /**
     * AJAX handler for scheduling content
     */
    public function ajax_schedule_content() {
        check_ajax_referer('adminx_seo_nonce', 'nonce');
        
        if (!current_user_can('edit_posts')) {
            wp_die(__('Insufficient permissions', 'adminx-seo-content'));
        }
        
        $post_id = intval($_POST['post_id']);
        $action_type = sanitize_text_field($_POST['action_type']);
        $scheduled_date = sanitize_text_field($_POST['scheduled_date']);
        
        $this->schedule_action($post_id, $action_type, $scheduled_date);
        
        wp_send_json_success(array(
            'message' => __('Content scheduled successfully', 'adminx-seo-content')
        ));
    }
    
    /**
     * AJAX handler for getting scheduled content
     */
    public function ajax_get_scheduled_content() {
        check_ajax_referer('adminx_seo_nonce', 'nonce');
        
        if (!current_user_can('edit_posts')) {
            wp_die(__('Insufficient permissions', 'adminx-seo-content'));
        }
        
        $scheduled_content = $this->get_all_scheduled_content();
        
        wp_send_json_success($scheduled_content);
    }
    
    /**
     * Get all scheduled content
     */
    public function get_all_scheduled_content() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'adminx_content_scheduler';
        
        $scheduled = $wpdb->get_results("
            SELECT s.*, p.post_title, p.post_status
            FROM $table_name s
            LEFT JOIN {$wpdb->posts} p ON s.post_id = p.ID
            WHERE s.status = 'pending'
            ORDER BY s.scheduled_date ASC
        ");
        
        return $scheduled;
    }
    
    /**
     * Get scheduler statistics
     */
    public function get_scheduler_statistics() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'adminx_content_scheduler';
        
        $stats = array();
        
        // Pending actions
        $stats['pending_actions'] = $wpdb->get_var("
            SELECT COUNT(*) FROM $table_name WHERE status = 'pending'
        ");
        
        // Actions by type
        $stats['actions_by_type'] = $wpdb->get_results("
            SELECT action_type, COUNT(*) as count
            FROM $table_name
            WHERE status = 'pending'
            GROUP BY action_type
        ");
        
        // Upcoming actions (next 7 days)
        $stats['upcoming_actions'] = $wpdb->get_results("
            SELECT s.*, p.post_title
            FROM $table_name s
            LEFT JOIN {$wpdb->posts} p ON s.post_id = p.ID
            WHERE s.status = 'pending'
            AND s.scheduled_date BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 7 DAY)
            ORDER BY s.scheduled_date ASC
            LIMIT 10
        ");
        
        // Expired posts
        $stats['expired_posts'] = $wpdb->get_var("
            SELECT COUNT(*)
            FROM {$wpdb->posts} p
            INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
            WHERE pm.meta_key = '_adminx_expired'
            AND pm.meta_value = '1'
        ");
        
        return $stats;
    }
    
    /**
     * Clean up old scheduler data
     */
    public function cleanup_old_data() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'adminx_content_scheduler';
        
        // Delete completed/failed actions older than 30 days
        $wpdb->query("
            DELETE FROM $table_name
            WHERE status IN ('completed', 'failed')
            AND created_at < DATE_SUB(NOW(), INTERVAL 30 DAY)
        ");
    }
}