<?php
/**
 * Plugin Name: AdminX SEO & Content
 * Plugin URI: https://github.com/arunrajiah/adminx-plugins/adminx-seo-content
 * Description: Comprehensive SEO health checker, auto internal link manager, content scheduler, and auto-tagging system for WordPress administrators.
 * Version: 1.0.0
 * Author: AdminX
 * Author URI: https://adminx.dev
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: adminx-seo-content
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.3
 * Requires PHP: 7.4
 * Network: false
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('ADMINX_SEO_CONTENT_VERSION', '1.0.0');
define('ADMINX_SEO_CONTENT_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('ADMINX_SEO_CONTENT_PLUGIN_URL', plugin_dir_url(__FILE__));
define('ADMINX_SEO_CONTENT_PLUGIN_FILE', __FILE__);

/**
 * Main AdminX SEO Content Plugin Class
 */
class AdminX_SEO_Content {
    
    /**
     * Plugin instance
     */
    private static $instance = null;
    
    /**
     * Get plugin instance
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        add_action('plugins_loaded', array($this, 'init'));
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
    }
    
    /**
     * Initialize plugin
     */
    public function init() {
        // Load text domain
        load_plugin_textdomain('adminx-seo-content', false, dirname(plugin_basename(__FILE__)) . '/languages');
        
        // Include required files
        $this->includes();
        
        // Initialize components
        $this->init_components();
        
        // Add admin hooks
        if (is_admin()) {
            add_action('admin_menu', array($this, 'add_admin_menu'));
            add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        }
    }
    
    /**
     * Include required files
     */
    private function includes() {
        require_once ADMINX_SEO_CONTENT_PLUGIN_DIR . 'includes/class-seo-health-checker.php';
        require_once ADMINX_SEO_CONTENT_PLUGIN_DIR . 'includes/class-internal-link-manager.php';
        require_once ADMINX_SEO_CONTENT_PLUGIN_DIR . 'includes/class-content-scheduler.php';
        require_once ADMINX_SEO_CONTENT_PLUGIN_DIR . 'includes/class-auto-tagger.php';
    }
    
    /**
     * Initialize components
     */
    private function init_components() {
        new AdminX_SEO_Health_Checker();
        new AdminX_Internal_Link_Manager();
        new AdminX_Content_Scheduler();
        new AdminX_Auto_Tagger();
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_menu_page(
            __('AdminX SEO & Content', 'adminx-seo-content'),
            __('AdminX SEO', 'adminx-seo-content'),
            'manage_options',
            'adminx-seo-content',
            array($this, 'admin_page'),
            'dashicons-search',
            30
        );
        
        add_submenu_page(
            'adminx-seo-content',
            __('SEO Health Check', 'adminx-seo-content'),
            __('SEO Health', 'adminx-seo-content'),
            'manage_options',
            'adminx-seo-health',
            array($this, 'seo_health_page')
        );
        
        add_submenu_page(
            'adminx-seo-content',
            __('Internal Links', 'adminx-seo-content'),
            __('Internal Links', 'adminx-seo-content'),
            'manage_options',
            'adminx-internal-links',
            array($this, 'internal_links_page')
        );
        
        add_submenu_page(
            'adminx-seo-content',
            __('Content Scheduler', 'adminx-seo-content'),
            __('Scheduler', 'adminx-seo-content'),
            'manage_options',
            'adminx-content-scheduler',
            array($this, 'content_scheduler_page')
        );
        
        add_submenu_page(
            'adminx-seo-content',
            __('Auto Tagging', 'adminx-seo-content'),
            __('Auto Tagging', 'adminx-seo-content'),
            'manage_options',
            'adminx-auto-tagging',
            array($this, 'auto_tagging_page')
        );
    }
    
    /**
     * Enqueue admin scripts and styles
     */
    public function enqueue_admin_scripts($hook) {
        if (strpos($hook, 'adminx-seo') === false) {
            return;
        }
        
        wp_enqueue_style(
            'adminx-seo-content-admin',
            ADMINX_SEO_CONTENT_PLUGIN_URL . 'assets/admin.css',
            array(),
            ADMINX_SEO_CONTENT_VERSION
        );
        
        wp_enqueue_script(
            'adminx-seo-content-admin',
            ADMINX_SEO_CONTENT_PLUGIN_URL . 'assets/admin.js',
            array('jquery'),
            ADMINX_SEO_CONTENT_VERSION,
            true
        );
        
        wp_localize_script('adminx-seo-content-admin', 'adminx_seo_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('adminx_seo_nonce')
        ));
    }
    
    /**
     * Main admin page
     */
    public function admin_page() {
        include ADMINX_SEO_CONTENT_PLUGIN_DIR . 'templates/admin-main.php';
    }
    
    /**
     * SEO health page
     */
    public function seo_health_page() {
        include ADMINX_SEO_CONTENT_PLUGIN_DIR . 'templates/seo-health.php';
    }
    
    /**
     * Internal links page
     */
    public function internal_links_page() {
        include ADMINX_SEO_CONTENT_PLUGIN_DIR . 'templates/internal-links.php';
    }
    
    /**
     * Content scheduler page
     */
    public function content_scheduler_page() {
        include ADMINX_SEO_CONTENT_PLUGIN_DIR . 'templates/content-scheduler.php';
    }
    
    /**
     * Auto tagging page
     */
    public function auto_tagging_page() {
        include ADMINX_SEO_CONTENT_PLUGIN_DIR . 'templates/auto-tagging.php';
    }
    
    /**
     * Plugin activation
     */
    public function activate() {
        // Create database tables if needed
        $this->create_tables();
        
        // Set default options
        add_option('adminx_seo_content_version', ADMINX_SEO_CONTENT_VERSION);
        add_option('adminx_seo_health_enabled', 1);
        add_option('adminx_internal_links_enabled', 1);
        add_option('adminx_content_scheduler_enabled', 1);
        add_option('adminx_auto_tagging_enabled', 1);
        
        // Schedule cron jobs
        if (!wp_next_scheduled('adminx_seo_health_check')) {
            wp_schedule_event(time(), 'daily', 'adminx_seo_health_check');
        }
        
        if (!wp_next_scheduled('adminx_content_scheduler_check')) {
            wp_schedule_event(time(), 'hourly', 'adminx_content_scheduler_check');
        }
    }
    
    /**
     * Plugin deactivation
     */
    public function deactivate() {
        // Clear scheduled events
        wp_clear_scheduled_hook('adminx_seo_health_check');
        wp_clear_scheduled_hook('adminx_content_scheduler_check');
    }
    
    /**
     * Create database tables
     */
    private function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // SEO health check results table
        $table_name = $wpdb->prefix . 'adminx_seo_health';
        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            post_id bigint(20) NOT NULL,
            check_type varchar(50) NOT NULL,
            status varchar(20) NOT NULL,
            message text,
            checked_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY post_id (post_id),
            KEY check_type (check_type)
        ) $charset_collate;";
        
        // Content scheduler table
        $scheduler_table = $wpdb->prefix . 'adminx_content_scheduler';
        $sql2 = "CREATE TABLE $scheduler_table (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            post_id bigint(20) NOT NULL,
            action_type varchar(20) NOT NULL,
            scheduled_date datetime NOT NULL,
            status varchar(20) DEFAULT 'pending',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY post_id (post_id),
            KEY scheduled_date (scheduled_date)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        dbDelta($sql2);
    }
}

// Initialize the plugin
AdminX_SEO_Content::get_instance();
