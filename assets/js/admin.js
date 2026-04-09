/**
 * SkyView SEO Admin JavaScript
 */

(function($) {
    'use strict';

    // Document ready
    $(document).ready(function() {
        // Initialize meta box functionality
        initMetaBox();
        
        // Initialize dashboard functionality
        initDashboard();
    });

    /**
     * Initialize meta box functionality
     */
    function initMetaBox() {
        // Tab switching
        $('.skyview-seo-meta-box-tab').on('click', function() {
            const tab = $(this).data('tab');
            
            // Update active tab
            $('.skyview-seo-meta-box-tab').removeClass('active');
            $(this).addClass('active');
            
            // Show corresponding panel
            $('.skyview-seo-meta-box-panel').removeClass('active');
            $(`.skyview-seo-meta-box-panel[data-panel="${tab}"]`).addClass('active');
        });
        
        // Analyze button
        $('#skyview-seo-analyze-button').on('click', function() {
            const postId = $(this).data('post-id');
            
            // Show loading
            $('#skyview-seo-results').hide();
            $('#skyview-seo-loading').show();
            
            // Run analysis
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'skyview_seo_analyze',
                    post_id: postId,
                    nonce: skyviewSEO.nonce
                },
                success: function(response) {
                    // Hide loading
                    $('#skyview-seo-loading').hide();
                    
                    if (response.success) {
                        // Reload page to show updated analysis
                        location.reload();
                    } else {
                        // Show error
                        $('#skyview-seo-results').html(`
                            <div class="skyview-seo-meta-box-notice error">
                                <p>${response.data.message || 'An error occurred during analysis.'}</p>
                            </div>
                        `).show();
                    }
                },
                error: function() {
                    // Hide loading
                    $('#skyview-seo-loading').hide();
                    
                    // Show error
                    $('#skyview-seo-results').html(`
                        <div class="skyview-seo-meta-box-notice error">
                            <p>${skyviewSEO.i18n.error}</p>
                        </div>
                    `).show();
                }
            });
        });
    }

    /**
     * Initialize dashboard functionality
     */
    function initDashboard() {
        const $refreshButton = $('#skyview-seo-refresh');
        const $exportButton = $('#skyview-seo-export');
        const $searchInput = $('#skyview-seo-page-search');
        const $itemsPerPage = $('#skyview-seo-items-per-page');

        // Search functionality
        if ($searchInput.length) {
            $searchInput.on('keyup', function() {
                const searchValue = $(this).val().toLowerCase();
                const currentUrl = window.location.href.split('?')[0];
                
                if (searchValue.length > 2) {
                    // Filter table rows
                    $('#skyview-seo-pages-tbody tr').each(function() {
                        const title = $(this).find('.skyview-seo-td-title').text().toLowerCase();
                        const type = $(this).find('.skyview-seo-td-type').text().toLowerCase();
                        
                        if (title.indexOf(searchValue) > -1 || type.indexOf(searchValue) > -1) {
                            $(this).show();
                        } else {
                            $(this).hide();
                        }
                    });
                    
                    // Hide pagination when searching
                    $('.skyview-seo-pagination').hide();
                } else if (searchValue.length === 0) {
                    // Reset to normal pagination view
                    window.location.href = currentUrl + '?page=skyview-seo&paged=1';
                }
            });
        }
        
        // Items per page change handler
        if ($itemsPerPage.length) {
            $itemsPerPage.on('change', function() {
                const perPage = $(this).val();
                const currentUrl = window.location.href.split('?')[0];
                window.location.href = currentUrl + '?page=skyview-seo&paged=1&per_page=' + perPage;
            });
        }

        // Refresh analysis button
        if ($refreshButton.length) {
            $refreshButton.on('click', function() {
                const $button = $(this);
            
                // Disable button and show loading
                $button.prop('disabled', true).find('.dashicons').addClass('spin');
            
                // Run analysis
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'skyview_seo_refresh',
                        nonce: skyviewSEO.nonce
                    },
                    success: function(response) {
                        // Enable button and hide loading
                        $button.prop('disabled', false).find('.dashicons').removeClass('spin');
                        
                        if (response.success) {
                            // Reload page to show updated analysis
                            location.reload();
                        } else {
                            // Show error
                            alert(response.data.message || skyviewSEO.i18n.error);
                        }
                    },
                    error: function() {
                        // Enable button and hide loading
                        $button.prop('disabled', false).find('.dashicons').removeClass('spin');
                        
                        // Show error
                        alert(skyviewSEO.i18n.error);
                    }
                });
            });
        }
        
        // Export report button
        if ($exportButton.length) {
            $exportButton.on('click', function() {
            // Create form for POST request
            const form = $('<form>', {
                method: 'POST',
                action: ajaxurl
            });
            
            // Add fields
            form.append($('<input>', {
                type: 'hidden',
                name: 'action',
                value: 'skyview_seo_export_report'
            }));
            
            form.append($('<input>', {
                type: 'hidden',
                name: 'nonce',
                value: skyviewSEO.nonce
            }));
            
            // Submit form
            form.appendTo('body').submit().remove();
            });
        }
        
        // Tab switching for analyze page
        $('.skyview-seo-tab-link').on('click', function(e) {
            e.preventDefault();
            const tabId = $(this).attr('href');
            
            // Hide all tabs
            $('.skyview-seo-tab-content').removeClass('active');
            $('.skyview-seo-tab-link').removeClass('active');
            
            // Show selected tab
            $(tabId).addClass('active');
            $(this).addClass('active');
        });
        
        // Tooltip functionality
        $('.skyview-seo-tooltip-trigger').hover(
            function() {
                const tooltip = $(this).attr('data-tooltip');
                const $tooltip = $('<div class="skyview-seo-tooltip">' + tooltip + '</div>');
                $tooltip.appendTo('body');
                
                const elementPos = $(this).offset();
                $tooltip.css({
                    'top': (elementPos.top - $tooltip.outerHeight() - 10) + 'px',
                    'left': (elementPos.left - ($tooltip.outerWidth() / 2) + 10) + 'px'
                });
                
                $tooltip.fadeIn(200);
            },
            function() {
                $('.skyview-seo-tooltip').remove();
            }
        );
    }

    /**
     * Add spin animation to dashicons
     */
    $.fn.extend({
        spin: function() {
            return this.each(function() {
                $(this).addClass('spin');
            });
        },
        unspin: function() {
            return this.each(function() {
                $(this).removeClass('spin');
            });
        }
    });

    // Add spin animation CSS
    $('<style>')
        .prop('type', 'text/css')
        .html(`
            @keyframes spin {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
            }
            .spin {
                animation: spin 2s linear infinite;
            }
        `)
        .appendTo('head');

})(jQuery);
