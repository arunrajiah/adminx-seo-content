# AdminX SEO & Content - User Guide

## Table of Contents
1. [Installation](#installation)
2. [Getting Started](#getting-started)
3. [SEO Health Checker](#seo-health-checker)
4. [Internal Link Manager](#internal-link-manager)
5. [Content Scheduler](#content-scheduler)
6. [Auto Tagging System](#auto-tagging-system)
7. [Dashboard Overview](#dashboard-overview)
8. [Settings & Configuration](#settings--configuration)
9. [Troubleshooting](#troubleshooting)
10. [FAQ](#faq)

## Installation

### Automatic Installation
1. Log in to your WordPress admin dashboard
2. Navigate to **Plugins > Add New**
3. Search for "AdminX SEO & Content"
4. Click **Install Now** and then **Activate**

### Manual Installation
1. Download the plugin zip file
2. Upload to `/wp-content/plugins/adminx-seo-content/`
3. Activate the plugin through the **Plugins** menu in WordPress

### System Requirements
- WordPress 5.0 or higher
- PHP 7.4 or higher
- MySQL 5.6 or higher
- 64MB memory limit (128MB recommended)

## Getting Started

After activation, you'll find **AdminX SEO** in your WordPress admin menu. The plugin consists of four main modules:

1. **SEO Health Checker** - Analyzes your content for SEO optimization
2. **Internal Link Manager** - Manages and suggests internal links
3. **Content Scheduler** - Schedules content publishing and expiry
4. **Auto Tagging** - Automatically tags content based on keywords

### Initial Setup
1. Navigate to **AdminX SEO** in your admin menu
2. Review the dashboard overview
3. Configure each module according to your needs
4. Run your first SEO health check

## SEO Health Checker

The SEO Health Checker analyzes your content for various SEO factors and provides actionable recommendations.

### Features
- **Meta Title Analysis**: Checks title length (30-60 characters optimal)
- **Meta Description Validation**: Ensures descriptions are 120-160 characters
- **Heading Structure**: Validates H1-H6 hierarchy
- **Image Alt Text**: Checks for missing alt attributes
- **Internal Links**: Identifies broken links and opportunities
- **Content Length**: Analyzes word count and readability
- **Keyword Density**: Calculates keyword usage

### How to Use

#### Single Post Check
1. Go to **AdminX SEO > SEO Health**
2. Select a post from the dropdown
3. Click **Run SEO Check**
4. Review the results and recommendations

#### Bulk Health Check
1. Navigate to the **Bulk SEO Check** section
2. Choose content type (Posts, Pages, or Both)
3. Set the number of posts to check
4. Click **Run Bulk Check**
5. Monitor progress and review results

#### Understanding Results
- **Green (Good)**: No issues found
- **Yellow (Warning)**: Minor issues that should be addressed
- **Red (Error)**: Critical issues requiring immediate attention
- **Blue (Info)**: Informational items for optimization

### Best Practices
- Run SEO checks after major content updates
- Address errors before warnings
- Focus on meta tags and heading structure first
- Regularly check for broken internal links

## Internal Link Manager

The Internal Link Manager helps you build a strong internal linking structure by suggesting relevant links and automating the linking process.

### Features
- **Smart Link Suggestions**: AI-powered link recommendations
- **Automatic Linking**: Auto-insert links based on keywords
- **Link Statistics**: Track internal linking performance
- **Broken Link Detection**: Identify and fix broken internal links
- **Link Opportunities**: Discover missed linking chances

### How to Use

#### Getting Link Suggestions
1. Go to **AdminX SEO > Internal Links**
2. Select a post or enter content
3. Click **Get Suggestions**
4. Review suggested links and their relevance scores
5. Click **Insert** to add links manually

#### Automatic Linking
1. Set your maximum links per post (recommended: 3-5)
2. Enable auto-linking for specific post types
3. Configure keyword matching preferences
4. The system will automatically add relevant links when posts are saved

#### Managing Link Rules
1. Navigate to the **Link Rules** section
2. Add keywords and target URLs
3. Set priority levels for different link types
4. Enable or disable rules as needed

### Link Statistics
- **Posts with Links**: Percentage of content with internal links
- **Link Distribution**: How links are spread across your site
- **Top Linked Posts**: Most frequently linked content
- **Broken Links**: Links that need fixing

## Content Scheduler

The Content Scheduler allows you to automate content publishing, unpublishing, and expiry management.

### Features
- **Scheduled Publishing**: Automatically publish draft content
- **Scheduled Unpublishing**: Remove content at specified times
- **Content Expiry**: Mark content as expired with optional auto-unpublish
- **Bulk Scheduling**: Schedule multiple posts at once
- **Custom Post Status**: Track scheduled and expired content

### How to Use

#### Scheduling Individual Posts
1. Edit any post or page
2. Find the **Content Scheduler** meta box in the sidebar
3. Set your desired action (Publish, Unpublish, Expire)
4. Choose the date and time
5. Save the post

#### Bulk Scheduling
1. Go to **AdminX SEO > Scheduler**
2. Select multiple posts using checkboxes
3. Choose the action type
4. Set the schedule date
5. Click **Schedule Selected**

#### Managing Scheduled Content
- View all scheduled actions in the **Scheduled Content** section
- Edit or cancel scheduled actions before they execute
- Monitor execution status and history
- Set up email notifications for scheduled events

### Scheduling Options
- **Publish**: Make draft content live at a specific time
- **Unpublish**: Move published content back to draft
- **Expire**: Mark content as expired (with optional unpublish)
- **Republish**: Restore previously unpublished content

## Auto Tagging System

The Auto Tagging system analyzes your content and automatically applies relevant tags based on customizable rules.

### Features
- **Keyword-Based Rules**: Create rules linking keywords to tags
- **Content Analysis**: AI-powered tag suggestions from content
- **Bulk Retagging**: Apply tags to existing content
- **Tag Performance**: Track which tags are most effective
- **Manual Override**: Review and approve tags before application

### How to Use

#### Setting Up Tag Rules
1. Go to **AdminX SEO > Auto Tagging**
2. Click **Add New Rule**
3. Enter keywords (comma-separated)
4. Specify the tag to apply
5. Choose logic (AND/OR for multiple keywords)
6. Set priority level
7. Save the rule

#### Automatic Tagging
1. Enable auto-tagging for specific post types
2. Tags will be automatically applied when posts are saved
3. Review suggested tags in the post editor
4. Manually approve or reject suggestions

#### Bulk Retagging
1. Navigate to **Bulk Retag** section
2. Select post type and number of posts
3. Choose whether to add or replace existing tags
4. Click **Start Bulk Retag**
5. Monitor progress and review results

### Tag Rule Examples
- **Keywords**: "wordpress, plugin, development" → **Tag**: "WordPress Development"
- **Keywords**: "seo, optimization" → **Tag**: "SEO"
- **Keywords**: "tutorial, guide, how-to" → **Tag**: "Tutorial"

## Dashboard Overview

The main dashboard provides a comprehensive overview of all plugin activities and statistics.

### Dashboard Widgets
- **SEO Health Overview**: Summary of recent SEO checks
- **Internal Links Status**: Linking statistics and opportunities
- **Scheduled Content**: Upcoming scheduled actions
- **Auto Tagging Activity**: Recent tagging actions and performance

### Quick Actions
- Run immediate SEO health checks
- Generate link suggestions for recent posts
- Schedule content for publishing
- Perform bulk retagging operations

### Statistics Tracking
- SEO health trends over time
- Internal linking growth
- Content scheduling efficiency
- Tag application success rates

## Settings & Configuration

### General Settings
- **Enable/Disable Modules**: Turn individual features on or off
- **Performance Settings**: Configure caching and optimization
- **Notification Preferences**: Set up email alerts
- **User Permissions**: Control who can access different features

### SEO Health Settings
- **Check Frequency**: How often to run automatic checks
- **Scoring Criteria**: Customize what constitutes good/bad SEO
- **Meta Tag Preferences**: Set optimal length ranges
- **Content Analysis**: Configure keyword density thresholds

### Internal Link Settings
- **Maximum Auto-Links**: Limit automatic links per post
- **Link Matching**: Configure how keywords are matched
- **Exclusion Rules**: Prevent linking to specific content
- **Cache Duration**: How long to store link suggestions

### Scheduler Settings
- **Default Actions**: Set default scheduling behavior
- **Notification Timing**: When to send scheduling alerts
- **Cleanup Schedule**: How long to keep scheduling history
- **Time Zone**: Configure scheduling time zone

### Auto Tagging Settings
- **Tag Application**: Automatic vs. manual approval
- **Content Analysis**: Sensitivity of tag suggestions
- **Rule Priority**: How conflicting rules are resolved
- **Bulk Limits**: Maximum posts for bulk operations

## Troubleshooting

### Common Issues

#### SEO Checks Not Running
- **Cause**: Insufficient permissions or memory limits
- **Solution**: Check user capabilities and increase PHP memory limit
- **Prevention**: Ensure WordPress cron is functioning properly

#### Internal Links Not Appearing
- **Cause**: Cache not updated or insufficient content
- **Solution**: Clear link cache and ensure posts have adequate content
- **Prevention**: Regularly update link suggestions cache

#### Scheduled Content Not Publishing
- **Cause**: WordPress cron issues or server timezone problems
- **Solution**: Check cron functionality and server time settings
- **Prevention**: Use external cron service for reliability

#### Auto Tags Not Applied
- **Cause**: Rules not matching content or disabled auto-tagging
- **Solution**: Review tag rules and enable auto-tagging for post types
- **Prevention**: Test rules with sample content before applying

### Performance Issues

#### Slow Admin Pages
- **Solution**: Increase PHP memory limit and optimize database
- **Check**: Review number of posts being processed simultaneously
- **Optimize**: Use bulk operations during low-traffic periods

#### High Memory Usage
- **Solution**: Reduce bulk operation limits
- **Check**: Monitor memory usage during large operations
- **Optimize**: Process content in smaller batches

### Getting Help
- Check the **System Status** page for diagnostic information
- Review WordPress error logs for specific error messages
- Contact support through the WordPress.org support forum
- Submit bug reports via GitHub repository

## FAQ

### General Questions

**Q: Does this plugin work with other SEO plugins?**
A: Yes, AdminX SEO & Content is designed to complement existing SEO plugins like Yoast SEO and RankMath. It focuses on content management and internal optimization.

**Q: Will this plugin slow down my website?**
A: No, the plugin is optimized for performance and runs most operations in the background. Frontend impact is minimal.

**Q: Can I use this on multiple sites?**
A: Yes, you can install the plugin on multiple WordPress sites. Each installation works independently.

### SEO Health Checker

**Q: How often should I run SEO health checks?**
A: We recommend running checks after major content updates and at least weekly for active sites.

**Q: What's the difference between warnings and errors?**
A: Errors are critical issues that significantly impact SEO, while warnings are optimization opportunities.

**Q: Can I customize the SEO criteria?**
A: Yes, you can adjust meta tag length requirements and other criteria in the settings.

### Internal Link Manager

**Q: How does the automatic linking work?**
A: The system analyzes your content and existing posts to identify relevant linking opportunities based on keywords and content similarity.

**Q: Can I prevent certain posts from being linked?**
A: Yes, you can exclude specific posts or post types from automatic linking in the settings.

**Q: How many internal links should I have per post?**
A: We recommend 3-5 internal links per post for optimal SEO benefit without over-optimization.

### Content Scheduler

**Q: What happens if my server goes down during a scheduled action?**
A: Scheduled actions will execute when your server comes back online, as they're processed by WordPress cron.

**Q: Can I schedule recurring content?**
A: Currently, the plugin supports one-time scheduling. Recurring schedules are planned for future versions.

**Q: How far in advance can I schedule content?**
A: There's no limit to how far in advance you can schedule content.

### Auto Tagging

**Q: How accurate is the automatic tagging?**
A: Accuracy depends on content quality and rule configuration. The system learns from your corrections over time.

**Q: Can I review tags before they're applied?**
A: Yes, you can enable manual approval mode to review all suggested tags before application.

**Q: Will auto-tagging replace my existing tags?**
A: No, auto-tagging adds to existing tags unless you specifically choose to replace them.

---

For additional support and updates, visit the [AdminX SEO & Content documentation](https://adminx.dev/docs/seo-content) or the [WordPress.org support forum](https://wordpress.org/support/plugin/adminx-seo-content).