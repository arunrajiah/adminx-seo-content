/**
 * AdminX SEO & Content - Admin JavaScript
 */

(function($) {
    'use strict';

    // Initialize when document is ready
    $(document).ready(function() {
        AdminXSEO.init();
    });

    // Main AdminX SEO object
    window.AdminXSEO = {
        
        // Initialize all functionality
        init: function() {
            this.initSEOHealthChecker();
            this.initInternalLinkManager();
            this.initContentScheduler();
            this.initAutoTagger();
            this.initTooltips();
        },

        // SEO Health Checker functionality
        initSEOHealthChecker: function() {
            // Run SEO check button
            $(document).on('click', '.run-seo-check', function(e) {
                e.preventDefault();
                var $button = $(this);
                var postId = $button.data('post-id');
                
                AdminXSEO.runSEOCheck(postId, $button);
            });

            // Auto-run SEO check on content change (debounced)
            var seoCheckTimeout;
            $(document).on('input', '#content, #title', function() {
                clearTimeout(seoCheckTimeout);
                seoCheckTimeout = setTimeout(function() {
                    var postId = $('#post_ID').val();
                    if (postId) {
                        AdminXSEO.runSEOCheck(postId);
                    }
                }, 2000);
            });
        },

        // Run SEO health check
        runSEOCheck: function(postId, $button) {
            if ($button) {
                $button.prop('disabled', true).html('<span class="adminx-loading"></span> Checking...');
            }

            $.ajax({
                url: adminx_seo_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'adminx_run_seo_check',
                    post_id: postId,
                    nonce: adminx_seo_ajax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        AdminXSEO.displaySEOResults(response.data);
                    } else {
                        AdminXSEO.showNotice('Error running SEO check: ' + response.data, 'error');
                    }
                },
                error: function() {
                    AdminXSEO.showNotice('Failed to run SEO check. Please try again.', 'error');
                },
                complete: function() {
                    if ($button) {
                        $button.prop('disabled', false).html('Run SEO Check');
                    }
                }
            });
        },

        // Display SEO check results
        displaySEOResults: function(results) {
            var $container = $('.seo-health-results');
            if ($container.length === 0) {
                return;
            }

            $container.empty();

            $.each(results, function(checkType, result) {
                var $item = $('<div class="seo-check-item ' + result.status + '">');
                $item.append('<h4>' + AdminXSEO.formatCheckType(checkType) + '</h4>');
                $item.append('<p>' + result.message + '</p>');
                
                if (result.value) {
                    $item.append('<small>Current: ' + result.value + '</small>');
                }
                
                $container.append($item);
            });
        },

        // Format check type for display
        formatCheckType: function(type) {
            return type.replace(/_/g, ' ').replace(/\b\w/g, function(l) {
                return l.toUpperCase();
            });
        },

        // Internal Link Manager functionality
        initInternalLinkManager: function() {
            // Get link suggestions
            $(document).on('click', '.get-link-suggestions', function(e) {
                e.preventDefault();
                var $button = $(this);
                var postId = $button.data('post-id');
                var content = $('#content').val() || '';
                
                AdminXSEO.getLinkSuggestions(postId, content, $button);
            });

            // Auto-link content
            $(document).on('click', '.auto-link-content', function(e) {
                e.preventDefault();
                var $button = $(this);
                var postId = $button.data('post-id');
                var content = $('#content').val() || '';
                var maxLinks = $('#max-auto-links').val() || 3;
                
                AdminXSEO.autoLinkContent(postId, content, maxLinks, $button);
            });

            // Insert suggested link
            $(document).on('click', '.insert-link', function(e) {
                e.preventDefault();
                var keyword = $(this).data('keyword');
                var url = $(this).data('url');
                var title = $(this).data('title');
                
                AdminXSEO.insertLink(keyword, url, title);
            });
        },

        // Get link suggestions
        getLinkSuggestions: function(postId, content, $button) {
            if ($button) {
                $button.prop('disabled', true).html('<span class="adminx-loading"></span> Getting suggestions...');
            }

            $.ajax({
                url: adminx_seo_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'adminx_get_link_suggestions',
                    post_id: postId,
                    content: content,
                    nonce: adminx_seo_ajax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        AdminXSEO.displayLinkSuggestions(response.data);
                    } else {
                        AdminXSEO.showNotice('Error getting link suggestions: ' + response.data, 'error');
                    }
                },
                error: function() {
                    AdminXSEO.showNotice('Failed to get link suggestions. Please try again.', 'error');
                },
                complete: function() {
                    if ($button) {
                        $button.prop('disabled', false).html('Get Suggestions');
                    }
                }
            });
        },

        // Display link suggestions
        displayLinkSuggestions: function(suggestions) {
            var $container = $('.internal-links-suggestions');
            if ($container.length === 0) {
                return;
            }

            $container.empty();

            if (suggestions.length === 0) {
                $container.append('<p>No link suggestions found.</p>');
                return;
            }

            $.each(suggestions, function(index, suggestion) {
                var $item = $('<div class="link-suggestion">');
                var $info = $('<div class="link-suggestion-info">');
                $info.append('<div class="link-suggestion-keyword">' + suggestion.keyword + '</div>');
                $info.append('<div class="link-suggestion-title">' + suggestion.title + '</div>');
                
                var $actions = $('<div class="link-suggestion-actions">');
                $actions.append('<button class="adminx-button small insert-link" data-keyword="' + suggestion.keyword + '" data-url="' + suggestion.url + '" data-title="' + suggestion.title + '">Insert</button>');
                
                $item.append($info).append($actions);
                $container.append($item);
            });
        },

        // Auto-link content
        autoLinkContent: function(postId, content, maxLinks, $button) {
            if ($button) {
                $button.prop('disabled', true).html('<span class="adminx-loading"></span> Adding links...');
            }

            $.ajax({
                url: adminx_seo_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'adminx_auto_link_content',
                    post_id: postId,
                    content: content,
                    max_links: maxLinks,
                    nonce: adminx_seo_ajax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        $('#content').val(response.data.content);
                        AdminXSEO.showNotice('Added ' + response.data.links_added + ' internal links.', 'success');
                    } else {
                        AdminXSEO.showNotice('Error auto-linking content: ' + response.data, 'error');
                    }
                },
                error: function() {
                    AdminXSEO.showNotice('Failed to auto-link content. Please try again.', 'error');
                },
                complete: function() {
                    if ($button) {
                        $button.prop('disabled', false).html('Auto-Link Content');
                    }
                }
            });
        },

        // Insert link into content
        insertLink: function(keyword, url, title) {
            var content = $('#content').val();
            var linkHtml = '<a href="' + url + '" title="' + title + '">' + keyword + '</a>';
            var regex = new RegExp('\\b' + keyword.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + '\\b', 'i');
            
            if (regex.test(content)) {
                var newContent = content.replace(regex, linkHtml);
                $('#content').val(newContent);
                AdminXSEO.showNotice('Link inserted successfully.', 'success');
            } else {
                AdminXSEO.showNotice('Keyword not found in content.', 'warning');
            }
        },

        // Content Scheduler functionality
        initContentScheduler: function() {
            // Schedule content action
            $(document).on('click', '.schedule-content', function(e) {
                e.preventDefault();
                var $button = $(this);
                var postId = $button.data('post-id');
                var actionType = $('#schedule-action-type').val();
                var scheduledDate = $('#schedule-date').val();
                
                if (!actionType || !scheduledDate) {
                    AdminXSEO.showNotice('Please select an action type and date.', 'warning');
                    return;
                }
                
                AdminXSEO.scheduleContent(postId, actionType, scheduledDate, $button);
            });

            // Load scheduled content
            $(document).on('click', '.load-scheduled-content', function(e) {
                e.preventDefault();
                AdminXSEO.loadScheduledContent();
            });

            // Date/time picker initialization
            if ($('input[type="datetime-local"]').length) {
                // Set minimum date to current date
                var now = new Date();
                var minDate = now.toISOString().slice(0, 16);
                $('input[type="datetime-local"]').attr('min', minDate);
            }
        },

        // Schedule content
        scheduleContent: function(postId, actionType, scheduledDate, $button) {
            if ($button) {
                $button.prop('disabled', true).html('<span class="adminx-loading"></span> Scheduling...');
            }

            $.ajax({
                url: adminx_seo_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'adminx_schedule_content',
                    post_id: postId,
                    action_type: actionType,
                    scheduled_date: scheduledDate,
                    nonce: adminx_seo_ajax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        AdminXSEO.showNotice(response.data.message, 'success');
                        AdminXSEO.loadScheduledContent();
                    } else {
                        AdminXSEO.showNotice('Error scheduling content: ' + response.data, 'error');
                    }
                },
                error: function() {
                    AdminXSEO.showNotice('Failed to schedule content. Please try again.', 'error');
                },
                complete: function() {
                    if ($button) {
                        $button.prop('disabled', false).html('Schedule');
                    }
                }
            });
        },

        // Load scheduled content
        loadScheduledContent: function() {
            $.ajax({
                url: adminx_seo_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'adminx_get_scheduled_content',
                    nonce: adminx_seo_ajax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        AdminXSEO.displayScheduledContent(response.data);
                    }
                }
            });
        },

        // Display scheduled content
        displayScheduledContent: function(scheduledContent) {
            var $container = $('.scheduled-actions-list');
            if ($container.length === 0) {
                return;
            }

            $container.empty();

            if (scheduledContent.length === 0) {
                $container.append('<p>No scheduled actions found.</p>');
                return;
            }

            $.each(scheduledContent, function(index, item) {
                var $action = $('<div class="scheduled-action">');
                var $info = $('<div class="scheduled-action-info">');
                $info.append('<div class="scheduled-action-type">' + item.action_type + '</div>');
                $info.append('<div class="scheduled-action-date">' + item.scheduled_date + '</div>');
                $info.append('<div class="scheduled-action-title">' + item.post_title + '</div>');
                
                var $status = $('<div class="scheduled-action-status ' + item.status + '">' + item.status + '</div>');
                
                $action.append($info).append($status);
                $container.append($action);
            });
        },

        // Auto Tagger functionality
        initAutoTagger: function() {
            // Add tag rule
            $(document).on('click', '.add-tag-rule', function(e) {
                e.preventDefault();
                var keywords = $('#tag-rule-keywords').val();
                var tag = $('#tag-rule-tag').val();
                var logic = $('#tag-rule-logic').val();
                var priority = $('#tag-rule-priority').val();
                
                if (!keywords || !tag) {
                    AdminXSEO.showNotice('Please enter keywords and tag name.', 'warning');
                    return;
                }
                
                AdminXSEO.addTagRule(keywords, tag, logic, priority);
            });

            // Delete tag rule
            $(document).on('click', '.delete-tag-rule', function(e) {
                e.preventDefault();
                var ruleId = $(this).data('rule-id');
                AdminXSEO.deleteTagRule(ruleId);
            });

            // Bulk retag
            $(document).on('click', '.bulk-retag', function(e) {
                e.preventDefault();
                var $button = $(this);
                var postType = $('#bulk-retag-post-type').val();
                var limit = $('#bulk-retag-limit').val();
                
                AdminXSEO.bulkRetag(postType, limit, $button);
            });

            // Toggle suggested tags
            $(document).on('click', '.suggested-tag', function() {
                $(this).toggleClass('selected');
            });

            // Apply suggested tags
            $(document).on('click', '.apply-suggested-tags', function(e) {
                e.preventDefault();
                var selectedTags = [];
                $('.suggested-tag.selected').each(function() {
                    selectedTags.push($(this).text());
                });
                
                if (selectedTags.length > 0) {
                    AdminXSEO.applySuggestedTags(selectedTags);
                }
            });
        },

        // Add tag rule
        addTagRule: function(keywords, tag, logic, priority) {
            $.ajax({
                url: adminx_seo_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'adminx_add_tag_rule',
                    keywords: keywords,
                    tag: tag,
                    logic: logic,
                    priority: priority,
                    nonce: adminx_seo_ajax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        AdminXSEO.showNotice(response.data.message, 'success');
                        location.reload(); // Reload to show new rule
                    } else {
                        AdminXSEO.showNotice('Error adding tag rule: ' + response.data, 'error');
                    }
                },
                error: function() {
                    AdminXSEO.showNotice('Failed to add tag rule. Please try again.', 'error');
                }
            });
        },

        // Delete tag rule
        deleteTagRule: function(ruleId) {
            if (!confirm('Are you sure you want to delete this tag rule?')) {
                return;
            }

            $.ajax({
                url: adminx_seo_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'adminx_delete_tag_rule',
                    rule_id: ruleId,
                    nonce: adminx_seo_ajax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        AdminXSEO.showNotice(response.data.message, 'success');
                        location.reload(); // Reload to remove rule
                    } else {
                        AdminXSEO.showNotice('Error deleting tag rule: ' + response.data, 'error');
                    }
                },
                error: function() {
                    AdminXSEO.showNotice('Failed to delete tag rule. Please try again.', 'error');
                }
            });
        },

        // Bulk retag
        bulkRetag: function(postType, limit, $button) {
            if ($button) {
                $button.prop('disabled', true).html('<span class="adminx-loading"></span> Processing...');
            }

            $.ajax({
                url: adminx_seo_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'adminx_bulk_retag',
                    post_type: postType,
                    limit: limit,
                    nonce: adminx_seo_ajax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        AdminXSEO.showNotice(response.data.message, 'success');
                    } else {
                        AdminXSEO.showNotice('Error during bulk retagging: ' + response.data, 'error');
                    }
                },
                error: function() {
                    AdminXSEO.showNotice('Failed to perform bulk retagging. Please try again.', 'error');
                },
                complete: function() {
                    if ($button) {
                        $button.prop('disabled', false).html('Bulk Retag');
                    }
                }
            });
        },

        // Apply suggested tags
        applySuggestedTags: function(tags) {
            var currentTags = $('#new-tag-post_tag').val();
            var allTags = currentTags ? currentTags + ',' + tags.join(',') : tags.join(',');
            $('#new-tag-post_tag').val(allTags);
            AdminXSEO.showNotice('Tags added to post.', 'success');
        },

        // Initialize tooltips
        initTooltips: function() {
            // Simple tooltip implementation
            $(document).on('mouseenter', '.adminx-tooltip', function() {
                var $this = $(this);
                var tooltip = $this.attr('data-tooltip');
                if (tooltip) {
                    $this.attr('title', tooltip);
                }
            });
        },

        // Show admin notice
        showNotice: function(message, type) {
            type = type || 'info';
            var $notice = $('<div class="adminx-notice ' + type + '"><p>' + message + '</p></div>');
            
            // Remove existing notices
            $('.adminx-notice').remove();
            
            // Add new notice
            if ($('.adminx-seo-container').length) {
                $('.adminx-seo-container').prepend($notice);
            } else {
                $('#wpbody-content .wrap').prepend($notice);
            }
            
            // Auto-remove after 5 seconds
            setTimeout(function() {
                $notice.fadeOut(function() {
                    $notice.remove();
                });
            }, 5000);
        },

        // Utility function to format dates
        formatDate: function(dateString) {
            var date = new Date(dateString);
            return date.toLocaleDateString() + ' ' + date.toLocaleTimeString();
        },

        // Utility function to escape HTML
        escapeHtml: function(text) {
            var map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return text.replace(/[&<>"']/g, function(m) { return map[m]; });
        }
    };

})(jQuery);