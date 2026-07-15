/**
 * WP Safelink Setup Wizard JavaScript
 * Version: 5.3.0
 */

(function($) {
    'use strict';

    // Initialize when DOM is ready
    $(document).ready(function() {
        initWizard();
    });

    function initWizard() {
        // Handle license verification
        handleLicenseVerification();

        // Handle toggle switches
        handleToggleSwitches();

        // Handle feature toggles
        handleFeatureToggles();

        // Handle configuration step dynamic fields
        handleConfigurationStep();

        // Handle AJAX step saving
        handleStepSaving();

        // Handle countdown duration toggle
        handleCountdownToggle();

        // Handle copy API functionality
        handleApiCopy();

        // Handle test safelink functionality (features step)
        handleTestSafelink();

        // Animate elements on page load
        animateWizardElements();
    }

    /**
     * Handle license verification
     */
    function handleLicenseVerification() {
        const $licenseInput = $('#license_key');
        const $statusBox = $('#license-status');
        const $activateButton = $('#activate-license');
        const $domainInput = $('#domain');
        let isValidating = false;

        // Handle license input change
        let validateTimer;
        $licenseInput.on('input', function() {
            const value = $(this).val().trim();

            // Clear previous timer
            clearTimeout(validateTimer);

            if (value.length > 0) {
                $activateButton.prop('disabled', false);

                // Provide visual feedback for license format
                if (value.length >= 10) {
                    $licenseInput.css('border-color', '#4F46E5');

                    // Auto-validate after user stops typing for 1 second
                    validateTimer = setTimeout(function() {
                        // Show hint that license looks valid
                        if (value.length >= 20) {
                            $licenseInput.css('box-shadow', '0 0 0 3px rgba(79, 70, 229, 0.1)');
                        }
                    }, 1000);
                } else {
                    $licenseInput.css('border-color', '');
                }
            } else {
                $activateButton.prop('disabled', true);
                $statusBox.fadeOut();
                $licenseInput.css({'border-color': '', 'box-shadow': ''});
            }
        });

        // Handle activate button click
        $activateButton.on('click', function(e) {
            e.preventDefault();

            if (isValidating) return;

            const licenseKey = $licenseInput.val().trim();
            const domain = $domainInput.val();

            if (!licenseKey) {
                showLicenseStatus('error', 'Please enter a license key');
                return;
            }

            // Show loading state
            isValidating = true;
            $activateButton.prop('disabled', true);
            const originalText = $activateButton.html();
            $activateButton.html('<span class="wpsafelink-wizard-loading"></span> ' + wpsafelink_wizard.i18n.activating);

            // Hide any previous status
            $statusBox.fadeOut();

            // AJAX request to validate license via wp_ajax_wpsafelink_wizard_save_step
            $.ajax({
                url: wpsafelink_wizard.ajax_url,
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'wpsafelink_wizard_save_step',
                    nonce: wpsafelink_wizard.nonce,
                    step: 'license',
                    license_key: licenseKey,
                    domain: domain
                },
                success: function(response) {
                    if (response.success) {
                        // License activated successfully
                        showLicenseStatus('success', response.data.message || wpsafelink_wizard.i18n.activated);
                        $activateButton.html('<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 6L9 17l-5-5"/></svg> ' + wpsafelink_wizard.i18n.activated);

                        // Redirect to next step after 1.5 seconds
                        setTimeout(function() {
                            if (response.data.redirect) {
                                window.location.href = response.data.redirect;
                            } else if (response.data.next_step) {
                                const nextUrl = updateQueryString('step', response.data.next_step);
                                window.location.href = nextUrl;
                            } else {
                                // Default to configuration step
                                window.location.href = updateQueryString('step', 'configuration');
                            }
                        }, 1500);
                    } else {
                        // License validation failed
                        const errorMsg = response.data || wpsafelink_wizard.i18n.invalid_license;
                        showLicenseStatus('error', errorMsg);

                        // Reset button
                        $activateButton.html(originalText);
                        $activateButton.prop('disabled', false);
                        isValidating = false;

                        // Shake the input field for feedback
                        $licenseInput.addClass('shake');
                        setTimeout(function() {
                            $licenseInput.removeClass('shake');
                        }, 500);
                    }
                },
                error: function() {
                    // Network or server error
                    showLicenseStatus('error', wpsafelink_wizard.i18n.error);

                    // Reset button
                    $activateButton.html(originalText);
                    $activateButton.prop('disabled', false);
                    isValidating = false;
                }
            });
        });

        // Function to show license status
        function showLicenseStatus(type, message) {
            const $statusContent = $statusBox.find('.wpsafelink-wizard-license-status-content');
            const $successIcon = $statusContent.find('.wpsafelink-wizard-license-status-icon.success');
            const $errorIcon = $statusContent.find('.wpsafelink-wizard-license-status-icon.error');
            const $statusText = $statusContent.find('.status-text');

            // Reset icons
            $successIcon.hide();
            $errorIcon.hide();

            // Show appropriate icon and set message
            if (type === 'success') {
                $successIcon.show();
                $statusBox.removeClass('error').addClass('success');
            } else {
                $errorIcon.show();
                $statusBox.removeClass('success').addClass('error');
            }

            $statusText.text(message);

            // Show status box with animation
            $statusBox.fadeIn(300);
        }
    }

    /**
     * Handle toggle switches
     */
    function handleToggleSwitches() {
        $('.wpsafelink-wizard-toggle-input').each(function() {
            const $checkbox = $(this);
            const $hiddenInput = $checkbox.siblings('input[type="hidden"]');

            $checkbox.on('change', function() {
                if ($(this).is(':checked')) {
                    $hiddenInput.val('yes');
                } else {
                    $hiddenInput.val('no');
                }
            });
        });
    }

    /**
     * Handle feature toggles
     */
    function handleFeatureToggles() {
        $('.wpsafelink-wizard-toggle-input[data-toggle-target]').on('change', function() {
            const targetId = $(this).data('toggle-target');
            const $target = $('#' + targetId);

            if ($(this).is(':checked')) {
                $target.slideDown();
            } else {
                $target.slideUp();
            }
        });
    }

    /**
     * Handle countdown toggle
     */
    function handleCountdownToggle() {
        $('[name="enable_countdown"]').on('change', function() {
            const $settings = $('#countdown-settings');
            if ($(this).is(':checked')) {
                $settings.slideDown();
            } else {
                $settings.slideUp();
            }
        });
    }

    /**
     * Handle configuration step dynamic fields
     */
    function handleConfigurationStep() {
        // Handle permalink mode changes
        $('input[name="permalink"]').on('change', function() {
            const mode = $(this).val();
            const $parameterField = $('.wrap-permalink_parameter');

            // Show/hide parameter field based on mode
            if (mode === '3') {
                $parameterField.slideUp(200);
            } else {
                $parameterField.slideDown(200);
            }

            // Update preview URLs
            updatePermalinkPreview();
        });

        // Handle auto integration toggle
        $('input[name="auto_integration_enable"]').on('change', function() {
            const $settings = $('.auto-integration-settings');
            if ($(this).is(':checked')) {
                $settings.slideDown(200);
            } else {
                $settings.slideUp(200);
            }
        });

        // Initialize help tooltips
        $('.wpsafelink-help-icon').each(function() {
            const $icon = $(this);
            const helpContent = $icon.data('help');
            const helpTitle = $icon.data('help-title') || 'Help';

            // Add click handler for help icons
            $icon.on('click', function(e) {
                e.preventDefault();
                e.stopPropagation();

                // Remove any existing tooltips
                $('.wpsafelink-help-tooltip').remove();

                // Create tooltip
                const $tooltip = $(`
                    <div class="wpsafelink-help-tooltip">
                        <div class="wpsafelink-help-tooltip-header">
                            <h4>${helpTitle}</h4>
                            <button class="wpsafelink-help-tooltip-close">×</button>
                        </div>
                        <div class="wpsafelink-help-tooltip-content">
                            ${helpContent}
                        </div>
                    </div>
                `);

                // Position tooltip near the icon
                $tooltip.appendTo('body');
                const iconPos = $icon.offset();
                $tooltip.css({
                    position: 'absolute',
                    top: iconPos.top + 25,
                    left: Math.min(iconPos.left, $(window).width() - 320),
                    zIndex: 9999
                });

                // Close tooltip handler
                $tooltip.find('.wpsafelink-help-tooltip-close').on('click', function() {
                    $tooltip.fadeOut(200, function() {
                        $(this).remove();
                    });
                });

                // Close on outside click
                setTimeout(function() {
                    $(document).one('click', function() {
                        $tooltip.fadeOut(200, function() {
                            $(this).remove();
                        });
                    });
                }, 100);
            });
        });

        // Initialize on load
        const initialTemplate = $('#template').val();
        if (initialTemplate) {
            $('#template').trigger('change');
        }

        const initialPermalink = $('input[name="permalink"]:checked').val();
        if (initialPermalink) {
            $('input[name="permalink"]:checked').trigger('change');
        }

        const autoIntegrationEnabled = $('input[name="auto_integration_enable"]').is(':checked');
        if (!autoIntegrationEnabled) {
            $('.auto-integration-settings').hide();
        }
    }

    /**
     * Handle AJAX step saving
     */
    function handleStepSaving() {
        // Handle configuration step specifically
        if (wpsafelink_wizard.current_step === 'configuration') {
            $('.wpsafelink-setup-wizard-form').on('submit', function(e) {
                e.preventDefault();

                const $form = $(this);
                const $button = $form.find('button[name="save_step"]');
                const originalText = $button.html();

                // Collect form data manually to ensure all fields are captured
                const formData = $form.serialize();

                // Show loading state
                $button.prop('disabled', true)
                    .html('<span class="wpsafelink-wizard-loading"></span> ' + wpsafelink_wizard.i18n.saving);

                // Add visual feedback
                $form.css('opacity', '0.8');

                // Send AJAX request
                $.ajax({
                    url: wpsafelink_wizard.ajax_url,
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        action: 'wpsafelink_wizard_save_step',
                        nonce: wpsafelink_wizard.nonce,
                        step: 'configuration',
                        data: formData
                    },
                    success: function(response) {
                        if (response.success) {
                            // Show success state
                            $button.html('<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 6L9 17l-5-5"/></svg> ' + wpsafelink_wizard.i18n.saved);
                            $button.addClass('success');

                            // Show success notification
                            showNotification('Configuration saved successfully!', 'success');

                            // Redirect to next step after a brief delay
                            setTimeout(function() {
                                if (response.data && response.data.redirect) {
                                    window.location.href = response.data.redirect;
                                } else if (response.data && response.data.next_step) {
                                    const nextUrl = updateQueryString('step', response.data.next_step);
                                    window.location.href = nextUrl;
                                } else {
                                    // Default to features step
                                    window.location.href = updateQueryString('step', 'features');
                                }
                            }, 1000);
                        } else {
                            // Show error message
                            const errorMsg = (response.data && typeof response.data === 'string')
                                ? response.data
                                : wpsafelink_wizard.i18n.error;
                            showNotification(errorMsg, 'error');

                            // Reset button
                            $button.prop('disabled', false).html(originalText);
                            $form.css('opacity', '1');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX Error:', status, error);
                        showNotification('Failed to save configuration. Please try again.', 'error');

                        // Reset button
                        $button.prop('disabled', false).html(originalText);
                        $form.css('opacity', '1');
                    }
                });
            });
        }
        // Handle other steps with AJAX
        else if ($('.wpsafelink-wizard-form button[type="submit"]').hasClass('ajax-submit')) {
            $('.wpsafelink-wizard-form').on('submit', function(e) {
                e.preventDefault();

                const $form = $(this);
                const $button = $form.find('button[type="submit"]');
                const step = wpsafelink_wizard.current_step;
                const formData = $form.serialize();

                // Show loading state
                $button.prop('disabled', true)
                    .html('<span class="wpsafelink-wizard-loading"></span> ' + wpsafelink_wizard.i18n.saving);

                // Send AJAX request
                $.ajax({
                    url: wpsafelink_wizard.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'wpsafelink_wizard_save_step',
                        nonce: wpsafelink_wizard.nonce,
                        step: step,
                        data: formData
                    },
                    success: function(response) {
                        if (response.success) {
                            // Show success message
                            $button.html(wpsafelink_wizard.i18n.saved);

                            // Redirect to next step or final page
                            if (response.data.redirect) {
                                window.location.href = response.data.redirect;
                            } else if (response.data.next_step) {
                                const nextUrl = updateQueryString('step', response.data.next_step);
                                window.location.href = nextUrl;
                            }
                        } else {
                            // Show error message
                            showNotification(response.data || wpsafelink_wizard.i18n.error, 'error');

                            // Reset button
                            $button.prop('disabled', false)
                                .html($button.data('original-text'));
                        }
                    },
                    error: function() {
                        showNotification(wpsafelink_wizard.i18n.error, 'error');

                        // Reset button
                        $button.prop('disabled', false)
                            .html($button.data('original-text'));
                    }
                });
            });
        }

        // Store original button text
        $('button[type="submit"]').each(function() {
            $(this).data('original-text', $(this).html());
        });
    }

    /**
     * Handle API copy functionality
     */
    function handleApiCopy() {
        $('.copy-api-btn').on('click', function() {
            const $button = $(this);
            const $input = $button.siblings('.api-key-field');

            // Select and copy text
            $input.select();
            document.execCommand('copy');

            // Show feedback
            const originalText = $button.text();
            $button.text('Copied!');

            setTimeout(function() {
                $button.text(originalText);
            }, 2000);
        });
    }

    /**
     * Handle Test Safelink functionality (Features/Testing step) - Ultra-thin Design
     */
    function handleTestSafelink() {
        // Only initialize if we're on the testing/features step
        if (!$('#wpsafelink_test_url').length) {
            return;
        }

        let isGenerating = false;

        // Handle quick test URL links - Ultra-thin approach
        setupQuickTestLinks();

        // Handle generate test link button
        setupGenerateButton();

        // Handle copy to clipboard for generated links
        setupCopyButtons();

        // Handle enter key submission
        setupEnterKeySubmit();

        /**
         * Setup quick test URL links - Ultra-thin Design
         */
        function setupQuickTestLinks() {
            // Handle both old and new selectors for compatibility
            $('.wpsafelink-wizard-quick-link, .wpsafelink-quick-btn').on('click', function(e) {
                e.preventDefault();
                const url = $(this).data('url');
                $('#wpsafelink_test_url').val(url).focus();

                // Ultra-thin visual feedback
                $('.wpsafelink-wizard-quick-link, .wpsafelink-quick-btn').removeClass('active');
                $(this).addClass('active');

                // Clean removal of active state
                setTimeout(() => {
                    $(this).removeClass('active');
                }, 500);
            });
        }

        /**
         * Setup generate test link button - Ultra-thin
         */
        function setupGenerateButton() {
            $('#generate_test_link').on('click', function() {
                if (isGenerating) return;

                const $button = $(this);
                const url = $('#wpsafelink_test_url').val().trim();

                // Ultra-thin validation
                if (!url) {
                    $('#wpsafelink_test_url').focus();
                    shakeElement($('#wpsafelink_test_url'));
                    return;
                }

                // URL validation
                if (!isValidUrl(url)) {
                    showNotification('Please enter a valid URL', 'error');
                    shakeElement($('#wpsafelink_test_url'));
                    return;
                }

                generateSafelink(url, $button);
            });
        }

        /**
         * Generate safelink via AJAX - Ultra-thin
         */
        function generateSafelink(url, $button) {
            isGenerating = true;

            // Ultra-thin loading state
            const originalHtml = $button.html();
            $button.prop('disabled', true)
                   .addClass('loading')
                   .html('<span class="wpsafelink-wizard-loading"></span> Generating...');

            // Add generating animation to section
            $('.wpsafelink-wizard-section').addClass('generating');

            $.ajax({
                url: wpsafelink_wizard.ajax_url,
                type: 'POST',
                data: {
                    action: 'wpsafelink_revamp_generate_link_ajax',
                    url: url,
                    _wpnonce: wpsafelink_wizard.generate_nonce || wpsafelink_wizard.nonce
                },
                success: function(response) {
                    if (response.success && response.data) {
                        // Display generated link with animation
                        displayGeneratedLink(response.data, url);

                        // Clear input and show success
                        $('#wpsafelink_test_url').val('');
                        showNotification('Safelink generated successfully!', 'success');

                        // Log generated link data for debugging
                        console.log('Generated link data:', response.data);

                        // Analytics or tracking
                        trackLinkGeneration();
                    } else {
                        showNotification(response.data?.message || 'Failed to generate link', 'error');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', status, error);
                    showNotification('An error occurred. Please try again.', 'error');
                },
                complete: function() {
                    // Reset button state - Ultra-thin
                    isGenerating = false;
                    $button.prop('disabled', false)
                           .removeClass('loading')
                           .html(originalHtml);
                    $('.wpsafelink-wizard-section').removeClass('generating');
                }
            });
        }

        /**
         * Display generated link result
         */
        function displayGeneratedLink(data, originalUrl) {
            const $display = $('#generated_link_display');
            const $successCard = $('#success_info_card');

            // Update values
            $('#generated_safelink').val(data.safelink);
            $('#original_url_display').val(originalUrl);
            $('#test_safelink').attr('href', data.safelink);

            // Show with animation
            $display.slideDown(300, function() {
                // Highlight the generated link
                $('#generated_safelink').addClass('highlight');
                setTimeout(() => {
                    $('#generated_safelink').removeClass('highlight');
                }, 1000);
            });

            // Show success card after a delay
            setTimeout(() => {
                $successCard.slideDown(300);
            }, 500);

            // Auto-focus the generated link for easy copying
            setTimeout(() => {
                $('#generated_safelink').select();
            }, 400);

            // Log the data for reference
            if (window.console && window.console.log) {
                console.log('Safelink Generated:', {
                    safelink: data.safelink,
                    safe_id: data.safe_id || 'N/A',
                    original: originalUrl,
                    timestamp: new Date().toISOString()
                });
            }
        }

        /**
         * Reset test form - Ultra-thin (exposed to global scope)
         */
        window.resetTestForm = function() {
            $('#generated_link_display').slideUp(200);
            $('#success_info_card').slideUp(200);
            $('#wpsafelink_test_url').val('').focus();

            // Clear active states from quick links
            $('.wpsafelink-wizard-quick-link').removeClass('active');

            // Clear notifications
            $('.wpsafelink-wizard-notification').fadeOut(200, function() {
                $(this).remove();
            });
        };

        /**
         * Setup copy buttons - Ultra-thin
         */
        function setupCopyButtons() {
            $(document).on('click', '.copy-link-btn', function() {
                const targetId = $(this).data('target');
                const $input = $('#' + targetId);
                const text = $input.val();

                // Select the input text
                $input[0].select();
                $input[0].setSelectionRange(0, 99999);

                copyToClipboard(text, $(this));
            });
        }

        /**
         * Copy to clipboard with feedback - Ultra-thin
         */
        function copyToClipboard(text, $button) {
            // Modern clipboard API with fallback
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(text).then(() => {
                    showCopySuccess($button);
                }).catch(err => {
                    fallbackCopy(text, $button);
                });
            } else {
                fallbackCopy(text, $button);
            }
        }

        /**
         * Fallback copy method for older browsers - Ultra-thin
         */
        function fallbackCopy(text, $button) {
            const $temp = $('<textarea>');
            $('body').append($temp);
            $temp.val(text).select();

            try {
                document.execCommand('copy');
                showCopySuccess($button);
            } catch (err) {
                console.error('Copy failed:', err);
                showNotification('Failed to copy. Please copy manually.', 'error');
            }

            $temp.remove();
        }

        /**
         * Show copy success feedback - Ultra-thin
         */
        function showCopySuccess($button) {
            const $copyText = $button.find('.copy-text');
            const originalText = $copyText.text();

            // Ultra-thin feedback
            $button.addClass('copied');
            $copyText.text('Copied!');

            setTimeout(() => {
                $button.removeClass('copied');
                $copyText.text(originalText);
            }, 2000);
        }

        /**
         * Setup enter key submit
         */
        function setupEnterKeySubmit() {
            $('#wpsafelink_test_url').on('keypress', function(e) {
                if (e.which === 13) {
                    e.preventDefault();
                    $('#generate_test_link').click();
                }
            });
        }

        /**
         * Validate URL format
         */
        function isValidUrl(string) {
            try {
                const url = new URL(string);
                return url.protocol === 'http:' || url.protocol === 'https:';
            } catch (err) {
                return false;
            }
        }

        /**
         * Shake element for error feedback
         */
        function shakeElement($element) {
            $element.addClass('shake');
            setTimeout(() => {
                $element.removeClass('shake');
            }, 500);
        }

        /**
         * Track link generation for analytics - Ultra-thin
         */
        function trackLinkGeneration() {
            // Minimal analytics tracking
            if (typeof gtag !== 'undefined') {
                gtag('event', 'generate_safelink', {
                    'event_category': 'wizard',
                    'event_label': 'test_link'
                });
            }
        }

    }

    /**
     * Animate wizard elements on load
     */
    function animateWizardElements() {
        // Animate benefits
        $('.wpsafelink-wizard-benefit').each(function(index) {
            const $benefit = $(this);
            setTimeout(function() {
                $benefit.css({
                    'opacity': '0',
                    'transform': 'translateY(20px)'
                }).animate({
                    'opacity': '1'
                }, 400).css('transform', 'translateY(0)');
            }, index * 100);
        });

        // Animate feature cards
        $('.wpsafelink-wizard-feature-card').each(function(index) {
            const $card = $(this);
            setTimeout(function() {
                $card.css({
                    'opacity': '0',
                    'transform': 'scale(0.95)'
                }).animate({
                    'opacity': '1'
                }, 400).css('transform', 'scale(1)');
            }, index * 50);
        });

        // Animate next step cards
        $('.wpsafelink-wizard-next-step-card').each(function(index) {
            const $card = $(this);
            setTimeout(function() {
                $card.css({
                    'opacity': '0',
                    'transform': 'translateX(-20px)'
                }).animate({
                    'opacity': '1'
                }, 400).css('transform', 'translateX(0)');
            }, index * 100);
        });
    }

    /**
     * Show notification message
     */
    function showNotification(message, type = 'info') {
        const $notification = $('<div class="wpsafelink-wizard-notification"></div>')
            .addClass('wpsafelink-wizard-notification-' + type)
            .text(message);

        $('body').append($notification);

        // Use CSS animations instead of jQuery animate
        $notification.css({
            'opacity': '0',
            'transform': 'translateX(100px)',
            'transition': 'all 0.3s ease'
        });

        // Force reflow to ensure transition works
        $notification[0].offsetHeight;

        $notification.css({
            'opacity': '1',
            'transform': 'translateX(0)'
        });

        setTimeout(function() {
            $notification.css({
                'opacity': '0',
                'transform': 'translateX(100px)'
            });

            setTimeout(function() {
                $notification.remove();
            }, 300);
        }, 3000);
    }

    /**
     * Update query string parameter
     */
    function updateQueryString(key, value) {
        const url = new URL(window.location.href);
        url.searchParams.set(key, value);
        return url.toString();
    }

    function updatePermalinkPreview() {
        const $parameter = $('#permalink_parameter');
        const parameter = $parameter.val() || 'go';

        // Ultra-thin preview update
        $('.wpsafelink-wizard-radio').each(function() {
            const $radio = $(this);
            const radioMode = $radio.find('input[type="radio"]').val();
            const $description = $radio.find('.wpsafelink-wizard-radio-description');

            let preview = '';
            const baseUrl = window.location.origin;

            switch(radioMode) {
                case '1':
                    preview = baseUrl + '/' + parameter + '/safelink_code';
                    break;
                case '2':
                    preview = baseUrl + '/?' + parameter + '=safelink_code';
                    break;
                case '3':
                    preview = baseUrl + '/?safelink_code';
                    break;
            }

            $description.text(preview);
        });
    }

    // Update preview when parameter changes - Ultra-thin
    $('#permalink_parameter').on('input', function() {
        updatePermalinkPreview();
    });

})(jQuery);