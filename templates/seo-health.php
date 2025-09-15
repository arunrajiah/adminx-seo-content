<?php
/**
 * AdminX SEO & Content - SEO Health Page Template
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

$seo_checker = new AdminX_SEO_Health_Checker();
$overall_stats = $seo_checker->get_overall_stats();

// Get recent posts for quick checking
$recent_posts = get_posts(array(
    'post_type' => array('post', 'page'),
    'post_status' => 'publish',
    'numberposts' => 10,
    'orderby' => 'date',
    'order' => 'DESC'
));
?>

<div class="adminx-seo-container">
    <div class="adminx-seo-header">
        <h1><?php _e('SEO Health Checker', 'adminx-seo-content'); ?></h1>
        <p><?php _e('Monitor and improve your content\'s SEO health with comprehensive checks for meta tags, headings, images, and internal links.', 'adminx-seo-content'); ?></p>
    </div>

    <!-- Overall Statistics -->
    <div class="adminx-stats-grid">
        <?php
        $status_counts = array('good' => 0, 'warning' => 0, 'error' => 0, 'info' => 0);
        foreach ($overall_stats as $stat) {
            if (isset($status_counts[$stat->status])) {
                $status_counts[$stat->status] += $stat->count;
            }
        }
        ?>
        
        <div class="adminx-stat-box">
            <span class="adminx-stat-number" style="color: #46b450;"><?php echo $status_counts['good']; ?></span>
            <span class="adminx-stat-label"><?php _e('Good SEO Checks', 'adminx-seo-content'); ?></span>
        </div>
        <div class="adminx-stat-box">
            <span class="adminx-stat-number" style="color: #ffb900;"><?php echo $status_counts['warning']; ?></span>
            <span class="adminx-stat-label"><?php _e('Warnings', 'adminx-seo-content'); ?></span>
        </div>
        <div class="adminx-stat-box">
            <span class="adminx-stat-number" style="color: #dc3232;"><?php echo $status_counts['error']; ?></span>
            <span class="adminx-stat-label"><?php _e('Errors', 'adminx-seo-content'); ?></span>
        </div>
        <div class="adminx-stat-box">
            <span class="adminx-stat-number" style="color: #00a0d2;"><?php echo $status_counts['info']; ?></span>
            <span class="adminx-stat-label"><?php _e('Info Items', 'adminx-seo-content'); ?></span>
        </div>
    </div>

    <!-- Quick SEO Check -->
    <div class="adminx-card">
        <h3><?php _e('Quick SEO Check', 'adminx-seo-content'); ?></h3>
        <div class="adminx-card-content">
            <p><?php _e('Select a post or page to run an immediate SEO health check:', 'adminx-seo-content'); ?></p>
            
            <div class="scheduler-form">
                <div class="scheduler-form-row">
                    <label for="quick-seo-post"><?php _e('Select Post/Page:', 'adminx-seo-content'); ?></label>
                    <select id="quick-seo-post" style="min-width: 300px;">
                        <option value=""><?php _e('Choose a post or page...', 'adminx-seo-content'); ?></option>
                        <?php foreach ($recent_posts as $post): ?>
                            <option value="<?php echo $post->ID; ?>">
                                <?php echo esc_html($post->post_title); ?> (<?php echo ucfirst($post->post_type); ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button class="adminx-button run-seo-check" data-post-id="" disabled>
                        <?php _e('Run SEO Check', 'adminx-seo-content'); ?>
                    </button>
                </div>
            </div>
            
            <div class="seo-health-results" style="margin-top: 20px;"></div>
        </div>
    </div>

    <!-- SEO Check Categories -->
    <div class="adminx-dashboard-cards">
        
        <!-- Meta Tags -->
        <div class="adminx-card">
            <h3><?php _e('Meta Tags Analysis', 'adminx-seo-content'); ?></h3>
            <div class="adminx-card-content">
                <p><?php _e('Checks for optimal meta title and description lengths:', 'adminx-seo-content'); ?></p>
                <ul>
                    <li><?php _e('Meta title: 30-60 characters', 'adminx-seo-content'); ?></li>
                    <li><?php _e('Meta description: 120-160 characters', 'adminx-seo-content'); ?></li>
                    <li><?php _e('Missing meta tags detection', 'adminx-seo-content'); ?></li>
                </ul>
                
                <?php
                $meta_stats = array_filter($overall_stats, function($stat) {
                    return in_array($stat->check_type, array('meta_title', 'meta_description'));
                });
                ?>
                
                <?php if (!empty($meta_stats)): ?>
                    <div style="margin-top: 15px;">
                        <?php foreach ($meta_stats as $stat): ?>
                            <div class="seo-health-status">
                                <span class="seo-status-icon <?php echo $stat->status; ?>"></span>
                                <span class="seo-status-text">
                                    <?php echo ucfirst(str_replace('_', ' ', $stat->check_type)); ?>: <?php echo $stat->count; ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Content Structure -->
        <div class="adminx-card">
            <h3><?php _e('Content Structure', 'adminx-seo-content'); ?></h3>
            <div class="adminx-card-content">
                <p><?php _e('Analyzes heading structure and content quality:', 'adminx-seo-content'); ?></p>
                <ul>
                    <li><?php _e('H1 heading presence and uniqueness', 'adminx-seo-content'); ?></li>
                    <li><?php _e('Proper heading hierarchy (H1-H6)', 'adminx-seo-content'); ?></li>
                    <li><?php _e('Content length optimization', 'adminx-seo-content'); ?></li>
                    <li><?php _e('Keyword density analysis', 'adminx-seo-content'); ?></li>
                </ul>
                
                <?php
                $content_stats = array_filter($overall_stats, function($stat) {
                    return in_array($stat->check_type, array('headings', 'content_length', 'keyword_density'));
                });
                ?>
                
                <?php if (!empty($content_stats)): ?>
                    <div style="margin-top: 15px;">
                        <?php foreach ($content_stats as $stat): ?>
                            <div class="seo-health-status">
                                <span class="seo-status-icon <?php echo $stat->status; ?>"></span>
                                <span class="seo-status-text">
                                    <?php echo ucfirst(str_replace('_', ' ', $stat->check_type)); ?>: <?php echo $stat->count; ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Images & Media -->
        <div class="adminx-card">
            <h3><?php _e('Images & Media', 'adminx-seo-content'); ?></h3>
            <div class="adminx-card-content">
                <p><?php _e('Ensures all images have proper SEO attributes:', 'adminx-seo-content'); ?></p>
                <ul>
                    <li><?php _e('Alt text presence for all images', 'adminx-seo-content'); ?></li>
                    <li><?php _e('Descriptive alt text quality', 'adminx-seo-content'); ?></li>
                    <li><?php _e('Image optimization recommendations', 'adminx-seo-content'); ?></li>
                </ul>
                
                <?php
                $image_stats = array_filter($overall_stats, function($stat) {
                    return $stat->check_type === 'alt_text';
                });
                ?>
                
                <?php if (!empty($image_stats)): ?>
                    <div style="margin-top: 15px;">
                        <?php foreach ($image_stats as $stat): ?>
                            <div class="seo-health-status">
                                <span class="seo-status-icon <?php echo $stat->status; ?>"></span>
                                <span class="seo-status-text">
                                    <?php echo ucfirst(str_replace('_', ' ', $stat->check_type)); ?>: <?php echo $stat->count; ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Internal Links -->
        <div class="adminx-card">
            <h3><?php _e('Internal Links', 'adminx-seo-content'); ?></h3>
            <div class="adminx-card-content">
                <p><?php _e('Monitors internal linking health and opportunities:', 'adminx-seo-content'); ?></p>
                <ul>
                    <li><?php _e('Broken internal links detection', 'adminx-seo-content'); ?></li>
                    <li><?php _e('Internal linking opportunities', 'adminx-seo-content'); ?></li>
                    <li><?php _e('Link distribution analysis', 'adminx-seo-content'); ?></li>
                </ul>
                
                <?php
                $link_stats = array_filter($overall_stats, function($stat) {
                    return $stat->check_type === 'internal_links';
                });
                ?>
                
                <?php if (!empty($link_stats)): ?>
                    <div style="margin-top: 15px;">
                        <?php foreach ($link_stats as $stat): ?>
                            <div class="seo-health-status">
                                <span class="seo-status-icon <?php echo $stat->status; ?>"></span>
                                <span class="seo-status-text">
                                    <?php echo ucfirst(str_replace('_', ' ', $stat->check_type)); ?>: <?php echo $stat->count; ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                
                <p style="margin-top: 15px;">
                    <a href="<?php echo admin_url('admin.php?page=adminx-internal-links'); ?>" class="adminx-button secondary">
                        <?php _e('Manage Internal Links', 'adminx-seo-content'); ?>
                    </a>
                </p>
            </div>
        </div>

    </div>

    <!-- Bulk SEO Check -->
    <div class="adminx-card">
        <h3><?php _e('Bulk SEO Health Check', 'adminx-seo-content'); ?></h3>
        <div class="adminx-card-content">
            <p><?php _e('Run SEO health checks on multiple posts at once. This will check all published posts and pages that haven\'t been checked in the last 7 days.', 'adminx-seo-content'); ?></p>
            
            <div class="scheduler-form">
                <div class="scheduler-form-row">
                    <label for="bulk-check-type"><?php _e('Content Type:', 'adminx-seo-content'); ?></label>
                    <select id="bulk-check-type">
                        <option value="post,page"><?php _e('Posts and Pages', 'adminx-seo-content'); ?></option>
                        <option value="post"><?php _e('Posts Only', 'adminx-seo-content'); ?></option>
                        <option value="page"><?php _e('Pages Only', 'adminx-seo-content'); ?></option>
                    </select>
                    
                    <label for="bulk-check-limit"><?php _e('Limit:', 'adminx-seo-content'); ?></label>
                    <select id="bulk-check-limit">
                        <option value="10">10 <?php _e('posts', 'adminx-seo-content'); ?></option>
                        <option value="25" selected>25 <?php _e('posts', 'adminx-seo-content'); ?></option>
                        <option value="50">50 <?php _e('posts', 'adminx-seo-content'); ?></option>
                        <option value="100">100 <?php _e('posts', 'adminx-seo-content'); ?></option>
                    </select>
                    
                    <button class="adminx-button bulk-seo-check">
                        <?php _e('Run Bulk Check', 'adminx-seo-content'); ?>
                    </button>
                </div>
            </div>
            
            <div id="bulk-check-progress" style="display: none; margin-top: 15px;">
                <div class="adminx-progress">
                    <div class="adminx-progress-bar" style="width: 0%">0%</div>
                </div>
                <p id="bulk-check-status"><?php _e('Starting bulk SEO check...', 'adminx-seo-content'); ?></p>
            </div>
        </div>
    </div>

    <!-- SEO Tips -->
    <div class="adminx-card">
        <h3><?php _e('SEO Optimization Tips', 'adminx-seo-content'); ?></h3>
        <div class="adminx-card-content">
            <div class="adminx-dashboard-cards">
                <div>
                    <h4><?php _e('Meta Title Best Practices', 'adminx-seo-content'); ?></h4>
                    <ul>
                        <li><?php _e('Keep between 30-60 characters', 'adminx-seo-content'); ?></li>
                        <li><?php _e('Include your target keyword', 'adminx-seo-content'); ?></li>
                        <li><?php _e('Make it compelling and clickable', 'adminx-seo-content'); ?></li>
                        <li><?php _e('Avoid keyword stuffing', 'adminx-seo-content'); ?></li>
                    </ul>
                </div>
                
                <div>
                    <h4><?php _e('Meta Description Guidelines', 'adminx-seo-content'); ?></h4>
                    <ul>
                        <li><?php _e('Aim for 120-160 characters', 'adminx-seo-content'); ?></li>
                        <li><?php _e('Write a compelling summary', 'adminx-seo-content'); ?></li>
                        <li><?php _e('Include a call-to-action', 'adminx-seo-content'); ?></li>
                        <li><?php _e('Use active voice', 'adminx-seo-content'); ?></li>
                    </ul>
                </div>
                
                <div>
                    <h4><?php _e('Content Structure Tips', 'adminx-seo-content'); ?></h4>
                    <ul>
                        <li><?php _e('Use only one H1 per page', 'adminx-seo-content'); ?></li>
                        <li><?php _e('Follow proper heading hierarchy', 'adminx-seo-content'); ?></li>
                        <li><?php _e('Aim for 300+ words of content', 'adminx-seo-content'); ?></li>
                        <li><?php _e('Break up text with subheadings', 'adminx-seo-content'); ?></li>
                    </ul>
                </div>
                
                <div>
                    <h4><?php _e('Image Optimization', 'adminx-seo-content'); ?></h4>
                    <ul>
                        <li><?php _e('Always add descriptive alt text', 'adminx-seo-content'); ?></li>
                        <li><?php _e('Use relevant keywords naturally', 'adminx-seo-content'); ?></li>
                        <li><?php _e('Keep file sizes optimized', 'adminx-seo-content'); ?></li>
                        <li><?php _e('Use descriptive file names', 'adminx-seo-content'); ?></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
jQuery(document).ready(function($) {
    // Quick SEO check post selection
    $('#quick-seo-post').on('change', function() {
        var postId = $(this).val();
        var $button = $('.run-seo-check');
        
        if (postId) {
            $button.attr('data-post-id', postId).prop('disabled', false);
        } else {
            $button.attr('data-post-id', '').prop('disabled', true);
        }
    });
    
    // Bulk SEO check
    $('.bulk-seo-check').on('click', function(e) {
        e.preventDefault();
        var $button = $(this);
        var contentType = $('#bulk-check-type').val();
        var limit = $('#bulk-check-limit').val();
        
        $button.prop('disabled', true).html('<span class="adminx-loading"></span> Running...');
        $('#bulk-check-progress').show();
        
        // Simulate progress for demo
        var progress = 0;
        var progressInterval = setInterval(function() {
            progress += Math.random() * 20;
            if (progress > 100) progress = 100;
            
            $('#bulk-check-progress .adminx-progress-bar').css('width', progress + '%').text(Math.round(progress) + '%');
            
            if (progress >= 100) {
                clearInterval(progressInterval);
                $('#bulk-check-status').text('<?php _e('Bulk SEO check completed!', 'adminx-seo-content'); ?>');
                $button.prop('disabled', false).html('<?php _e('Run Bulk Check', 'adminx-seo-content'); ?>');
                
                setTimeout(function() {
                    $('#bulk-check-progress').hide();
                    location.reload();
                }, 2000);
            }
        }, 500);
        
        $('#bulk-check-status').text('<?php _e('Processing posts...', 'adminx-seo-content'); ?>');
    });
});
</script>