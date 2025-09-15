<?php
/**
 * AdminX SEO & Content - Main Admin Page Template
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Get statistics
$seo_checker = new AdminX_SEO_Health_Checker();
$link_manager = new AdminX_Internal_Link_Manager();
$scheduler = new AdminX_Content_Scheduler();
$auto_tagger = new AdminX_Auto_Tagger();

$seo_stats = $seo_checker->get_overall_stats();
$link_stats = $link_manager->get_link_statistics();
$scheduler_stats = $scheduler->get_scheduler_statistics();
$tagger_stats = $auto_tagger->get_auto_tag_statistics();
?>

<div class="adminx-seo-container">
    <div class="adminx-seo-header">
        <h1><?php _e('AdminX SEO & Content Dashboard', 'adminx-seo-content'); ?></h1>
        <p><?php _e('Comprehensive SEO health checking, internal link management, content scheduling, and auto-tagging for your WordPress site.', 'adminx-seo-content'); ?></p>
    </div>

    <!-- Statistics Overview -->
    <div class="adminx-stats-grid">
        <div class="adminx-stat-box">
            <span class="adminx-stat-number"><?php echo count($seo_stats); ?></span>
            <span class="adminx-stat-label"><?php _e('SEO Checks Performed', 'adminx-seo-content'); ?></span>
        </div>
        <div class="adminx-stat-box">
            <span class="adminx-stat-number"><?php echo $link_stats['posts_with_links'] ?? 0; ?></span>
            <span class="adminx-stat-label"><?php _e('Posts with Internal Links', 'adminx-seo-content'); ?></span>
        </div>
        <div class="adminx-stat-box">
            <span class="adminx-stat-number"><?php echo $scheduler_stats['pending_actions'] ?? 0; ?></span>
            <span class="adminx-stat-label"><?php _e('Scheduled Actions', 'adminx-seo-content'); ?></span>
        </div>
        <div class="adminx-stat-box">
            <span class="adminx-stat-number"><?php echo $tagger_stats['total_rules'] ?? 0; ?></span>
            <span class="adminx-stat-label"><?php _e('Auto-Tag Rules', 'adminx-seo-content'); ?></span>
        </div>
    </div>

    <!-- Dashboard Cards -->
    <div class="adminx-dashboard-cards">
        
        <!-- SEO Health Overview -->
        <div class="adminx-card">
            <h3><?php _e('SEO Health Overview', 'adminx-seo-content'); ?></h3>
            <div class="adminx-card-content">
                <?php if (!empty($seo_stats)): ?>
                    <?php
                    $status_counts = array('good' => 0, 'warning' => 0, 'error' => 0);
                    foreach ($seo_stats as $stat) {
                        if (isset($status_counts[$stat->status])) {
                            $status_counts[$stat->status] += $stat->count;
                        }
                    }
                    $total = array_sum($status_counts);
                    ?>
                    
                    <?php foreach ($status_counts as $status => $count): ?>
                        <?php if ($count > 0): ?>
                            <div class="seo-health-status">
                                <span class="seo-status-icon <?php echo $status; ?>"></span>
                                <span class="seo-status-text"><?php echo ucfirst($status); ?>: <?php echo $count; ?></span>
                            </div>
                            <?php if ($total > 0): ?>
                                <div class="adminx-progress">
                                    <div class="adminx-progress-bar <?php echo $status; ?>" style="width: <?php echo ($count / $total) * 100; ?>%">
                                        <?php echo round(($count / $total) * 100, 1); ?>%
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p><?php _e('No SEO checks performed yet.', 'adminx-seo-content'); ?></p>
                <?php endif; ?>
                
                <p>
                    <a href="<?php echo admin_url('admin.php?page=adminx-seo-health'); ?>" class="adminx-button">
                        <?php _e('View SEO Health', 'adminx-seo-content'); ?>
                    </a>
                </p>
            </div>
        </div>

        <!-- Internal Links Overview -->
        <div class="adminx-card">
            <h3><?php _e('Internal Links Overview', 'adminx-seo-content'); ?></h3>
            <div class="adminx-card-content">
                <?php if (!empty($link_stats)): ?>
                    <p>
                        <strong><?php echo $link_stats['link_percentage']; ?>%</strong> 
                        <?php _e('of your posts have internal links', 'adminx-seo-content'); ?>
                    </p>
                    
                    <div class="adminx-progress">
                        <div class="adminx-progress-bar <?php echo $link_stats['link_percentage'] > 70 ? 'good' : ($link_stats['link_percentage'] > 40 ? 'warning' : 'error'); ?>" 
                             style="width: <?php echo $link_stats['link_percentage']; ?>%">
                            <?php echo $link_stats['link_percentage']; ?>%
                        </div>
                    </div>
                    
                    <p>
                        <small>
                            <?php echo $link_stats['posts_with_links']; ?> <?php _e('posts with links', 'adminx-seo-content'); ?> | 
                            <?php echo $link_stats['posts_without_links_count']; ?> <?php _e('posts without links', 'adminx-seo-content'); ?>
                        </small>
                    </p>
                <?php else: ?>
                    <p><?php _e('No internal link data available.', 'adminx-seo-content'); ?></p>
                <?php endif; ?>
                
                <p>
                    <a href="<?php echo admin_url('admin.php?page=adminx-internal-links'); ?>" class="adminx-button">
                        <?php _e('Manage Internal Links', 'adminx-seo-content'); ?>
                    </a>
                </p>
            </div>
        </div>

        <!-- Content Scheduler Overview -->
        <div class="adminx-card">
            <h3><?php _e('Content Scheduler Overview', 'adminx-seo-content'); ?></h3>
            <div class="adminx-card-content">
                <?php if (!empty($scheduler_stats['upcoming_actions'])): ?>
                    <p><strong><?php _e('Upcoming Actions:', 'adminx-seo-content'); ?></strong></p>
                    <div class="scheduled-actions-list">
                        <?php foreach (array_slice($scheduler_stats['upcoming_actions'], 0, 3) as $action): ?>
                            <div class="scheduled-action">
                                <div class="scheduled-action-info">
                                    <div class="scheduled-action-type"><?php echo ucfirst($action->action_type); ?></div>
                                    <div class="scheduled-action-date"><?php echo date('M j, Y H:i', strtotime($action->scheduled_date)); ?></div>
                                    <div class="scheduled-action-title"><?php echo esc_html($action->post_title); ?></div>
                                </div>
                                <div class="scheduled-action-status pending">pending</div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <?php if (count($scheduler_stats['upcoming_actions']) > 3): ?>
                        <p><small><?php printf(__('And %d more...', 'adminx-seo-content'), count($scheduler_stats['upcoming_actions']) - 3); ?></small></p>
                    <?php endif; ?>
                <?php else: ?>
                    <p><?php _e('No upcoming scheduled actions.', 'adminx-seo-content'); ?></p>
                <?php endif; ?>
                
                <p>
                    <a href="<?php echo admin_url('admin.php?page=adminx-content-scheduler'); ?>" class="adminx-button">
                        <?php _e('Manage Scheduler', 'adminx-seo-content'); ?>
                    </a>
                </p>
            </div>
        </div>

        <!-- Auto Tagger Overview -->
        <div class="adminx-card">
            <h3><?php _e('Auto Tagger Overview', 'adminx-seo-content'); ?></h3>
            <div class="adminx-card-content">
                <?php if (!empty($tagger_stats['popular_auto_tags'])): ?>
                    <p><strong><?php _e('Most Used Auto-Generated Tags:', 'adminx-seo-content'); ?></strong></p>
                    <div class="suggested-tags">
                        <?php foreach (array_slice($tagger_stats['popular_auto_tags'], 0, 5, true) as $tag => $count): ?>
                            <span class="suggested-tag"><?php echo esc_html($tag); ?> (<?php echo $count; ?>)</span>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                
                <p>
                    <strong><?php echo $tagger_stats['posts_with_auto_tagging'] ?? 0; ?></strong> 
                    <?php _e('posts have auto-tagging enabled', 'adminx-seo-content'); ?>
                </p>
                
                <?php if (!empty($tagger_stats['recent_actions'])): ?>
                    <p>
                        <small>
                            <?php printf(__('Last auto-tag action: %s', 'adminx-seo-content'), 
                                date('M j, Y H:i', strtotime($tagger_stats['recent_actions'][0]['timestamp']))); ?>
                        </small>
                    </p>
                <?php endif; ?>
                
                <p>
                    <a href="<?php echo admin_url('admin.php?page=adminx-auto-tagging'); ?>" class="adminx-button">
                        <?php _e('Manage Auto Tagging', 'adminx-seo-content'); ?>
                    </a>
                </p>
            </div>
        </div>

    </div>

    <!-- Quick Actions -->
    <div class="adminx-card">
        <h3><?php _e('Quick Actions', 'adminx-seo-content'); ?></h3>
        <div class="adminx-card-content">
            <p>
                <a href="<?php echo admin_url('admin.php?page=adminx-seo-health'); ?>" class="adminx-button">
                    <?php _e('Run SEO Health Check', 'adminx-seo-content'); ?>
                </a>
                
                <a href="<?php echo admin_url('admin.php?page=adminx-internal-links'); ?>" class="adminx-button secondary">
                    <?php _e('Generate Link Suggestions', 'adminx-seo-content'); ?>
                </a>
                
                <a href="<?php echo admin_url('admin.php?page=adminx-content-scheduler'); ?>" class="adminx-button secondary">
                    <?php _e('Schedule Content', 'adminx-seo-content'); ?>
                </a>
                
                <a href="<?php echo admin_url('admin.php?page=adminx-auto-tagging'); ?>" class="adminx-button secondary">
                    <?php _e('Bulk Retag Posts', 'adminx-seo-content'); ?>
                </a>
            </p>
        </div>
    </div>

    <!-- Recent Activity -->
    <?php if (!empty($tagger_stats['recent_actions'])): ?>
    <div class="adminx-card">
        <h3><?php _e('Recent Auto-Tagging Activity', 'adminx-seo-content'); ?></h3>
        <div class="adminx-card-content">
            <?php foreach (array_slice($tagger_stats['recent_actions'], 0, 5) as $action): ?>
                <div style="margin-bottom: 10px; padding: 10px; background: #f9f9f9; border-radius: 4px;">
                    <strong><?php echo get_the_title($action['post_id']); ?></strong>
                    <br>
                    <small>
                        <?php _e('Tags added:', 'adminx-seo-content'); ?> 
                        <?php echo implode(', ', $action['tags']); ?>
                        <br>
                        <?php echo date('M j, Y H:i', strtotime($action['timestamp'])); ?>
                    </small>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

</div>

<script>
jQuery(document).ready(function($) {
    // Auto-refresh statistics every 30 seconds
    setInterval(function() {
        // Only refresh if user is still on the page
        if (document.hasFocus()) {
            location.reload();
        }
    }, 30000);
});
</script>