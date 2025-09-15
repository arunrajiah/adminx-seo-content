=== AdminX SEO & Content ===
Contributors: adminx
Tags: seo, content, internal links, scheduler, auto-tagging, meta tags, optimization
Requires at least: 5.0
Tested up to: 6.3
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Comprehensive SEO health checker, auto internal link manager, content scheduler, and auto-tagging system for WordPress administrators.

== Description ==

AdminX SEO & Content is a powerful WordPress plugin designed specifically for administrators who want to optimize their site's SEO performance and streamline content management. This comprehensive tool combines four essential features into one unified solution.

= Key Features =

**SEO Health Checker**
* Comprehensive SEO analysis for meta tags, headings, and content structure
* Alt text validation for images
* Broken internal links detection
* Content length and keyword density analysis
* Automated SEO scoring and recommendations
* Bulk SEO health checks for multiple posts

**Auto Internal Link Manager**
* Intelligent keyword-based link suggestions
* Automatic internal linking based on content analysis
* Link opportunity identification
* Broken link detection and reporting
* Internal link statistics and analytics
* Customizable linking rules and preferences

**Content Scheduler**
* Schedule posts to publish/unpublish at specific times
* Content expiry management with auto-unpublish options
* Bulk scheduling operations
* Visual scheduling calendar
* Email notifications for scheduled actions
* Custom post status management

**Auto Tagging System**
* Keyword-based automatic tag generation
* Content analysis for relevant tag suggestions
* Customizable tagging rules and logic
* Bulk retagging for existing content
* Tag performance analytics
* Manual tag suggestion override

= Why Choose AdminX SEO & Content? =

* **All-in-One Solution**: Four powerful tools in one plugin
* **Administrator Focused**: Designed specifically for site administrators
* **Local Processing**: All features work locally without external API dependencies
* **Performance Optimized**: Lightweight and efficient code
* **User Friendly**: Intuitive interface with comprehensive documentation
* **Extensible**: Hooks and filters for developers

= Perfect For =

* WordPress administrators managing multiple sites
* Content managers looking to improve SEO performance
* Bloggers wanting to automate internal linking
* Publishers needing advanced content scheduling
* Anyone seeking comprehensive SEO optimization tools

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/adminx-seo-content` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Navigate to AdminX SEO in your WordPress admin menu to configure the plugin.
4. Configure your SEO health check preferences, internal linking rules, scheduling options, and auto-tagging settings.

= Minimum Requirements =

* WordPress 5.0 or higher
* PHP 7.4 or higher
* MySQL 5.6 or higher
* 64MB memory limit (128MB recommended)

== Frequently Asked Questions ==

= Does this plugin require any external services? =

No, AdminX SEO & Content works entirely locally on your WordPress installation. All SEO analysis, link management, scheduling, and tagging operations are performed on your server without requiring external API calls.

= Will this plugin slow down my website? =

AdminX SEO & Content is designed to be lightweight and efficient. Most operations run in the background or only when specifically triggered by administrators. The plugin includes caching mechanisms to minimize performance impact.

= Can I customize the SEO health check criteria? =

Yes, the plugin includes various settings to customize SEO health check parameters, including meta tag length requirements, content analysis rules, and scoring criteria.

= How does the auto internal linking work? =

The auto internal link manager analyzes your content and existing posts to identify relevant linking opportunities. It uses keyword matching, content similarity, and customizable rules to suggest and automatically insert internal links.

= Can I schedule content to unpublish automatically? =

Yes, the content scheduler allows you to set expiry dates for posts and pages, with options to automatically unpublish content or simply mark it as expired while keeping it visible.

= How accurate is the auto-tagging feature? =

The auto-tagging system uses advanced content analysis to identify relevant keywords and phrases. You can customize the tagging rules and review suggestions before they're applied to ensure accuracy.

= Is this plugin compatible with other SEO plugins? =

AdminX SEO & Content is designed to complement existing SEO plugins like Yoast SEO or RankMath. It focuses on content management and internal optimization rather than conflicting with meta tag management.

= Can I export SEO health check results? =

Yes, the plugin includes export functionality for SEO health check results, internal link reports, and scheduling data in CSV format.

== Screenshots ==

1. Main dashboard showing SEO health overview, internal links statistics, scheduled content, and auto-tagging activity
2. SEO Health Checker interface with comprehensive analysis results and recommendations
3. Internal Link Manager showing link suggestions and automatic linking options
4. Content Scheduler with calendar view and bulk scheduling operations
5. Auto Tagging system with rule management and bulk retagging tools
6. Detailed SEO health check results for individual posts
7. Internal link statistics and broken link detection
8. Scheduled content overview with upcoming actions

== Changelog ==

= 1.0.0 =
* Initial release
* SEO Health Checker with comprehensive analysis
* Auto Internal Link Manager with intelligent suggestions
* Content Scheduler with flexible timing options
* Auto Tagging system with customizable rules
* Dashboard with unified statistics and quick actions
* Bulk operations for all major features
* Export functionality for reports and data
* WordPress 6.3 compatibility
* PHP 8.1 compatibility

== Upgrade Notice ==

= 1.0.0 =
Initial release of AdminX SEO & Content. Install to get comprehensive SEO optimization and content management tools for your WordPress site.

== Support ==

For support, documentation, and feature requests, please visit:
* Plugin Documentation: [AdminX SEO & Content Docs](https://adminx.dev/docs/seo-content)
* Support Forum: [WordPress.org Support](https://wordpress.org/support/plugin/adminx-seo-content)
* GitHub Repository: [AdminX Plugins](https://github.com/arunrajiah/adminx-plugins)

== Privacy Policy ==

AdminX SEO & Content does not collect, store, or transmit any personal data outside of your WordPress installation. All analysis and processing is performed locally on your server. The plugin may store SEO analysis results, scheduling data, and tagging information in your WordPress database for functionality purposes.

== Credits ==

AdminX SEO & Content is developed and maintained by the AdminX team. Special thanks to the WordPress community for their continuous support and feedback.

== Technical Details ==

= Database Tables =
The plugin creates the following database tables:
* `wp_adminx_seo_health` - Stores SEO health check results
* `wp_adminx_content_scheduler` - Manages scheduled content actions

= Hooks and Filters =
The plugin provides numerous hooks and filters for developers:
* `adminx_seo_check_complete` - Fired after SEO health check completion
* `adminx_internal_link_added` - Triggered when internal link is automatically added
* `adminx_content_scheduled` - Called when content is scheduled
* `adminx_auto_tag_applied` - Fired when auto-tags are applied to content

= Cron Jobs =
The plugin schedules the following cron jobs:
* `adminx_seo_health_check` - Daily SEO health monitoring
* `adminx_content_scheduler_check` - Hourly scheduled content processing

= Performance Considerations =
* Uses WordPress transients for caching frequently accessed data
* Implements batch processing for bulk operations
* Optimized database queries with proper indexing
* Lazy loading for admin interface components