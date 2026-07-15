<?php
/**
 * WP Safelink Settings
 *
 * @author WP Safelink
 * @package WP Safelink
 * @since 1.0.0
 */
if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

if (!class_exists('WPSafelink_Settings')) :

    class WPSafelink_Settings
    {

        /**
         * Option name
         */
        private $opt_setting_key = 'wpsafelink_settings';

        /**
         * Constructor
         *
         * @return void
         * @since 1.0.0
         */
        public function __construct()
        {
            add_action('admin_enqueue_scripts', array($this, 'admin_enqueue'));
            add_action('admin_menu', array($this, 'add_menu_page'));
            add_action('init', array($this, 'default_setting'));

	        // Wizard AJAX handlers
	        add_action('wp_ajax_wpsafelink_validate_license', array($this, 'ajax_validate_license'));
	        add_action('wp_ajax_wpsafelink_change_license', array($this, 'ajax_change_license'));
	        add_action('wp_ajax_wpsafelink_check_integration', array($this, 'ajax_check_integration'));
	        add_action( 'wp_ajax_wpsafelink_license_status', array( $this, 'ajax_license_status' ) );
	        add_action( 'wp_ajax_wpsafelink_integration_status', array( $this, 'ajax_check_integration_status' ) );

	        add_action( 'init', array( $this, 'create_safelink_js' ) );

	        // REST API initialization
	        add_action( 'rest_api_init', array( $this, 'register_rest_routes' ) );
        }

        public function create_safelink_js() {
	        $current_url = $_SERVER['REQUEST_URI'];
	        if ( strpos( $current_url, 'wpsafelink.js' ) === false ) {
		        return;
	        }

	        $options = wpsafelink_options();

	        $method = $options['auto_convert_link_method'];

	        $dm = "";
	        $dm_exclude = "";
	        if ($method == "include") {
		        $dom = explode( PHP_EOL, $options['domain_list']);
		        $dom = array_map( 'trim', $dom );
		        $dom = array_map( 'strtolower', $dom );
		        $dm  = '';
		        $rep = array( 'https://', 'http://', 'www.' );
		        foreach ( $dom as $d ) {
			        $dm .= '"' . $d . '",';
		        }
	        } else {
		        $dom_exclude = explode(PHP_EOL, $options['domain_list']);
		        $dom_exclude = array_map('trim', $dom_exclude);
		        $dom_exclude = array_map('strtolower', $dom_exclude);
		        $dm_exclude = '';
		        $rep = array('https://', 'http://', 'www.');
		        foreach ($dom_exclude as $d) {
			        $dm_exclude .= '"' . $d . '",';
		        }
            }

	        $domain = (empty($options['auto_convert_link_base_url']) ? home_url() : $options['auto_convert_link_base_url']);
	        $domain = (substr($domain, -1) != '/' ? $domain . '/' : $domain);
	        if ($options['permalink'] == 1) {
		        $safe_link = $domain . $options['permalink_parameter'] . '/';
	        } else if ($options['permalink'] == 2) {
		        $safe_link = $domain . '?' . $options['permalink_parameter'] . '=';
	        } else {
		        $safe_link = home_url() . '?';
	        }

	        $replace = array(
		        '{base_url}' => $safe_link,
		        '{domain}' => rtrim($dm, ","),
		        '{exclude_domain}' => rtrim($dm_exclude, ",")
	        );

	        $js = file_get_contents(wpsafelink_plugin_path() . '/assets/wpsafelink.js');
	        $js = str_replace(array_keys($replace), array_values($replace), $js);

	        require_once wpsafelink_plugin_path() . '/vendor/HunterObfuscator.php';
	        $hunter = new HunterObfuscator($js);
	        $obsfucated = $hunter->Obfuscate();

	        header( 'Content-Type: application/javascript' );
	        echo $obsfucated;
	        die();
        }

        /**
         * Clear cached license script
         *
         * @access  private
         * @return  void
         * @since   1.0.0
         */
        private function clear_cached_license_script()
        {
            $options = wpsafelink_options();
            if (isset($options['license']) && !empty($options['license'])) {
                $options['license'] = '';
                update_option($this->opt_setting_key, $options);
            }
	        // Clear the new wp_options-based license storage
	        delete_option( 'wpsafelink_license_data' );
	        delete_option( 'wpsafelink_license_migrated' );
        }

        /**
         * Initialize default settings if options don't exist
         *
         * @access  private
         * @return  void
         * @since   1.0.0
         */
        private function initialize_default_settings()
        {
            $exist_options = wpsafelink_options();
            if (!$exist_options) {
                $all_settings = [
                    'general',
                    'auto-generate-link',
                    'templates',
	                'styles',
                    'captcha',
                    'advertisement',
                    'anti-adblock',
                    'adlinkfly'
                ];
                $default_options = [];
                foreach ($all_settings as $setting) {
                    $options = $this->get_general_options($setting);
                    $default_options = array_merge($default_options, $options);
                }

                $update_options = [];
                foreach ($default_options as $key => $value) {
                    $disabled = $value['disabled'] ?? false;
                    if ($disabled) {
                        continue;
                    }
                    $update_options[$key] = $value['default'] ?? "";
                }

                update_option($this->opt_setting_key, $update_options);
            }
        }

        /**
         * Change settings to default setting
         *
         * @access  public
         * @return  void
         * @since   1.0.0
         */
        public function default_setting($force = false)
        {
        }

        /**
         * Enqueue scripts and style
         *
         * @access  public
         * @return  void
         * @since   1.0.0
         */
	    public function admin_enqueue( $hook = '' )
        {
	        // Only enqueue on our plugin settings page
	        if ( $hook !== 'toplevel_page_wpsafelink' ) {
		        return;
	        }

	        // Media uploader for Styles tab (and media fields)
	        if ( function_exists( 'wp_enqueue_media' ) ) {
		        wp_enqueue_media();
	        }
	        $css_url = plugins_url( 'assets/css/admin.css', wpsafelink_plugin_file() );
	        $ver     = '';
	        if ( file_exists( wpsafelink_plugin_path() . '/assets/css/admin.css' ) ) {
		        $ver = filemtime( wpsafelink_plugin_path() . '/assets/css/admin.css' );
	        }
	        wp_enqueue_style( 'wpsafelink-admin', $css_url, array(), $ver );

	        // Localize script for AJAX
	        wp_localize_script( 'jquery', 'wpsafelink_ajax', array(
		        'ajax_url' => admin_url( 'admin-ajax.php' ),
		        '_wpnonce' => wp_create_nonce( 'wpsafelink_ajax_nonce' ),
	        ) );
	        
	        // Add inline CSS for help system
	        wp_add_inline_style( 'wpsafelink-admin', $this->get_help_system_styles() );
	        
	        // Add inline JS for help system
	        wp_add_inline_script( 'jquery', $this->get_help_system_script() );
        }

        /**
         * Adding Menu
         *
         * @access  public
         * @return  void
         * @since   1.0.0
         */
        public function add_menu_page()
        {
            add_menu_page(
                'WP Safelink',
                'WP Safelink',
                'manage_options',
                'wpsafelink',
                array($this, 'submenu_page_callback'),
                ''
            );
        }

        /**
         * Print the UI Settings
         *
         * @access  public
         * @return  void
         * @since   1.0.0
         */
        public function submenu_page_callback()
        {
            global $wpsafelink_core;
            $success_update = '';
            $success_message = '';
            $error_update = '';
            $error_message = '';

            $license = $wpsafelink_core->license('key', true);
            $check_license = !empty($license) ?? false;

            // Redirect to wizard if no license exists and wizard not completed
            if (!$check_license) {
                echo '<html><head><meta http-equiv="refresh" content="0; url=' . admin_url('admin.php?page=wpsafelink-setup') . '"></head></html>';
                exit;
            }

            // Show success message if coming from completed wizard
            if (isset($_GET['wizard_completed']) && $_GET['wizard_completed'] == '1') {
                $success_update = true;
                $success_message = 'Setup wizard completed successfully! Your WP Safelink is now ready to use.';
            }
            
            // Check if user has PRO version activated using the new is_pro() method
            // This uses ultra-robust validation to ensure only PRO licenses get access
            $has_pro_license = $wpsafelink_core->is_pro();
            // To test overlay without PRO license: $has_pro_license = false;

            // Process save data
            if (isset($_POST['action'])) {
                if ($_POST['action'] == 'save') {
                    $post_options = $_POST['wpsafelink'] ?? array();

                    $current_options = wpsafelink_options();
                    $post_options = array_merge($current_options, $post_options);

                    update_option($this->opt_setting_key, $post_options);

                    $success_update = true;
                    $success_message = 'Settings saved.';
                } else if ($_POST['action'] == 'license') {
                    if (isset($_POST['sub']) && $_POST['sub'] == 'Change License') {
                        $this->clear_cached_license_script();
	                    $success_update  = true;
	                    $success_message = 'License cleared successfully. Please enter a new license.';
                    } else {
                        $license = $_POST['wpsafelink_license'];
                        $check_license = $wpsafelink_core->license($license);
	                    if ( $check_license['success'] ) {
                            $success_update = true;
                            $success_message = 'License validated.';

	                        $this->initialize_default_settings();
                        } else {
                            $error_update = true;
		                    $error_message = $check_license['data']->msg;
                        }
                    }
                } else if ($_POST['action'] == 'generate_link') {
                    $target_link = $_POST['wpsafelink_link'];
                    $link = $wpsafelink_core->postGenerateLink($target_link);
                    $success_update = true;
                    $success_message = 'Target Link : <code><a target="_blank" href="' . $target_link . '">' . $target_link . '</a></code><br/>Your Safelink : <code><a target="_blank" href="' . $link['generated3'] . '">' . $link['generated3'] . '</a></code> OR <code><a target="_blank" href="' . $link['encrypt_link'] . '">' . $link['encrypt_link'] . '</a></code>';
                }
            }
            if (isset($_GET['delete']) && $_GET['delete'] > 0) {
                global $wpdb;
                $wpdb->delete("{$wpdb->prefix}wpsafelink", array('ID' => $_GET['delete']), '');

                $success_update = true;
                $success_message = 'Safelink deleted.';
            }

            $tab = (!empty($_GET['tab']) ? $_GET['tab'] : 'general');
	        // Backward compatibility: map old Styles tab to Templates
	        if ( $tab === 'styles' ) {
		        $tab = 'templates';
	        }
            $plugin_data = get_plugin_data(wpsafelink_plugin_file());
            $data = wpsafelink_options();

	        $license                       = $wpsafelink_core->license();
	        $check_license                 = $license['success'];
            ?>
            <div id="wpsafelink-settings" class="wrap">

                <?php if ($success_update) : ?>
                    <div id="setting-error-settings_updated" class="updated settings-error notice is-dismissible">
                        <p><strong><?php echo $success_message; ?></strong></p>
                        <button type="button" class="notice-dismiss"><span
                                    class="screen-reader-text">Dismiss this notice.</span></button>
                    </div>
                <?php endif; ?>

                <?php if ($error_update) : ?>
                    <div id="setting-error-settings_updated" class="notice notice-error is-dismissible">
                        <p><strong><?php echo $error_message; ?></strong></p>
                        <button type="button" class="notice-dismiss"><span
                                    class="screen-reader-text">Dismiss this notice.</span></button>
                    </div>
                <?php endif; ?>

                <h1>WP Safelink <?php echo $has_pro_license ? '( PRO Version )' : '( Full Version )'; ?>
                    <small><code>version <?php echo $plugin_data['Version']; ?></code></small></h1>
                <p><?php echo $plugin_data['Description']; ?></p>

                <?php if (!WPSAFELINK_IONCUBE_OK) :
                    $ioncube_installed = extension_loaded('ionCube Loader');
                    $ioncube_version = phpversion('ionCube Loader');
                ?>
                <div class="wpsafelink-ioncube-warning" style="
                    background: #fef2f2;
                    border: 1px solid #fca5a5;
                    border-left: 4px solid #dc2626;
                    border-radius: 4px;
                    padding: 16px 20px;
                    margin: 15px 0 20px 0;
                    display: flex;
                    align-items: flex-start;
                    gap: 14px;
                ">
                    <span class="dashicons dashicons-warning" style="color: #dc2626; font-size: 24px; margin-top: 2px;"></span>
                    <div style="flex: 1;">
                        <h3 style="margin: 0 0 8px; color: #991b1b; font-size: 15px;">
                            <?php esc_html_e('ionCube Loader Required', 'wp-safelink'); ?>
                        </h3>
                        <p style="margin: 0 0 12px; color: #b91c1c; font-size: 13px;">
                            <?php esc_html_e('WP Safelink requires ionCube Loader 14.0.0+ to function. Some features are disabled until this requirement is met.', 'wp-safelink'); ?>
                        </p>
                        <table class="widefat striped" style="max-width: 450px; margin-bottom: 12px; font-size: 13px;">
                            <tr>
                                <td><strong><?php esc_html_e('Required', 'wp-safelink'); ?></strong></td>
                                <td>ionCube Loader 14.0.0+</td>
                            </tr>
                            <tr>
                                <td><strong><?php esc_html_e('Current', 'wp-safelink'); ?></strong></td>
                                <td><?php echo $ioncube_installed ? esc_html($ioncube_version) : '<em>' . esc_html__('Not Installed', 'wp-safelink') . '</em>'; ?></td>
                            </tr>
                        </table>
                        <p style="margin: 0;">
                            <a href="<?php echo esc_url(admin_url('admin.php?page=wpsafelink')); ?>" class="button button-small">
                                <?php esc_html_e('Check Again', 'wp-safelink'); ?>
                            </a>
                            <span style="margin-left: 10px; color: #6b7280; font-size: 12px;">
                                <?php esc_html_e('Contact your hosting provider to install ionCube Loader.', 'wp-safelink'); ?>
                            </span>
                        </p>
                    </div>
                </div>
                <?php endif; ?>

	            <?php if ( $check_license && $tab !== 'license-tab' ) : ?>
                    <style>
                        .wpsafelink_license_status_table .spinner {
                            visibility: visible;
                            float: none;
                            margin: 0;
                        }

                        .wpsafelink_license_status_table mark.yes {
                            background: #FFF;
                            color: #000;
                            padding: 3px 8px;
                            border-radius: 3px;
                            font-weight: 600;
                        }

                        .wpsafelink_license_status_table mark.no {
                            background: #e74c3c;
                            color: #fff;
                            padding: 3px 8px;
                            border-radius: 3px;
                            font-weight: 600;
                        }

                        .wpsafelink_license_status_table tbody td {
                            padding: 10px 12px;
                        }

                        /* Responsive positioning for larger screens */
                        @media screen and (min-width: 1400px) {
                            #wpsafelink-license-status {
                                position: absolute;
                                right: 20px;
                                top: 20px;
                                width: 350px;
                                margin-top: 0 !important;
                                z-index: 100;
                                box-shadow: 0 1px 3px rgba(0, 0, 0, 0.13);
                                background: #fff;
                                border-radius: 4px;
                                overflow: visible;
                            }
                            
                            #wpsafelink-integration-status {
                                /* Integration status is now inside license status container */
                                margin-top: 0 !important;
                            }
                            

                            #wpsafelink-settings {
                                position: relative;
                                padding-right: 380px;
                                min-height: 600px; /* Ensure enough height for both panels */
                            }

                            .wpsafelink_license_status_table {
                                max-width: 100% !important;
                                border: none;
                            }
                        }

                        /* For smaller screens, keep default stacked layout */
                        @media screen and (max-width: 1199px) {
                            #wpsafelink-license-status {
                                display: block;
                                margin-top: 20px;
                                position: static;
                                width: 100%;
                                box-shadow: 0 1px 3px rgba(0, 0, 0, 0.13);
                                background: #fff;
                                border-radius: 4px;
                            }
                            
                            #wpsafelink-integration-status {
                                margin-top: 0 !important;
                            }

                            .wpsafelink_license_status_table {
                                max-width: 100%;
                            }
                        }

                        /* Spinning animation for loading state */
                        @keyframes spin {
                            0% { transform: rotate(0deg); }
                            100% { transform: rotate(360deg); }
                        }

                        .dashicons-update-alt.spin {
                            animation: spin 1s linear infinite;
                            display: inline-block;
                        }

                        /* Improve modal button disabled state */
                        #wpsaf-modal-confirm:disabled {
                            opacity: 0.7;
                            cursor: not-allowed;
                        }
                    </style>
                    <div id="wpsafelink-license-status">
                        <table class="wpsafelink_license_status_table widefat" id="wpsafelink_license_status"
                               cellspacing="0">
                            <tbody>
                            <tr>
                                <td colspan="3" class="load_status" style="text-align: center; padding: 20px;">
                                    <span class="spinner is-active" style="float: none; margin: 0 auto;"></span>
                                    <span style="margin-left: 10px;">Checking license status with server...</span>
                                </td>
                            </tr>
                            </tbody>
                            <tfoot>
                            </tfoot>
                        </table>
                        
                        <?php
                        // Include Integration Status section inside License Status container
                        $wpsafelink_settings_instance = $this;
                        include_once wpsafelink_plugin_path() . '/views/settings/integration-status.php';
                        ?>
                    </div>

                    <script type="text/javascript">
                    jQuery(function($) {
                        // Toggle status table visibility
                        $(document).on('click', '#wpsaf-status-toggle', function() {
                            var $body = $('#wpsaf-status-body');
                            var $icon = $('#wpsaf-status-toggle-icon');

                            $body.slideToggle(300, function() {
                                if ($body.is(':visible')) {
                                    $icon.removeClass('dashicons-arrow-down-alt2').addClass('dashicons-arrow-up-alt2');
                                } else {
                                    $icon.removeClass('dashicons-arrow-up-alt2').addClass('dashicons-arrow-down-alt2');
                                }
                            });
                        });

                        // Change license button - using event delegation for dynamic HTML
                        $(document).on('click', '#wpsaf-change-license-btn, #wpsafelink_change_license_btn', function(e) {
                            e.preventDefault();
                            $('#wpsaf-change-modal').fadeIn(300);
                        });

                        // Modal close handlers - using event delegation for dynamic HTML
                        $(document).on('click', '#wpsaf-modal-close, #wpsaf-modal-cancel', function(e) {
                            e.preventDefault();
                            $('#wpsaf-change-modal').fadeOut(300);
                        });

                        // Modal overlay click to close - using event delegation for dynamic HTML
                        $(document).on('click', '.wpsaf-modal-overlay', function(e) {
                            if ($(e.target).hasClass('wpsaf-modal-overlay')) {
                                $('#wpsaf-change-modal').fadeOut(300);
                            }
                        });

                        // Confirm change license - using AJAX instead of form submission
                        $(document).on('click', '#wpsaf-modal-confirm', function(e) {
                            e.preventDefault();

                            var $button = $(this);
                            var originalText = $button.html();

                            // Disable button and show loading state
                            $button.prop('disabled', true).html('<span class="dashicons dashicons-update-alt spin"></span> Processing...');

                            // Make AJAX request to clear the license
                            $.ajax({
                                url: wpsafelink_ajax.ajax_url,
                                type: 'POST',
                                data: {
                                    action: 'wpsafelink_change_license',
                                    nonce: '<?php echo wp_create_nonce('wpsafelink_change_license'); ?>'
                                },
                                success: function(response) {
                                    if (response.success) {
                                        // Close modal
                                        $('#wpsaf-change-modal').fadeOut(300);

                                        // Show success message
                                        var successHtml = '<div class="notice notice-success is-dismissible"><p>' + response.data.message + '</p></div>';
                                        $('.wpsaf-main-container').prepend(successHtml);

                                        window.location.reload();
                                    } else {
                                        // Show error message
                                        alert('Error: ' + (response.data.message || 'Failed to clear license'));
                                        // Reset button
                                        $button.prop('disabled', false).html(originalText);
                                    }
                                },
                                error: function() {
                                    alert('Failed to process request. Please try again.');
                                    // Reset button
                                    $button.prop('disabled', false).html(originalText);
                                }
                            });
                        });
                    });
                    </script>
                    <script type="text/javascript">
                        function wpsafelink_check_license_status() {
                            jQuery.ajax({
                                url: wpsafelink_ajax.ajax_url,
                                type: 'POST',
                                data: {
                                    action: 'wpsafelink_license_status',
                                    _wpnonce: wpsafelink_ajax._wpnonce,
                                    page: 'wpsafelink',
                                    tab: '<?php echo esc_js( $tab ); ?>'
                                },
                                success: function (response) {
                                    if (response.status === 'success') {
                                        jQuery('#wpsafelink_license_status').html(response.message);
                                    } else {
                                        jQuery('#wpsafelink_license_status').html('<tr><td colspan="3" style="text-align: center; padding: 20px; color: #dc3232;">Failed to check license status</td></tr>');
                                    }
                                },
                                error: function (response) {
                                    jQuery('#wpsafelink_license_status').html('<tr><td colspan="3" style="text-align: center; padding: 20px; color: #dc3232;">Failed to connect to license server</td></tr>');
                                }
                            });
                        }

                        jQuery(document).ready(function ($) {
                            // Check license status on page load
                            if ($('#wpsafelink_license_status').length) {
                                wpsafelink_check_license_status();
                            }
                            
                            // Duplicate handler removed - using modal approach from lines 513-536 instead
                        });
                    </script>

                    <!-- Change License Modal -->
                    <div id="wpsaf-change-modal" class="wpsaf-license-modal" style="display: none;">
                        <div class="wpsaf-modal-overlay"></div>
                        <div class="wpsaf-modal-container">
                            <div class="wpsaf-modal-header">
                                <span class="wpsaf-modal-icon">⚠️</span>
                                <h3>Change License Key</h3>
                                <button type="button" class="wpsaf-modal-close" id="wpsaf-modal-close">
                                    <span class="dashicons dashicons-no"></span>
                                </button>
                            </div>

                            <div class="wpsaf-modal-body">
                                <div class="wpsaf-warning-content">
                                    <p class="wpsaf-warning-title">
                                        Are you sure you want to change your license key?
                                    </p>
                                    <p class="wpsaf-warning-description">
                                        Changing your license will deactivate the current license on this site.
                                    </p>

                                    <div class="wpsaf-impact-list">
                                        <div class="wpsaf-impact-item">
                                            <span class="wpsaf-impact-icon">📌</span>
                                            <span>Current license will be cleared from this installation</span>
                                        </div>
                                        <div class="wpsaf-impact-item">
                                            <span class="wpsaf-impact-icon">🔄</span>
                                            <span>You'll need to enter a new valid license key</span>
                                        </div>
                                        <div class="wpsaf-impact-item">
                                            <span class="wpsaf-impact-icon">✨</span>
                                            <span>Premium features will be temporarily disabled</span>
                                        </div>
                                    </div>

                                    <div class="wpsaf-reactivation-note">
                                        <p>
                                            <strong>Note:</strong> You can reuse this license on another site or re-enter it here later.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="wpsaf-modal-footer">
                                <button type="button" class="wpsaf-button wpsaf-button-secondary" id="wpsaf-modal-cancel">
                                    Cancel
                                </button>
                                <button type="button" class="wpsaf-button wpsaf-button-primary" id="wpsaf-modal-confirm">
                                    <span class="dashicons dashicons-edit"></span>
                                    Change License
                                </button>
                            </div>
                        </div>
                    </div>


	            <?php endif; ?>

                <?php if (!$check_license) :
                    require_once wpsafelink_plugin_path() . '/views/settings/settings-license.php';
                else: ?>
                    <!-- Top Navigation Bar -->
                    <div class="wpsaf-top-nav">
                        <a class="wpsaf-top-nav-item <?php echo in_array($tab, ['general', 'templates', 'integration', 'second_safelink', 'google_redirect', 'multiple_pages', 'adlinkfly_pro', 'pro_tools']) ? 'active' : ''; ?>"
                           href="<?php echo admin_url('admin.php?page=wpsafelink&tab=general'); ?>">
                            <span class="dashicons dashicons-admin-settings"></span>
                            Settings
                        </a>
                        <a class="wpsaf-top-nav-item <?php echo ($tab == 'generate-link') ? 'active' : ''; ?>"
                           href="<?php echo admin_url('admin.php?page=wpsafelink&tab=generate-link'); ?>">
                            <span class="dashicons dashicons-admin-links"></span>
                            Generate Link
                        </a>
                        <a class="wpsaf-top-nav-item <?php echo ($tab == 'license-tab') ? 'active' : ''; ?>"
                           href="<?php echo admin_url('admin.php?page=wpsafelink&tab=license-tab'); ?>">
                            <span class="dashicons dashicons-admin-network"></span>
                            License
                        </a>
                        <a class="wpsaf-top-nav-item"
                           href="https://themeson.com/support/?utm_source=wp-admin&utm_medium=plugin&utm_campaign=wp-safelink"
                           target="_blank">
                            <span class="dashicons dashicons-sos"></span>
                            Support
                            <span class="dashicons dashicons-external" style="font-size: 12px; margin-left: 2px;"></span>
                        </a>
                    </div>

                    <!-- Main Settings Container with Sidebar -->
                    <div class="wpsaf-settings-container<?php echo in_array($tab, ['generate-link', 'license-tab']) ? ' no-sidebar' : ''; ?>">
                        <?php if (!in_array($tab, ['generate-link', 'license-tab'])) : ?>
                        <!-- Left Sidebar Navigation -->
                        <div class="wpsaf-sidebar">
                            <ul class="wpsaf-sidebar-menu">
                                <!-- Basic Settings -->
                                <li class="<?php echo($tab == 'general' ? 'active' : ''); ?>">
                                    <a href="<?php echo admin_url('admin.php?page=wpsafelink&tab=general'); ?>">
                                        <span class="wpsaf-menu-icon"><span class="dashicons dashicons-admin-generic"></span></span>
                                        <div class="wpsaf-menu-content">
                                            <span class="wpsaf-menu-title">GENERAL</span>
                                            <span class="wpsaf-menu-desc">Basic configuration</span>
                                        </div>
                                    </a>
                                </li>
                                <li class="<?php echo($tab == 'templates' ? 'active' : ''); ?>">
                                    <a href="<?php echo admin_url('admin.php?page=wpsafelink&tab=templates'); ?>">
                                        <span class="wpsaf-menu-icon"><span class="dashicons dashicons-admin-appearance"></span></span>
                                        <div class="wpsaf-menu-content">
                                            <span class="wpsaf-menu-title">TEMPLATES</span>
                                            <span class="wpsaf-menu-desc">Page templates</span>
                                        </div>
                                    </a>
                                </li>
                                <li class="<?php echo($tab == 'integration' ? 'active' : ''); ?>">
                                    <a href="<?php echo admin_url('admin.php?page=wpsafelink&tab=integration'); ?>">
                                        <span class="wpsaf-menu-icon"><span class="dashicons dashicons-admin-plugins"></span></span>
                                        <div class="wpsaf-menu-content">
                                            <span class="wpsaf-menu-title">INTEGRATION</span>
                                            <span class="wpsaf-menu-desc">Adlinkfly and Download Web Integration</span>
                                        </div>
                                    </a>
                                </li>

                                <!-- Separator -->
                                <li class="wpsaf-separator">
                                    <span>Advanced Features</span>
                                </li>

                                <!-- Advanced Features (PRO) -->
                                <li class="<?php echo($tab == 'second_safelink' ? 'active' : ''); ?> <?php echo(!$has_pro_license ? 'premium-item' : ''); ?>">
                                    <a href="<?php echo admin_url('admin.php?page=wpsafelink&tab=second_safelink'); ?>">
                                        <span class="wpsaf-menu-icon"><span class="dashicons dashicons-networking"></span></span>
                                        <div class="wpsaf-menu-content">
                                            <span class="wpsaf-menu-title">SECOND SAFELINK<?php echo(!$has_pro_license ? '' : ''); ?></span>
                                            <span class="wpsaf-menu-desc">Multi-level protection</span>
                                        </div>
                                    </a>
                                </li>
                                <li class="<?php echo($tab == 'adlinkfly_pro' ? 'active' : ''); ?> <?php echo(!$has_pro_license ? 'premium-item' : ''); ?>">
                                    <a href="<?php echo admin_url('admin.php?page=wpsafelink&tab=adlinkfly_pro'); ?>">
                                        <span class="wpsaf-menu-icon"><span class="dashicons dashicons-money-alt"></span></span>
                                        <div class="wpsaf-menu-content">
                                            <span class="wpsaf-menu-title">ADLINKFLY<?php echo(!$has_pro_license ? '' : ''); ?></span>
                                            <span class="wpsaf-menu-desc">Monetization system</span>
                                        </div>
                                    </a>
                                </li>
                                <li class="<?php echo($tab == 'google_redirect' ? 'active' : ''); ?> <?php echo(!$has_pro_license ? 'premium-item' : ''); ?>">
                                    <a href="<?php echo admin_url('admin.php?page=wpsafelink&tab=google_redirect'); ?>">
                                        <span class="wpsaf-menu-icon"><span class="dashicons dashicons-randomize"></span></span>
                                        <div class="wpsaf-menu-content">
                                            <span class="wpsaf-menu-title">GOOGLE REDIRECT<?php echo(!$has_pro_license ? '' : ''); ?></span>
                                            <span class="wpsaf-menu-desc">Search redirect</span>
                                        </div>
                                    </a>
                                </li>
                                <li class="<?php echo($tab == 'multiple_pages' ? 'active' : ''); ?> <?php echo(!$has_pro_license ? 'premium-item' : ''); ?>">
                                    <a href="<?php echo admin_url('admin.php?page=wpsafelink&tab=multiple_pages'); ?>">
                                        <span class="wpsaf-menu-icon"><span class="dashicons dashicons-admin-page"></span></span>
                                        <div class="wpsaf-menu-content">
                                            <span class="wpsaf-menu-title">MULTIPLE PAGES<?php echo(!$has_pro_license ? '' : ''); ?></span>
                                            <span class="wpsaf-menu-desc">Multi-page safelinks</span>
                                        </div>
                                    </a>
                                </li>
                                <li class="<?php echo($tab == 'pro_tools' ? 'active' : ''); ?> <?php echo(!$has_pro_license ? 'premium-item' : ''); ?>">
                                    <a href="<?php echo admin_url('admin.php?page=wpsafelink&tab=pro_tools'); ?>">
                                        <span class="wpsaf-menu-icon"><span class="dashicons dashicons-hammer"></span></span>
                                        <div class="wpsaf-menu-content">
                                            <span class="wpsaf-menu-title">PRO TOOLS<?php echo(!$has_pro_license ? '' : ''); ?></span>
                                            <span class="wpsaf-menu-desc">Advanced utilities</span>
                                        </div>
                                    </a>
                                </li>
                            </ul>
                        </div>
                        <?php endif; ?>

                        <!-- Main Content Area -->
                        <div class="wpsaf-content-area">

                    <style>
                        /* No Sidebar Layout for Generate Link Tab */
                        .wpsaf-settings-container.no-sidebar .wpsaf-content-area {
                            width: 100%;
                            max-width: none;
                            margin-left: 0;
                        }

                        /* PRO Overlay Styles - Modern Banner Design */
                        .wpsafelink-pro-overlay-wrapper {
                            position: relative;
                        }
                        
                        .wpsafelink-pro-banner {
                            background: #7c3aed;
                            border-radius: 12px;
                            padding: 24px 32px;
                            margin: 20px 0px;
                            box-shadow: 0 4px 20px rgba(124, 58, 237, 0.25);
                            position: relative;
                            overflow: hidden;
                        }
                        
                        .wpsafelink-pro-banner::before {

                        }
                        
                        @keyframes shimmer {
                            0% { transform: rotate(0deg); }
                            100% { transform: rotate(360deg); }
                        }
                        
                        .wpsafelink-pro-banner-content {
                            position: relative;
                            z-index: 1;
                            display: flex;
                            align-items: center;
                            justify-content: space-between;
                            flex-wrap: wrap;
                            gap: 20px;
                        }
                        
                        .wpsafelink-pro-banner-left {
                            flex: 1;
                            min-width: 300px;
                        }
                        
                        .wpsafelink-pro-banner-header {
                            display: flex;
                            align-items: center;
                            gap: 12px;
                            margin-bottom: 12px;
                        }
                        
                        .wpsafelink-pro-star {
                            font-size: 48px;
                            animation: starPulse 2s ease-in-out infinite;
                        }
                        
                        @keyframes starPulse {
                            0%, 100% { transform: scale(1) rotate(0deg); }
                            50% { transform: scale(1.2) rotate(10deg); }
                        }
                        
                        .wpsafelink-pro-banner h2 {
                            color: #ffffff;
                            font-size: 24px;
                            font-weight: 600;
                            margin: 0;
                            display: flex;
                            align-items: center;
                            gap: 8px;
                        }
                        
                        .wpsafelink-pro-banner-description {
                            color: #f3e8ff;
                            font-size: 14px;
                            margin-bottom: 16px;
                            line-height: 1.5;
                        }
                        
                        .wpsafelink-pro-features-inline {
                            display: flex;
                            flex-wrap: wrap;
                            gap: 12px;
                            margin-top: 12px;
                        }
                        
                        .wpsafelink-pro-feature-pill {
                            background: rgba(255, 255, 255, 0.2);
                            backdrop-filter: blur(10px);
                            border: 1px solid rgba(255, 255, 255, 0.3);
                            border-radius: 20px;
                            padding: 6px 14px;
                            color: #ffffff;
                            font-size: 13px;
                            display: flex;
                            align-items: center;
                            gap: 6px;
                            transition: all 0.3s ease;
                        }
                        
                        .wpsafelink-pro-feature-pill:hover {
                            background: rgba(255, 255, 255, 0.3);
                            transform: translateY(-2px);
                        }
                        
                        .wpsafelink-pro-banner-right {
                            display: flex;
                            flex-direction: column;
                            align-items: center;
                            gap: 8px;
                        }
                        
                        .wpsafelink-upgrade-btn-top {
                            display: inline-flex;
                            align-items: center;
                            gap: 8px;
                            background: #ffffff;
                            color: #7c3aed;
                            padding: 12px 24px;
                            border-radius: 8px;
                            font-size: 15px;
                            font-weight: 600;
                            text-decoration: none;
                            transition: all 0.3s ease;
                            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
                        }
                        
                        .wpsafelink-upgrade-btn-top:hover {
                            transform: translateY(-2px);
                            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.2);
                            background: #f9fafb;
                            color: #6d28d9;
                        }
                        
                        .wpsafelink-trial-text {
                            color: #f3e8ff;
                            font-size: 11px;
                            font-style: italic;
                            opacity: 0.9;
                        }
                        
                        .wpsafelink-pro-content-locked {
                            opacity: 0.5;
                            pointer-events: none;
                            user-select: none;
                            position: relative;
                        }
                        
                        .wpsafelink-pro-content-locked::after {
                            content: '';
                            position: absolute;
                            top: 0;
                            left: 0;
                            right: 0;
                            bottom: 0;
                            background: transparent;
                            pointer-events: none;
                        }
                    </style>
                    <div class="wpsafelink-settings">
                        <?php
                        if ($tab === 'general') :
	                        // Merge General + Advertisement + Anti Adblock + Auto Generate Link
	                        $options = [];
	                        // General section
	                        $options = array_merge( $options, [
		                        'title_general_section' => [
			                        'title'       => 'General Settings',
			                        'type'        => 'title',
			                        'description' => ''
		                        ]
	                        ] );
	                        $options = array_merge( $options, $this->get_general_options( 'general' ) );
	                        // Advertisement section
	                        $options = array_merge( $options, [
		                        'title_advertisement_section' => [
			                        'title'       => 'Advertisement Settings',
			                        'type'        => 'title',
			                        'description' => ''
		                        ]
	                        ] );
	                        $options = array_merge( $options, $this->get_general_options( 'advertisement' ) );
	                        // Anti Adblock section
	                        $options = array_merge( $options, [
		                        'title_anti_adblock_section' => [
			                        'title'       => 'Anti Adblock Settings',
			                        'type'        => 'title',
			                        'description' => ''
		                        ]
	                        ] );
	                        $options = array_merge( $options, $this->get_general_options( 'anti-adblock' ) );

	                        // Auto Generate Link section
	                        $options = array_merge( $options, [
		                        'title_autogen_section' => [
			                        'title'       => 'Auto Generate Link',
			                        'type'        => 'title',
			                        'description' => ''
		                        ]
	                        ] );
	                        $options = array_merge( $options, $this->get_general_options( 'auto-generate-link' ) );
                            require_once wpsafelink_plugin_path() . '/views/settings/settings-general.php';
                        elseif ($tab === 'templates') :
	                        // Combine Templates and Styles into a single Templates tab
	                        $options = [];
	                        $options = array_merge( $options, $this->get_general_options( 'templates' ) );
	                        $options = array_merge( $options, $this->get_general_options( 'styles' ) );
	                        require_once wpsafelink_plugin_path() . '/views/settings/settings-general.php';
                        elseif ($tab === 'license') :
                            require_once wpsafelink_plugin_path() . '/views/settings/settings-license.php';
                        elseif ($tab === 'license-tab') :
                            // Build formatted_data from cached license (no API call)
                            if ( $check_license && isset( $license['data'] ) ) {
                                $license_data_obj = $license['data'];
                                $msg_data = is_array( $license_data_obj->msg ) ? $license_data_obj->msg : (array) $license_data_obj->msg;
                                $formatted_data = array(
                                    'message' => 'License is Active and Valid',
                                    'success' => true,
                                    'result'  => array(
                                        'license_key'   => substr( $msg_data['license'] ?? '', 0, 10 ) . '*********',
                                        'product'       => $msg_data['title'] ?? 'WP Safelink WordPress Plugin',
                                        'status'        => $msg_data['status'] ?? 'active',
                                        'domain'        => $msg_data['domain'] ?? str_replace( ['https://', 'http://'], '', home_url() ),
                                        'last_check'    => isset( $license_data_obj->last_access ) ? date( 'd M Y, H:i:s', strtotime( $license_data_obj->last_access ) ) : date( 'd M Y, H:i:s' ),
                                    )
                                );
                            } else {
                                $formatted_data = array(
                                    'message' => 'License is Invalid or Expired',
                                    'success' => false,
                                    'result'  => array()
                                );
                            }
                            require_once wpsafelink_plugin_path() . '/views/settings/settings-license-tab.php';
                        elseif ($tab === 'generate-link') :
	                        require_once wpsafelink_plugin_path() . '/views/settings/settings-generate-link.php';
                        elseif ( $tab === 'integration' ) :
	                        $options = $this->get_general_options( 'integration' );
	                        require_once wpsafelink_plugin_path() . '/views/settings/settings-integration.php';
                        elseif ( $tab === 'second_safelink' ) :
                            $options = $this->get_general_options( 'second_safelink' );
                            $is_pro_tab = true;
                            $pro_tab_name = 'Second Safelink';
                            require_once wpsafelink_plugin_path() . '/views/settings/settings-general.php';
                        elseif ( $tab === 'google_redirect' ) :
                            $options = $this->get_general_options( 'google_redirect' );
                            $is_pro_tab = true;
                            $pro_tab_name = 'Google Redirect';
                            require_once wpsafelink_plugin_path() . '/views/settings/settings-general.php';
                        elseif ( $tab === 'multiple_pages' ) :
                            $options = $this->get_general_options( 'multiple_pages' );
                            $is_pro_tab = true;
                            $pro_tab_name = 'Multiple Pages';
                            require_once wpsafelink_plugin_path() . '/views/settings/settings-general.php';
                        elseif ( $tab === 'adlinkfly_pro' ) :
                            $options = $this->get_general_options( 'adlinkfly_pro' );
                            $is_pro_tab = true;
                            $pro_tab_name = 'Adlinkfly PRO';
                            require_once wpsafelink_plugin_path() . '/views/settings/settings-general.php';
                        elseif ( $tab === 'pro_tools' ) :
                            // Handle import action if submitted
                            if ( isset( $_POST['action'] ) && $_POST['action'] == 'save' ) {
                                $post_options = $_POST['wpsafelink'] ?? array();
                                if ( ! empty( $post_options['import'] ) ) {
                                    global $wpsafelink_core;
                                    global $wpdb;
                                    $success = 0;

                                    $import = $post_options['import'];
                                    $import = $wpsafelink_core->decrypt_link( $import, true );
                                    $import = json_decode( $import, true );
                                    foreach ( $import as $line ) {
                                        $success += 1;
                                    }

                                    $data['import'] = '';
                                    update_option( $this->opt_setting_key, $data );

                                    $success_update  = true;
                                    $success_message = 'Successfully imported ' . $success . ' links';
                                    ?>
                                    <?php if ( $success_update ) : ?>
                                        <div id="setting-error-settings_updated" class="updated settings-error notice is-dismissible">
                                            <p><strong><?php echo $success_message; ?></strong></p>
                                            <button type="button" class="notice-dismiss"><span
                                                        class="screen-reader-text">Dismiss this notice.</span></button>
                                        </div>
                                    <?php endif; ?>
                                    <?php
                                }
                            }
                            $options = $this->get_general_options( 'pro_tools' );
                            $is_pro_tab = true;
                            $pro_tab_name = 'Pro Tools';
                            require_once wpsafelink_plugin_path() . '/views/settings/settings-general.php';
                            ?>
                        <?php endif; ?>

                        <?php do_action('wpsafelink_tab_content', $tab); ?>
                        </div><!-- .wpsaf-content-area -->
                    </div><!-- .wpsaf-settings-container -->
                <?php endif; ?>
            </div>


	        <?php
        }

        /**
         * Get general options
         *
         * @access  public
         * @return  array   Options list
         * @since   1.0.0
         */
        function get_general_options($type = 'general')
        {
	        $options = [];
            if ($type == 'general') {
                $options = array(
                    'auto_save_safelink' => array(
                        'id' => 'auto_save_safelink',
                        'title' => 'Auto Save Safelink',
                        'type' => 'checkbox',
                        'label' => 'Activate Auto Save Safelink',
                        'default' => 'no',
                        'help' => 'Automatically saves generated safelinks to the database for tracking and analytics. When enabled, you can view statistics like click counts, view counts, and conversion rates for each safelink in your dashboard.',
                        'help_title' => 'Auto Save Safelink Explained'
                    ),
                    'permalink' => array(
                        'id' => 'permalink',
                        'title' => 'Permalink Type',
                        'type' => 'select',
                        'label' => 'Permalink Type',
                        'options' => [
                            '1' => get_bloginfo('url') . '/[PARAMETER]/safelink_code',
                            '2' => get_bloginfo('url') . '/?[PARAMETER]=safelink_code',
                            '3' => get_bloginfo('url') . '/?safelink_code',
                        ],
                        'default' => '1',
                        'help' => '<strong>Choose your safelink URL structure:</strong><ul><li><strong>Mode 1 (Path):</strong> Clean URLs using path structure - Best for SEO</li><li><strong>Mode 2 (Query):</strong> Query parameter structure - Compatible with all servers</li><li><strong>Mode 3 (Raw):</strong> Direct query without parameter name - Shortest URLs</li></ul><p class="highlight">Mode 1 requires pretty permalinks to be enabled in WordPress settings.</p>',
                        'help_title' => 'Permalink Structure Options'
                    ),
                    'permalink_parameter' => array(
                        'id' => 'permalink_parameter',
                        'title' => 'Permalink Parameter',
                        'type' => 'text',
                        'label' => 'Permalink Parameter',
                        'default' => 'go',
                        'help' => 'Customize the URL parameter used in your safelinks. This defines how your links appear to visitors. For example, using "go" creates URLs like <code>/go/abc123</code> or <code>/?go=abc123</code>. You can use any word like "download", "link", "redirect", etc. Keep it short and SEO-friendly.',
                        'help_title' => 'Customizing Your URL Parameter'
                    ),
                    'content_method' => [
                        'id' => 'content_method',
                        'title' => 'Content Method',
                        'type' => 'select',
                        'label' => 'Content Method',
                        'options' => [
                            'random' => 'Random All Posts',
                            'selected' => 'Selected Posts',
                        ],
                        'default' => 'random',
                        'help' => 'Choose how content is displayed on your safelink pages. <strong>Random All Posts</strong> automatically selects a random post from your site to display alongside the safelink, keeping visitors engaged. <strong>Selected Posts</strong> allows you to specify exact posts to rotate through, giving you full control over what content appears.',
                        'help_title' => 'Content Display Strategy'
                    ],
                    'content_ids' => [
                        'id' => 'content_ids',
                        'title' => 'Content IDs',
                        'type' => 'textarea',
                        'label' => 'Content IDs',
                        'description' => 'Enter the post IDs, separated by new line',
                        'help' => 'Specify which posts to display on safelink pages when using "Selected Posts" mode. Enter one post ID per line. To find a post ID, go to Posts in your WordPress admin - the ID is shown in the URL when editing a post. Example:<br><code>142</code><br><code>256</code><br><code>389</code><br>The system will randomly select from these posts for each safelink view.',
                        'help_title' => 'Specifying Post IDs'
                    ]
                );
            } else if ($type == 'auto-generate-link') {
                $options = array(
                    'auto_convert_link' => array(
                        'id' => 'auto_convert_link',
                        'title' => 'Auto Convert Link',
                        'type' => 'checkbox',
                        'label' => 'Auto Convert Link',
                        'default' => 'no',
                        'help' => 'Automatically converts external links on your website to safelinks using JavaScript. This feature works by scanning your pages for links matching your domain rules and converting them to safelinks in real-time. Perfect for monetizing all external links without manually editing each one.',
                        'help_title' => 'How Auto Convert Works'
                    ),
                    'auto_convert_link_base_url' => array(
                        'id' => 'auto_convert_link_base_url',
                        'title' => 'Base URL',
                        'type' => 'text',
                        'default' => get_bloginfo('url'),
                        'readonly' => true,
                        'help' => 'This is your site\'s base URL used for generating safelinks. It\'s automatically set to your WordPress site URL and cannot be changed. All safelinks will be created under this domain, ensuring they work correctly with your WordPress installation.',
                        'help_title' => 'Safelink Base URL'
                    ),
                    'auto_convert_link_method' => [
                        'title' => 'Auto Convert Link Method',
                        'id' => 'auto_convert_link_method',
                        'type' => 'select',
                        'options' => [
                            'include' => 'Include Domain',
                            'exclude' => 'Exclude Domain',
                        ],
                        'description' => 'Select the method for auto convert link',
                        'default' => 'include',
                        'help' => '<strong>Include Domain:</strong> Only convert links from domains in your list. Use this to monetize specific affiliate or external sites.<br><br><strong>Exclude Domain:</strong> Convert all external links EXCEPT domains in your list. Use this to avoid converting trusted sites like payment gateways, social media, or your own domains.<br><br><span class="highlight">Tip: Start with Include mode for better control.</span>',
                        'help_title' => 'Domain Filtering Method'
                    ],
                    'domain_list' => [
                        'title' => 'Domain List',
                        'id' => 'domain_list',
                        'type' => 'textarea',
                        'description' => 'Enter the domain list, separated by new line',
                        'help' => 'Enter domain names to include or exclude from auto-conversion. One domain per line, without http:// or https://. The system automatically handles www and non-www versions. Example:<br><code>mediafire.com</code><br><code>mega.nz</code><br><code>drive.google.com</code><br><code>dropbox.com</code><br><br><strong>Pro tip:</strong> Use subdomains for precise control (e.g., <code>download.example.com</code>).',
                        'help_title' => 'Managing Domain Lists'
                    ]
                );
            } else if ($type == 'templates') {
                $templates = [];
                $temps = glob(wpsafelink_plugin_path() . '/template/*.php');
	            $default_template = 'installed';
                $templates['installed'] = 'Installed WordPress Theme';
                foreach ($temps as $t) {
                    $t = explode('/', $t);
                    $t = $t[count($t) - 1];
                    $t = str_replace('.php', '', $t);
                    $templates[$t] = $t;
                }

                $options = array(
	                'title_templates_settings' => [
		                'title'       => 'Template Settings',
		                'type'        => 'title',
		                'description' => 'Choose the safelink template and adjust its behavior.'
	                ],
                    'template' => array(
                        'title' => 'Template',
                        'id' => 'template',
                        'type' => 'select',
                        'options' => $templates,
                        'default' => $default_template,
                        'help' => 'Select the visual template for your safelink pages. Each template offers different layouts and features:<br><br><strong>Template1:</strong> Clean, minimal design with centered content<br><strong>Template2:</strong> Integrated with your theme header/footer<br><strong>Template3:</strong> Full-page design with sidebar support<br><strong>Template4:</strong> Modern card-based layout<br><br><span class="highlight">Note: Template2 and Template3 require theme integration.</span>',
                        'help_title' => 'Choosing a Template'
                    ),
                    'skip_verification' => array(
	                    'id'    => 'skip_verification',
	                    'title' => 'Skip Verification',
	                    'type'  => 'checkbox',
	                    'label' => 'Skip Verification',
                        'default' => 'no',
	                    'help' => 'Enable this to skip the human verification step entirely. Users will still see ads and countdown timer but won\'t need to click verification buttons. Use this for trusted traffic sources or when you want a simpler user experience. Only available with Template3.',
	                    'help_title' => 'Bypassing Verification'
                    ),'auto_integration_enable' => [
                        'id' => 'auto_integration_enable',
                        'title' => 'Enable Auto Integration',
                        'type' => 'checkbox',
                        'label' => 'Automatically integrate safelink into theme',
                        'default' => '1',
                        'help' => 'Enable this to automatically inject safelink functions into your theme without editing template files. This eliminates the need to manually add newwpsafelink_top() and newwpsafelink_bottom() to your theme files.',
                        'help_title' => 'Automatic Theme Integration'
                    ],
                    'auto_integration_top_placement' => [
                        'id' => 'auto_integration_top_placement',
                        'title' => 'Top Section Placement',
                        'type' => 'select',
                        'options' => [
                            'wp_body_open' => 'After Body Tag (wp_body_open)',
                            'before_title' => 'Before Post Title',
                            'after_title' => 'After Post Title',
                            'content_start' => 'Start of Content'
                        ],
                        'default' => 'after_title',
                        'description' => 'Where to place the top safelink section',
                        'help' => '<strong>After Body Tag:</strong> Places content right after the opening &lt;body&gt; tag.<br><strong>Before Title:</strong> Shows above the post/page title.<br><strong>After Title:</strong> Shows immediately after the title (recommended).<br><strong>Start of Content:</strong> Shows at the beginning of the main content area.',
                        'help_title' => 'Top Section Positioning'
                    ],
                    'auto_integration_bottom_placement' => [
                        'id' => 'auto_integration_bottom_placement',
                        'title' => 'Bottom Section Placement',
                        'type' => 'select',
                        'options' => [
                            'wp_footer' => 'Footer (wp_footer)',
                            'content_end' => 'End of Content',
                            'after_content' => 'After Content'
                        ],
                        'default' => 'content_end',
                        'description' => 'Where to place the bottom safelink section',
                        'help' => '<strong>Footer:</strong> Places content in the footer scripts area.<br><strong>End of Content:</strong> Shows at the end of the main content (recommended).<br><strong>After Content:</strong> Shows after the entire content area.',
                        'help_title' => 'Bottom Section Positioning'
                    ],
                    'auto_integration_priority' => [
                        'id' => 'auto_integration_priority',
                        'title' => 'Hook Priority',
                        'type' => 'text',
                        'default' => '10',
                        'description' => 'WordPress hook priority (lower = earlier)',
                        'help' => 'Controls when the safelink content is added relative to other plugins/themes. Lower numbers execute first (1-9), standard is 10, higher numbers execute later (11-999). Adjust if content appears in wrong position.',
                        'help_title' => 'Execution Priority'
                    ],'title_styles_action'   => [
			            'title'       => 'Action Button',
			            'type'        => 'title',
			            'description' => 'Configure styles for action buttons (or image-based buttons).'
                    ],
		            'generate_manual_scroll' => [
			            'id'      => 'generate_manual_scroll',
			            'title'   => 'Manual Scroll to Get Link',
			            'type'    => 'checkbox',
			            'label'   => 'Require users to manually scroll to the link section',
			            'default' => 'yes',
			            'help' => 'When enabled, users must manually scroll down to find the download button after the timer expires. This increases page engagement time and ad visibility. The auto-scroll is disabled, giving you more control over user flow and potentially higher ad revenue.',
			            'help_title' => 'Scroll Behavior Control'
		            ],
                    'action_button' => [
                        'id' => 'action_button',
                        'title' => 'Action Button Type',
                        'type' => 'select',
                        'options' => [
                            'button' => 'Button',
                            'image' => 'Image'
                        ],
                        'help' => '<strong>Button:</strong> Text-based buttons with customizable labels. Clean, accessible, and fast-loading.<br><br><strong>Image:</strong> Custom image buttons for unique branding. Upload your own button graphics to match your site design perfectly. Great for creating eye-catching CTAs.',
                        'help_title' => 'Button Display Options',
                        'default' => 'button'
                    ],
                    'action_button_image_1' => [
                        'id' => 'action_button_image_1',
                        'title' => 'Image Button (Human Verification)',
                        'type' => 'media',
                        'default' => wpsafelink_plugin_url() . '/assets/human-verification4.png',
                        'help' => 'Custom image for the human verification button. Upload branded buttons that match your site design. Recommended: 250x60px PNG with transparent background. This appears first to verify users are human.',
                        'help_title' => 'Verification Button Image'
                    ],
                    'action_button_image_2' => [
                        'id' => 'action_button_image_2',
                        'title' => 'Image Button 1 (Generate Link)',
                        'type' => 'media',
                        'default' => wpsafelink_plugin_url() . '/assets/generate4.png',
                        'help' => 'Image for the link generation button (shown after verification). Use eye-catching designs with clear call-to-action text embedded in the image. This button triggers the main action users came for.',
                        'help_title' => 'Generate Button Graphic'
                    ],
                    'action_button_image_3' => [
                        'id' => 'action_button_image_3',
                        'title' => 'Image Button 2 (Please Wait)',
                        'type' => 'media',
                        'default' => wpsafelink_plugin_url() . '/assets/wait4.png',
                        'help' => 'Loading state image shown while processing the request. Should visually indicate waiting/loading. Consider animated GIFs or images with progress indicators for better user feedback.',
                        'help_title' => 'Loading State Visual'
                    ],
                    'action_button_image_4' => [
                        'id' => 'action_button_image_4',
                        'title' => 'Image Button 3 (Target Link)',
                        'type' => 'media',
                        'default' => wpsafelink_plugin_url() . '/assets/target4.png',
                        'help' => 'Final button image when the download link is ready. Make it prominent and exciting - this is the payoff moment. Green colors or download icons work well. Should clearly communicate "click here for your download".',
                        'help_title' => 'Final Download Button Image'
                    ],
                    'action_button_text_1' => [
                        'id' => 'action_button_text_1',
                        'title' => 'Button Text (Human Verification)',
                        'type' => 'text',
                        'default' => 'IM NOT ROBOT',
                        'help' => 'Text for the first verification button. Keep it clear and action-oriented. Examples: "Verify Human", "I\'m Human", "Click to Verify", "Start Verification". Uppercase text can increase clicks.',
                        'help_title' => 'Verification Button Label'
                    ],
                    'action_button_text_2' => [
                        'id' => 'action_button_text_2',
                        'title' => 'Button Text 1 (Generate Link)',
                        'type' => 'text',
                        'default' => 'Scroll Down',
                        'help' => 'Text after verification passed. Tell users what to do next. The "2X" indicates double-click protection against bots. Examples: "GENERATE DOWNLOAD LINK", "CREATE YOUR LINK", "GET ACCESS NOW".',
                        'help_title' => 'Generate Link Button Text'
                    ],
                    'action_button_text_3' => [
                        'id' => 'action_button_text_3',
                        'title' => 'Button Text 2 (Please Wait)',
                        'type' => 'text',
                        'default' => 'PLEASE WAIT ...',
                        'help' => 'Loading state text while processing. Shows users something is happening. Keep it short and reassuring. Examples: "PROCESSING...", "LOADING...", "ALMOST READY...", "PREPARING LINK...".',
                        'help_title' => 'Loading State Message'
                    ],
                    'action_button_text_4' => [
                        'id' => 'action_button_text_4',
                        'title' => 'Button Text 3 (Target Link)',
                        'type' => 'text',
                        'default' => 'DOWNLOAD LINK',
                        'help' => 'Final button text when link is ready. This is the call-to-action users have been waiting for. Make it exciting! Examples: "DOWNLOAD NOW", "GET YOUR FILE", "ACCESS LINK", "CLICK HERE TO DOWNLOAD".',
                        'help_title' => 'Final CTA Button Text'
                    ],
                    'title_template_behavior'  => [
		                'title'       => 'Behavior',
		                'type'        => 'title',
		                'description' => ''
	                ],
                    'timer_style' => [
                        'id' => 'timer_style',
                        'title' => 'Timer Display Style',
                        'type' => 'select',
                        'options' => [
                            'text' => 'Text Timer',
                            'countdown' => 'Countdown Circle'
                        ],
                        'description' => 'Choose how the countdown timer appears to visitors',
                        'default' => 'text',
                        'help' => '<strong>Text Timer:</strong> Simple text countdown that shows remaining seconds. Clean and minimal, works well with all templates.<br><br><strong>Countdown Circle:</strong> Animated circular progress indicator with visual feedback. More engaging and modern, perfect for keeping visitor attention during wait time.<br><br>The countdown ensures ads are viewed and prevents bot abuse.',
                        'help_title' => 'Timer Visual Styles'
                    ],
                    'time_delay' => [
                        'id' => 'time_delay',
                        'title' => 'Time Delay',
                        'type' => 'text',
                        'description' => 'Time delay in seconds',
                        'default' => '5',
                        'help' => 'Set how long visitors must wait before accessing the link (in seconds). Recommended values:<br><br><strong>3-5 seconds:</strong> Quick access, minimal friction<br><strong>10-15 seconds:</strong> Standard wait, good for ad revenue<br><strong>20-30 seconds:</strong> Extended viewing, maximum ad exposure<br><br><span class="highlight">Tip: Balance user experience with monetization goals.</span>',
                        'help_title' => 'Setting Wait Times'
                    ],
                    'time_delay_message' => [
                        'id' => 'time_delay_message',
                        'title' => 'Time Delay Message',
                        'type' => 'textarea',
                        'description' => '*Use syntax <code>{time}</code> HTML Support',
                        'default' => 'Thank you for your visit. Your links will be created in {time} seconds.',
                        'help' => 'Customize the message shown during countdown. Use <code>{time}</code> to display remaining seconds dynamically. HTML is supported for formatting. Examples:<br><br><code>Please wait {time} seconds...</code><br><code>Your download starts in <strong>{time}</strong> seconds!</code><br><code>Loading content... {time}s remaining</code><br><br>Keep messages friendly and informative.',
                        'help_title' => 'Custom Countdown Messages'
                    ],
                    'countdown_color_start' => [
                        'id' => 'countdown_color_start',
                        'title' => 'Countdown Start Color',
                        'type' => 'color',
                        'description' => 'Color when countdown starts (applies to countdown circle style)',
                        'default' => '#41b883',
                        'help' => 'Set the initial color of the countdown circle. This color appears when the timer starts. Green shades suggest "go" or "ready", creating a positive user experience. You can match this to your brand colors for consistency.',
                        'help_title' => 'Start Color Psychology'
                    ],
                    'countdown_color_warning' => [
                        'id' => 'countdown_color_warning',
                        'title' => 'Countdown Warning Color',
                        'type' => 'color',
                        'description' => 'Color when time is running out (last 10 seconds)',
                        'default' => '#ffa500',
                        'help' => 'Color that appears as time runs low (last 10 seconds). Orange/yellow shades create urgency without alarm. This transition helps users understand the timer is progressing and prepares them for the link to appear soon.',
                        'help_title' => 'Warning Color Timing'
                    ],
                    'countdown_color_alert' => [
                        'id' => 'countdown_color_alert',
                        'title' => 'Countdown Alert Color',
                        'type' => 'color',
                        'description' => 'Color for final seconds (last 5 seconds)',
                        'default' => '#ff0000',
                        'help' => 'Final countdown color (last 5 seconds). Red typically signals "almost there" and creates anticipation. This visual cue helps users prepare to click when the link becomes available.',
                        'help_title' => 'Alert Color Impact'
                    ],
                    'countdown_stroke_width' => [
                        'id' => 'countdown_stroke_width',
                        'title' => 'Circle Stroke Width',
                        'type' => 'select',
                        'options' => [
                            '2' => 'Ultra Thin',
                            '4' => 'Thin',
                            '7' => 'Normal',
                            '10' => 'Bold',
                            '15' => 'Extra Bold'
                        ],
                        'description' => 'Thickness of the countdown circle border',
                        'default' => '2',
                        'help' => 'Controls the thickness of the animated circle border. <strong>Ultra Thin/Thin:</strong> Modern, minimal look. <strong>Normal:</strong> Balanced visibility. <strong>Bold/Extra Bold:</strong> High impact, draws attention. Match this to your overall design aesthetic.',
                        'help_title' => 'Circle Border Thickness'
                    ],
                    'countdown_size' => [
                        'id' => 'countdown_size',
                        'title' => 'Countdown Circle Size',
                        'type' => 'select',
                        'options' => [
                            '150' => 'Small (150px)',
                            '200' => 'Medium (200px)', 
                            '250' => 'Large (250px)',
                            '300' => 'Extra Large (300px)'
                        ],
                        'description' => 'Size of the countdown circle timer',
                        'default' => '250',
                        'help' => 'Diameter of the countdown circle in pixels. <strong>Small:</strong> Subtle, doesn\'t dominate the page. <strong>Medium:</strong> Good balance for most layouts. <strong>Large/Extra Large:</strong> Maximum visibility, perfect for mobile users or when timer is the main focus.',
                        'help_title' => 'Optimal Circle Sizing'
                    ],
                    'countdown_show_text' => [
                        'id' => 'countdown_show_text',
                        'title' => 'Show Timer Text with Circle',
                        'type' => 'checkbox',
                        'label' => 'Display timer numbers inside the circle',
                        'default' => 'yes',
                        'help' => 'Display numeric countdown inside the circle animation. Enable for clarity - users see exact seconds remaining. Disable for a cleaner, more minimal aesthetic where the visual progress is enough.',
                        'help_title' => 'Timer Text Display'
                    ],
                    'title_human_verification' => [
                        'title' => 'Human Verification',
                        'type' => 'title',
                        'description' => 'Settings related to human verification methods.',
                        'class' => 'human-verification-section'
                    ],
                    'enable_human_verification' => array(
                        'id'    => 'enable_human_verification',
                        'title' => 'Human Verification',
                        'type'  => 'checkbox',
                        'label' => 'Enable Human Verification',
                        'default' => 'yes',
                        'help' => 'When enabled, users must click a button to verify they are human after the timer expires. This adds an extra layer of bot protection and ensures real user engagement. The button text can be customized below. Works best with Template3.',
                        'help_title' => 'Human Verification Explained'
                    ),
                    'verification_homepage' => array(
	                    'id'    => 'verification_homepage',
	                    'title' => 'Verification on Homepage',
	                    'type'  => 'checkbox',
	                    'label' => 'Verification on Homepage',
                        'default' => 'no',
	                    'help' => 'When enabled, visitors are redirected to your homepage after completing verification instead of directly to the target link. This increases homepage views and allows you to display additional content or offers before the final redirect. Works best with Template2.',
	                    'help_title' => 'Homepage Verification Flow'
                    ),
                    'method_human_verification' => [
                        'id' => 'method_human_verification',
                        'title' => 'Verification Method',
                        'type' => 'select',
                        'label' => 'Verification Method',
                        'options' => [
                            'button' => 'Button Click',
                            'recaptcha' => 'ReCAPTCHA',
                            'hcaptcha' => 'hCaptcha',
                        ],
                        'default' => 'button',
                        'help' => '<strong>Button Click:</strong> Users click a button to verify they are human after the timer ends.<br><strong>CAPTCHA:</strong> Users complete a CAPTCHA challenge (reCAPTCHA or hCaptcha) to verify they are human.<br><br><span class="highlight">Note: CAPTCHA requires additional setup below.</span>',
                        'help_title' => 'Choosing a Verification Method'
                    ],
                    'recaptcha_title' => [
                        'title' => 'reCAPTCHA',
                        'type' => 'title',
                        'description' => 'Get your site key and secret key from <a href="https://www.google.com/recaptcha/admin/" target="_blank">Google reCAPTCHA</a>'
                    ],
                    'recaptcha_site_key' => array(
                        'id' => 'recaptcha_site_key',
                        'title' => 'Site Key',
                        'type' => 'text',
                        'label' => 'Site Key',
                        'help' => 'Your public reCAPTCHA site key. This is safe to expose in your HTML/JavaScript. To get your keys: 1) Visit Google reCAPTCHA admin, 2) Register your site, 3) Choose reCAPTCHA v2, 4) Copy the Site Key here.',
                        'help_title' => 'reCAPTCHA Site Key'
                    ),
                    'recaptcha_secret_key' => array(
                        'id' => 'recaptcha_secret_key',
                        'title' => 'Secret Key',
                        'type' => 'text',
                        'label' => 'Secret Key',
                        'help' => 'Your private reCAPTCHA secret key for server-side validation. Keep this confidential - never expose it in frontend code. This key verifies that captcha responses are legitimate. Store it securely.',
                        'help_title' => 'reCAPTCHA Secret Key Security'
                    ),
                    'recaptcha_label' => array(
                        'id' => 'recaptcha_label',
                        'title' => 'reCAPTCHA Alert Verification Text',
                        'type' => 'text',
                        'label' => 'Label',
                        'default' => 'Please complete reCAPTCHA verification',
                        'help' => 'Custom message shown when users skip or fail the reCAPTCHA. Keep it friendly and clear. Examples: "Please verify you\'re human", "Complete the security check to continue", "One more step - verify captcha".',
                        'help_title' => 'Custom CAPTCHA Messages'
                    ),
                    'hcaptcha_title' => [
                        'title' => 'hCaptcha',
                        'type' => 'title',
                        'description' => 'Get your site key and secret key from <a href="https://www.hcaptcha.com" target="_blank">hCaptcha</a>'
                    ],
                    'hcaptcha_site_key' => array(
                        'id' => 'hcaptcha_site_key',
                        'title' => 'Site Key',
                        'type' => 'text',
                        'label' => 'Site Key',
                        'help' => 'Your hCaptcha sitekey for frontend integration. Get it from hCaptcha dashboard after registering your site. hCaptcha is GDPR-compliant and can earn you cryptocurrency for each solve.',
                        'help_title' => 'hCaptcha Site Key Setup'
                    ),
                    'hcaptcha_secret_key' => array(
                        'id' => 'hcaptcha_secret_key',
                        'title' => 'Secret Key',
                        'type' => 'text',
                        'label' => 'Secret Key',
                        'help' => 'Your hCaptcha secret for backend verification. Never share this key or commit it to version control. hCaptcha uses this to validate responses and credit your account with HCT tokens.',
                        'help_title' => 'hCaptcha Secret Security'
                    ),
                    'hcaptcha_label' => array(
                        'id' => 'hcaptcha_label',
                        'title' => 'hCaptcha Alert Verification Text',
                        'type' => 'text',
                        'label' => 'Label',
                        'default' => 'Please complete hCaptcha verification',
                        'help' => 'Alert message for hCaptcha verification. Customize based on your audience. Professional sites: "Security verification required". Casual sites: "Quick bot check!" Keep it short and action-oriented.',
                        'help_title' => 'hCaptcha Alert Customization'
                    ),
                );
            } else if ( $type == 'styles' ) {
	            $options = array(
		            'title_styles_appearance'  => [
			            'title'       => 'Appearance',
			            'type'        => 'title',
			            'description' => 'Customize the look and feel of WP Safelink components.'
		            ],
		            'style_logo'               => [
			            'id'          => 'style_logo',
			            'title'       => 'Logo',
			            'type'        => 'media',
			            'description' => 'Upload a logo used by templates and components.',
			            'default'     => wpsafelink_plugin_url() . '/assets/logo.png',
			            'help' => 'Upload your brand logo to display on safelink pages. Recommended size: 200x60px or similar aspect ratio. Supports PNG, JPG, and SVG formats. The logo helps build trust and brand recognition while visitors wait.',
			            'help_title' => 'Brand Logo Settings'
		            ],
		            'style_font_family'        => [
			            'id'      => 'style_font_family',
			            'title'   => 'Font Family',
			            'type'    => 'select',
			            'options' => [
				            'system-ui' => 'System UI (Default)',
				            'inter'     => 'Inter (System fallback)',
				            'arial'     => 'Arial',
				            'roboto'    => 'Roboto',
				            'georgia'   => 'Georgia',
			            ],
			            'default' => 'system-ui',
			            'help' => 'Choose the font for your safelink pages. <strong>System UI:</strong> Uses visitor\'s system font for fastest loading. <strong>Inter:</strong> Modern, clean web font. <strong>Arial/Roboto:</strong> Classic web-safe choices. <strong>Georgia:</strong> Serif font for readability.',
			            'help_title' => 'Typography Selection'
		            ],
		            'style_font_size'          => [
			            'id'          => 'style_font_size',
			            'title'       => 'Base Font Size',
			            'type'        => 'text',
			            'description' => 'CSS size value (e.g. 16px)',
			            'default'     => '16px',
			            'help' => 'Set the base text size for safelink pages. Standard is 16px. Use 14px for more compact layouts, 18px for better readability on mobile. You can use px, em, or rem units. All other text scales proportionally from this base.',
			            'help_title' => 'Font Size Guidelines'
		            ],
		            'style_text_color'         => [
			            'id'      => 'style_text_color',
			            'title'   => 'Text Color',
			            'type'    => 'color',
			            'default' => '#111827',
			            'help' => 'Primary text color for all content on safelink pages. Dark colors (#111827, #000000) provide best readability on light backgrounds. Ensure sufficient contrast for accessibility (WCAG AA standard recommends 4.5:1 ratio).',
			            'help_title' => 'Text Color Accessibility'
		            ],
		            'style_muted_color'        => [
			            'id'      => 'style_muted_color',
			            'title'   => 'Muted Text Color',
			            'type'    => 'color',
			            'default' => '#6b7280',
			            'help' => 'Secondary text color for less important information like hints, timestamps, or metadata. Should be lighter than primary text but still readable. Gray shades work well (#6b7280, #9ca3af).',
			            'help_title' => 'Secondary Text Styling'
		            ],
		            'style_border_color'       => [
			            'id'      => 'style_border_color',
			            'title'   => 'Border Color',
			            'type'    => 'color',
			            'default' => '#e5e7eb',
			            'help' => 'Color for dividers, boxes, and element borders. Light grays (#e5e7eb) create subtle separation without being distracting. Match this to your theme\'s border colors for consistency.',
			            'help_title' => 'Border Visual Hierarchy'
		            ],
		            'style_background_color'   => [
			            'id'      => 'style_background_color',
			            'title'   => 'Background Color',
			            'type'    => 'color',
			            'default' => '#ffffff',
			            'help' => 'Main background color for safelink pages. White (#ffffff) is standard for readability. Light grays (#f9fafb) reduce eye strain. Dark backgrounds require inverting all text colors.',
			            'help_title' => 'Background Color Impact'
		            ],
		            'style_accent_color'       => [
			            'id'      => 'style_accent_color',
			            'title'   => 'Primary (Accent) Color',
			            'type'    => 'color',
			            'default' => '#1ABC9C',
			            'help' => 'Brand color for buttons, links, and interactive elements. This should match your site\'s primary color. Blue (#1ABC9C) suggests trust and action. Green implies success. Red creates urgency.',
			            'help_title' => 'Brand Color Psychology'
		            ],
		            'style_accent_hover_color' => [
			            'id'      => 'style_accent_hover_color',
			            'title'   => 'Primary Hover Color',
			            'type'    => 'color',
			            'default' => '#16A085',
			            'help' => 'Color when hovering over buttons and links. Should be a darker shade of your accent color for clear visual feedback. This helps users understand what\'s clickable and improves UX.',
			            'help_title' => 'Hover State Feedback'
		            ],
		            'style_radius' => [
			            'id'          => 'style_radius',
			            'title'       => 'Border Radius',
			            'type'        => 'text',
			            'description' => 'CSS size value (e.g. 8px)',
			            'default'     => '10px',
			            'help' => 'Roundness of corners for buttons, boxes, and containers. 0px = sharp corners (formal). 4-8px = slightly rounded (modern). 10-16px = rounded (friendly). 999px = pill-shaped buttons.',
			            'help_title' => 'Corner Radius Design Impact'
		            ],
                );
            } else if ($type == 'advertisement') {
                $options = array(
                    'advertisement_top_1' => array(
                        'title' => 'Advertisement Top (Before Button)',
                        'id' => 'advertisement_top_1',
                        'type'    => 'textarea',
                        'default' => '<a href="https://themeson.com" target="_blank"><img src="https://placehold.co/300x250" /></a>',
                        'help' => 'Ad space above the action button. Prime visibility location - users see this while waiting. Recommended sizes: 300x250 (rectangle), 728x90 (banner), or responsive ads. Supports HTML, JavaScript, and iframe ad codes from any network.',
                        'help_title' => 'Top Advertisement Placement'
                    ),
                    'advertisement_top_2' => array(
                        'title' => 'Advertisement Top (After Button)',
                        'id' => 'advertisement_top_2',
                        'type'    => 'textarea',
                        'default' => '<a href="https://themeson.com" target="_blank"><img src="https://placehold.co/300x250" /></a>',
                        'help' => 'Secondary ad position below the action button but still above the fold. Great for doubling revenue without cluttering. Users often look here after clicking the button. Same format support as other ad slots.',
                        'help_title' => 'Secondary Top Ad Space'
                    ),
                    'advertisement_bottom_full_screen' => array(
	                    'title'   => 'Advertisement Bottom Full Screen',
	                    'id'      => 'advertisement_bottom_full_screen',
	                    'type'    => 'checkbox',
	                    'label'   => 'Enable Advertisement Bottom Full Screen',
	                    'default' => 'yes',
	                    'help' => 'Expand bottom advertisements to full width for maximum impact. When enabled, bottom ads break out of content containers for better visibility. Especially effective for large banner ads or native advertising grids.',
	                    'help_title' => 'Full-Width Ad Display'
                    ),
                    'advertisement_bottom_1' => array(
                        'title' => 'Advertisement Bottom (Before Button)',
                        'id' => 'advertisement_bottom_1',
                        'type'    => 'textarea',
                        'default' => '<a href="https://themeson.com" target="_blank"><img src="https://placehold.co/300x600" /></a>',
                        'help' => 'Bottom section ad before the download button. Perfect for tall formats like 300x600 skyscrapers or 160x600 wide skyscrapers. Users scroll past this to reach their link, ensuring views. High-performing position for affiliate offers.',
                        'help_title' => 'Bottom Pre-Button Ad'
                    ),
                    'advertisement_bottom_2' => array(
                        'title' => 'Advertisement Bottom (After Button)',
                        'id' => 'advertisement_bottom_2',
                        'type'    => 'textarea',
                        'default' => '<a href="https://themeson.com" target="_blank"><img src="https://placehold.co/300x600" /></a>',
                        'help' => 'Final ad position after everything else. While lowest priority, still valuable for users who scroll to explore. Good for related content, newsletter signups, or lower-paying ad networks. Helps maximize page RPM.',
                        'help_title' => 'Bottom Post-Button Ad'
                    ),
                );
            } else if ($type == 'anti-adblock') {
                $options = array(
                    'anti_adblock' => array(
                        'title' => 'Anti Adblock',
                        'id' => 'anti_adblock',
                        'type' => 'checkbox',
                        'label' => 'Enable Anti Adblock',
                        'help' => 'Detect and prevent users with ad blockers from accessing your safelinks. When enabled, users will see a message asking them to disable their ad blocker before they can proceed. This ensures your monetization efforts are not bypassed and helps maintain your revenue stream.',
                        'help_title' => 'Anti-Adblock Protection'
                    ),
                    'anti_adblock_header_1' => [
	                    'id' => 'anti_adblock_header_1',
                        'title' => 'Header text 1',
                        'type' => 'textarea',
                        'default' => 'Adblock Detected',
                        'help' => 'Main headline shown when ad blocker is detected. Keep it short and factual. Examples: "Ad Blocker Active", "Please Support Us", "Content Blocked". This grabs attention and states the issue clearly.',
                        'help_title' => 'Anti-Adblock Main Message'
                    ],
                    'anti_adblock_header_2' => [
	                    'id' => 'anti_adblock_header_2',
                        'title' => 'Header text 2',
                        'type' => 'textarea',
                        'default' => 'Its seems you are using an ad blocker/VPN/Proxy/Custom DNS. Please disable it to support our website and access the link.',
                        'help' => 'Instructions for users with ad blockers. Be polite but clear about what they need to do. Examples: "Disable your ad blocker to continue", "We rely on ads to keep this service free", "Whitelist our site to access downloads". Explain the value exchange.',
                        'help_title' => 'Anti-Adblock Instructions'
                    ],
                );
            } else if ($type == 'adlinkfly') {
	            $options = array(
		            'title_adlinkfly'      => [
			            'title'       => 'Adlinkfly',
			            'type'        => 'title',
			            'description' => 'For demo purpose you can check this link <a href="https://demo-adlinkfly.themeson.com" target="_blank">https://demo-adlinkfly.themeson.com</a>'
		            ],
		            'adlinkfly'            => array(
			            'title' => 'Adlinkfly',
			            'id'    => 'adlinkfly',
			            'type'  => 'checkbox',
			            'label' => 'Enable Adlinkfly',
			            'help' => 'Enable dual monetization by routing links through both WP Safelink and Adlinkfly. Users first see your WP Safelink page with ads, then get redirected through Adlinkfly for additional revenue. This can double your earnings per click.',
			            'help_title' => 'Dual Monetization Strategy'
		            ),
		            'adlinkfly_url'        => [
			            'id'          => 'adlinkfly_url',
			            'title'       => 'Adlinkfly URL',
			            'type'        => 'text',
			            'description' => 'example : https://redir.themeson.com',
			            'help' => 'Enter your Adlinkfly installation URL. Must be a valid Adlinkfly instance configured to accept API requests from this domain. Test the connection after setup to ensure proper integration.',
			            'help_title' => 'Adlinkfly Endpoint Configuration'
		            ],
		            'adlinkfly_secret_key' => [
			            'id'          => 'adlinkfly_secret_key',
			            'title'       => 'Adlinkfly Secret Key',
			            'type'        => 'text',
			            'default'     => md5( get_bloginfo( 'url' ) ),
			            'readonly'    => true,
			            'description' => 'Adlinkfly Secret Key is only for <strong><a href="https://themeson.com/safelink/" target="_blank">PRO VERSION</a></strong>. Its easy for integration and more secure for your safelink and tutorial will be available on <strong><a href="https://themeson.com/safelink/" target="_blank">PRO VERSION</a></strong> Configuration.',
			            'help' => 'Auto-generated security key for Adlinkfly API authentication. PRO version provides enhanced security with encrypted communication, automatic key rotation, and advanced link tracking between both platforms.',
			            'help_title' => 'API Security Key'
		            ]
	            );
            } else if ( $type == 'setup' ) {
	            $valid_header_integration = $this->check_theme_integration( 'header' );
	            $valid_footer_integration = $this->check_theme_integration( 'footer' );
	            $options = array(
		            'title_installation' => [
			            'title'       => 'Installation',
			            'type'        => 'title',
			            'description' => 'Follow these steps to properly integrate WP Safelink into your WordPress theme. For detailed video tutorials and documentation, visit our <a href="https://kb.themeson.com/knowledge-base/integrate-wp-safelink-to-custom-theme" target="_blank">knowledge base</a>. If you need help, please visit <a href="https://support.themeson.com" target="_blank">https://support.themeson.com</a>.'
		            ],
		            'header_code'        => [
			            'id'          => 'header_code',
			            'title'       => 'Header Code',
			            'type'        => 'textarea',
			            'description' => 'Step 1: Open your theme\'s header.php file and paste this code just before the closing &lt;/head&gt; tag. This adds required scripts and styles.<br/><strong style="color:' . ( $valid_header_integration ? 'green' : 'red' ) . '">' . ( $valid_header_integration ? 'Header code integration verified!' : 'Header code integration required - follow Step 1' ) . '</strong>',
			            'default'     => "&lt;?php if(function_exists('newwpsafelink_top')) newwpsafelink_top();?&gt;",
			            'disabled'    => true
		            ],
		            'footer_code'        => [
			            'id'          => 'footer_code',
			            'title'       => 'Footer Code',
			            'type'        => 'textarea',
			            'description' => 'Step 2: Open your theme\'s footer.php file and paste this code just before the closing &lt;/body&gt; tag. This enables core safelink functionality.<br/><strong style="color:' . ( $valid_footer_integration ? 'green' : 'red' ) . '">' . ( $valid_footer_integration ? 'Footer code integration verified!' : 'Footer code integration required - follow Step 2' ) . '</strong>',
			            'default'     => "&lt;?php if(function_exists('newwpsafelink_bottom')) newwpsafelink_bottom();?&gt;",
			            'disabled'    => true
		            ],
		            'auto_link'          => [
			            'id'          => 'auto_link',
			            'title'       => 'Auto Generate Link Javascript',
			            'type'        => 'textarea',
			            'description' => 'Optional Step 3: To enable automatic link conversion on any webpage, add this JavaScript code before the closing &lt;/body&gt; tag. This allows WP Safelink to automatically convert links matching your settings.',
			            'default'     => "&lt;script src='" . get_bloginfo( 'url' ) . "/wpsafelink.js'&gt;&lt;/script&gt;",
			            'disabled'    => true
		            ]
	            );
            } else if ( $type == 'integration' ) {
	            // Generate API key dynamically
	            $api_key      = $this->generate_api_key();
	            $api_endpoint = get_rest_url( null, 'v1/wpsafelink/validate' );

	            $options = array(
		            'title_integration'  => [
			            'title'       => 'WP Safelink Integration',
			            'type'        => 'title',
			            'description' => 'Integrate WP Safelink with other extension plugins. This allows seamless connection between your main WP Safelink installation and other WordPress sites.'
		            ],
		            'api_key'            => [
			            'id'          => 'api_key',
			            'title'       => 'Integration Key',
			            'type'        => 'text',
			            'readonly'    => true,
			            'description' => 'This Integration key is generated dynamically based on your license. Use this key to authenticate with other WP Safelink plugins.<br><button type="button" class="button button-small" onclick="navigator.clipboard.writeText(\'' . esc_attr( $api_key ) . '\'); alert(\'API Key copied to clipboard!\');">Copy API Key</button>',
			            'default'     => $api_key,
			            'help' => 'Your unique integration key for connecting WP Safelink across multiple sites. This key is automatically generated from your license and domain. Use it to: sync settings between sites, enable remote link generation, or integrate with WP Safelink extensions. Keep this key secure.',
			            'help_title' => 'Multi-Site Integration Key'
		            ],
		            'title_auto_convert' => [
			            'title'       => 'WP Safelink Auto Convert Link',
			            'type'        => 'title',
			            'description' => '<strong>WP Safelink Auto Convert Link</strong> automatically converts external links on your WordPress site to safelinks. Perfect for monetizing outbound links without manual intervention.<br><br><strong>Features:</strong><ul style="list-style: disc; margin-left: 20px;"><li>Automatic link conversion based on domain rules</li><li>Include/Exclude domain lists</li><li>Custom link patterns</li><li>Real-time conversion</li></ul>'
		            ],
		            'title_adlinkfly'    => [
			            'title'       => 'Adlinkfly Integration',
			            'type'        => 'title',
			            'description' => '<strong>Connect WP Safelink with Adlinkfly</strong> for enhanced monetization and link management.'
		            ],
		            // Moved from dedicated Adlinkfly tab into Integration
		            'adlinkfly'          => array(
			            'title' => 'Enable Adlinkfly',
			            'id'    => 'adlinkfly',
			            'type'  => 'checkbox',
			            'label' => 'Enable Adlinkfly',
			            'help' => 'Connect WP Safelink with Adlinkfly URL shortener for enhanced monetization. When enabled, your safelinks can redirect through Adlinkfly first, adding an extra revenue layer. Perfect for maximizing earnings from high-traffic links.',
			            'help_title' => 'Adlinkfly Integration Benefits'
		            ),
		            'adlinkfly_url'      => [
			            'id'          => 'adlinkfly_url',
			            'title'       => 'Adlinkfly URL',
			            'type'        => 'text',
			            'description' => 'Example: https://redir.themeson.com',
			            'help' => 'Your Adlinkfly installation URL. This should be the full URL to your Adlinkfly site (without trailing slash). Make sure your Adlinkfly instance is properly configured to accept links from this WP Safelink installation.',
			            'help_title' => 'Adlinkfly Instance URL'
		            ],
		            'adlinkfly_secret_key' => [
			            'id'          => 'adlinkfly_secret_key',
			            'title'       => 'Adlinkfly Secret Key',
			            'type'        => 'text',
			            'default'     => md5( get_bloginfo( 'url' ) ),
			            'readonly'    => true,
			            'description' => 'Adlinkfly Secret Key is available in PRO version for easier and more secure integration.',
			            'help' => 'Secure authentication key for Adlinkfly API communication. This key ensures that only your WP Safelink can send links to your Adlinkfly instance. The PRO version includes advanced encryption and automatic key rotation for maximum security.',
			            'help_title' => 'Secure API Authentication'
		            ]
	            );
            } else if ($type == 'pro_tools') {
                global $wpdb;
                global $wpsafelink_core;

                // Generate export data
                $sql               = "SELECT * FROM {$wpdb->prefix}wpsafelink";
                $safe_lists        = $wpdb->get_results( $sql, 'ARRAY_A' );
                $encrypt_safelists = '';
                if ( ! empty( $safe_lists ) ) {
                    $encrypt_safelists = $wpsafelink_core->encrypt_link( json_encode( $safe_lists ) );
                }

                // Generate API key
                $restapi_apikey = md5( get_bloginfo( 'url' ) . 'wp-safelink.pro.php' . $wpsafelink_core->license( 'key' ) );

                $options = [
                    // Main Title
                    'title_pro_tools'       => [
                        'title'       => '🛠️ Pro Tools & Integration Suite',
                        'type'        => 'title',
                        'description' => '<div style="background: #667eea; color: white; padding: 20px; border-radius: 10px; margin-bottom: 20px;">
                            <h3 style="margin: 0 0 10px 0; color: white;">Advanced Features for Power Users</h3>
                            <p style="margin: 0; opacity: 0.95;">Unlock the full potential of WP Safelink with security controls, API access, and data management tools.</p>
                        </div>'
                    ],

                    // SECTION 1: SECURITY SHIELD
                    'title_security_shield' => [
                        'title'       => '🛡️ Security Shield',
                        'type'        => 'title',
                        'description' => '<div style="background: #fef3c7; border-left: 4px solid #f59e0b; padding: 15px; margin-bottom: 20px; border-radius: 0 8px 8px 0;">
                            <h4 style="margin: 0 0 8px 0; color: #92400e;">Block Unwanted Traffic</h4>
                            <p style="margin: 0; color: #78350f;">Protect your ad revenue by detecting and blocking visitors using VPNs, proxies, or other anonymization tools. This ensures only genuine users can access your safelinks.</p>
                        </div>'
                    ],
                    'antiproxy'             => [
                        'id'          => 'antiproxy',
                        'title'       => 'Enable Protection',
                        'type'        => 'checkbox',
                        'label'       => 'Activate Anti-Proxy/VPN Shield',
                        'default'     => 'no',
                        'description' => 'When enabled, users with detected VPN/proxy connections will see a custom message instead of the safelink.'
                    ],
                    'proxycheck_apikey'     => [
                        'id'          => 'proxycheck_apikey',
                        'title'       => 'ProxyCheck API Key',
                        'type'        => 'text',
                        'placeholder' => 'Enter your ProxyCheck.io API key',
                        'description' => '🔑 Get your free API key at <a href="https://proxycheck.io/" target="_blank" style="color: #1ABC9C;">proxycheck.io</a> • Free tier: 1,000 queries/day • Paid plans available for higher volume'
                    ],
                    'antiproxy1'            => [
                        'id'          => 'antiproxy1',
                        'title'       => 'Block Message',
                        'type'        => 'textarea',
                        'default'     => 'Access Restricted: We\'ve detected that you\'re using a VPN or proxy service. Please disable it to continue.',
                        'description' => 'Message shown to blocked users. Keep it professional and clear.'
                    ],

                    // SECTION 2: DEVELOPER API
                    'title_developer_api'   => [
                        'title'       => '🔌 Developer API',
                        'type'        => 'title',
                        'description' => '<div style="background: #ede9fe; border-left: 4px solid #7c3aed; padding: 15px; margin: 20px 0; border-radius: 0 8px 8px 0;">
                            <h4 style="margin: 0 0 8px 0; color: #4c1d95;">Automate Link Generation</h4>
                            <p style="margin: 0; color: #5b21b6;">Integrate WP Safelink with external applications, scripts, or services. Generate safelinks programmatically using our RESTful API endpoint.</p>
                        </div>'
                    ],
                    'restapi_enable'        => [
                        'id'          => 'restapi_enable',
                        'title'       => 'Enable API',
                        'type'        => 'checkbox',
                        'label'       => 'Activate REST API Endpoint',
                        'default'     => 'no',
                        'description' => 'Allows external applications to create safelinks via API calls.'
                    ],
                    'restapi_apikey'        => [
                        'id'          => 'restapi_api',
                        'title'       => 'Your API Key',
                        'type'        => 'text',
                        'default'     => $restapi_apikey,
                        'readonly'    => true,
                        'description' => '⚠️ Keep this key secret! It provides full access to create safelinks on your site.'
                    ],
                    'restapi_example'       => [
                        'id'          => 'restapi_Example',
                        'title'       => 'API Endpoint',
                        'type'        => 'textarea',
                        'default'     => get_rest_url( '', "/wpsafelink/create?url=https://example.com&api_key=$restapi_apikey" ),
                        'disabled'    => true,
                        'description' => '<div style="background: #f0f9ff; border: 1px solid #0ea5e9; padding: 12px; border-radius: 6px; margin-top: 10px;">
                            <strong style="color: #0c4a6e;">Quick Start:</strong>
                            <ol style="margin: 8px 0 0 20px; color: #0369a1;">
                                <li>Enable the API above and save settings</li>
                                <li>Send a POST or GET request to the endpoint</li>
                                <li>Include <code>url</code> (target URL) and <code>api_key</code> parameters</li>
                                <li>Receive JSON response with safelink URLs</li>
                            </ol>
                            <p style="margin: 8px 0 0 0; color: #0c4a6e;"><strong>Response format:</strong> <code>{"target_url": "...", "safelink_short_url": "...", "safelink_long_url": "..."}</code></p>
                        </div>'
                    ],

                    // SECTION 3: DATA MANAGEMENT
                    'title_data_management' => [
                        'title'       => '📦 Data Management',
                        'type'        => 'title',
                        'description' => '<div style="background: #ecfdf5; border-left: 4px solid #10b981; padding: 15px; margin: 20px 0; border-radius: 0 8px 8px 0;">
                            <h4 style="margin: 0 0 8px 0; color: #064e3b;">Backup & Migration</h4>
                            <p style="margin: 0; color: #047857;">Seamlessly transfer your safelinks between sites or create backups. All data is encrypted for security during transport.</p>
                        </div>'
                    ],
                    'export'                => [
                        'id'          => 'export',
                        'title'       => '📤 Export Links',
                        'description' => '<strong>Current Database:</strong> ' . count( $safe_lists ) . ' safelinks • Copy the encrypted data below to backup or migrate your links',
                        'type'        => 'textarea',
                        'default'     => $encrypt_safelists,
                        'disabled'    => true
                    ],
                    'import'                => [
                        'id'          => 'import',
                        'title'       => '📥 Import Links',
                        'description' => 'Paste encrypted export data here to restore links. <strong style="color: #dc2626;">Warning:</strong> This will ADD to existing links, not replace them.',
                        'type'        => 'textarea',
                        'placeholder' => 'Paste your encrypted export data here...'
                    ],

                    // Help Section
                    'title_help'            => [
                        'title'       => '',
                        'type'        => 'title',
                        'description' => '<div style="background: #f8fafc; border: 1px solid #cbd5e1; padding: 15px; border-radius: 8px; margin-top: 30px;">
                            <h4 style="margin: 0 0 10px 0; color: #334155;">💡 Pro Tips</h4>
                            <ul style="margin: 0; padding-left: 20px; color: #475569;">
                                <li><strong>Security:</strong> Enable Anti-Proxy to maximize ad revenue from genuine users</li>
                                <li><strong>API:</strong> Perfect for auto-posting bots, mobile apps, or bulk operations</li>
                                <li><strong>Backup:</strong> Export your links regularly, especially before major changes</li>
                                <li><strong>Migration:</strong> Use Import/Export to clone settings to multiple sites</li>
                            </ul>
                        </div>'
                    ]
                ];
            } else if ($type == 'second_safelink') {
                $options = [
                    'title_second_safelink_new'  => [
                        'title'       => '',
                        'type'        => 'title',
                        'description' => '<div style="background: #f0f9ff; border: 1px solid #0ea5e9; border-radius: 8px; padding: 15px; margin-bottom: 20px;">
                            <h4 style="margin-top: 0; color: #0c4a6e; font-size: 16px;">What is Second Safelink?</h4>
                            <p style="color: #475569; margin: 10px 0;">Second Safelink creates a <strong>chain of safelinks</strong> across multiple websites, maximizing your monetization potential. When enabled, visitors pass through TWO safelink pages before reaching the target URL:</p>
                            <ol style="color: #475569; margin: 10px 0 10px 20px;">
                                <li><strong>First hop:</strong> Your main website safelink (this site)</li>
                                <li><strong>Second hop:</strong> Another website\'s safelink (your second monetization site)</li>
                                <li><strong>Final destination:</strong> The target URL</li>
                            </ol>
                            <p style="color: #475569; margin: 10px 0;">This doubles your ad impressions and revenue per click!</p>
                        </div>'
                    ],
                    'second_safelink_enable' => [
                        'id'      => 'second_safelink_enable',
                        'title'   => 'Enable Second Safelink',
                        'type'    => 'checkbox',
                        'label'   => 'Activate Second Safelink chain',
                        'default' => 'no'
                    ],
                    'second_safelink_key'    => [
                        'id'          => 'second_safelink_key',
                        'title'       => 'Integration Key',
                        'type'        => 'text',
                        'placeholder' => 'XXX-XXXXXXXXXXXXXXXXXXXXXXXX',
                        'description' => '<button type="button" class="button button-secondary" id="validate-second-safelink-btn">Validate Connection</button>
                            <span id="second-safelink-status" style="margin-left: 10px;"></span>
                            <div id="second-safelink-url-display" style="margin-top: 10px; display: none;">
                                <strong>Connected to:</strong> <span id="second-safelink-url-text" style="color: #059669;"></span>
                            </div>'
                    ],
                    'setup_instructions'     => [
                        'title'       => '',
                        'type'        => 'title',
                        'description' => '<div style="background: #fefce8; border: 1px solid #facc15; border-radius: 8px; padding: 15px;">
                            <h4 style="margin-top: 0; color: #713f12; font-size: 15px;">How to Set Up Second Safelink:</h4>
                            <ol style="color: #854d0e; line-height: 1.8;">
                                <li><strong>Install WP Safelink on another website</strong><br>
                                    <span style="color: #92400e; font-size: 13px;">This must be a DIFFERENT website than your current site. Example: If this is site-a.com, use site-b.com</span>
                                </li>
                                <li><strong>Generate Integration Key on your SECOND site:</strong><br>
                                    <code style="background: #fff; padding: 3px 6px; border-radius: 4px;">WP Safelink → Integration Settings</code><br>
                                    <span style="color: #92400e; font-size: 13px;">Look for "Your Integration Key" - it will be a special encrypted key like: XXX-XXXXXXXXXXXXXXXXXXXXXXXX</span>
                                </li>
                                <li><strong>Copy the Integration Key</strong><br>
                                    <span style="color: #92400e; font-size: 13px;">Copy the entire key including the prefix (XXX-...)</span>
                                </li>
                                <li><strong>Paste and Validate</strong><br>
                                    <span style="color: #92400e; font-size: 13px;">Paste the key above and click "Validate Connection"</span>
                                </li>
                                <li><strong>Save Changes</strong><br>
                                    <span style="color: #92400e; font-size: 13px;">Once validated, save to activate the second safelink chain</span>
                                </li>
                            </ol>
                            <div style="background: #fee2e2; border-radius: 6px; padding: 10px; margin-top: 15px;">
                                <strong style="color: #991b1b;">⚠️ Important:</strong>
                                <ul style="color: #b91c1c; margin: 5px 0 0 20px; font-size: 13px;">
                                    <li>Both sites must have WP Safelink installed and activated</li>
                                    <li>Do NOT use your own site\'s integration key (cannot chain to itself)</li>
                                    <li>Each integration key is unique and contains the encrypted site URL</li>
                                    <li>The key changes each time it\'s generated for security</li>
                                </ul>
                            </div>
                        </div>'
                    ]
                ];
            } else if ($type == 'google_redirect') {
                $options = [
                    'title_google_redirect' => [
                        'title'       => 'Google Redirect',
                        'type'        => 'title',
                        'description' => 'If a visitor doesn\'t come from Google, redirect them to one of your Google SERP links first. This helps set Referrer as Google before continuing to the safelink.'
                    ],
                    'google_redirect_enable' => [
                        'id'      => 'google_redirect_enable',
                        'title'   => 'Google Redirect',
                        'type'    => 'checkbox',
                        'label'   => 'Activate Google Redirect',
                        'default' => 'no'
                    ],
                    'google_redirect_url'    => [
                        'id'          => 'google_redirect_url',
                        'title'       => 'Google Redirect URL',
                        'type'        => 'multiple_input',
                        'default'     => '',
                        'description' => 'Use these steps to collect Google redirect links (the long URLs that start with <code>https://www.google.com/url?</code>) that point back to your site:<ol style="margin-top:6px;">
                            <li>Open your browser (Chrome/Firefox). Going incognito helps avoid personalized results.</li>
                            <li>Go to <a href="https://www.google.com" target="_blank">google.com</a>.</li>
                            <li>Search for either: <br>• Your full domain: <code>' . get_bloginfo( 'url' ) . '</code> <br>• Any keyword that returns a result from your website (you can also use <code>site:' . parse_url( get_bloginfo( 'url' ), PHP_URL_HOST ) . '</code> plus a keyword).</li>
                            <li>On the results page, find a result that belongs to your website (the visible link host matches <code>' . parse_url( get_bloginfo( 'url' ), PHP_URL_HOST ) . '</code> — it can be your home page, an article, category, etc.).</li>
                            <li>Right‑click the result title and choose <em>Copy link address</em>. Google will copy a long redirect URL beginning with <code>https://www.google.com/url?</code>.</li>
                            <li>Paste that URL into the field above. Repeat to add 2–5 different URLs for rotation.</li>
                        </ol>
                        <p><strong>Notes:</strong> One URL per line. Make sure each URL ultimately targets your own domain (<code>' . get_bloginfo( 'url' ) . '</code>). Both the home page and any internal pages/posts are valid.</p>'
                    ],
                    'title_google_redirect_content' => [
                        'title'       => 'Content Page',
                        'type'        => 'title',
                        'description' => 'Show a customizable content page with text and images before redirecting users to Google.'
                    ],
                    'google_redirect_content_enable' => [
                        'id'      => 'google_redirect_content_enable',
                        'title'   => 'Enable Content Page',
                        'type'    => 'checkbox',
                        'label'   => 'Show content page before Google redirect',
                        'default' => 'no'
                    ],
                    'google_redirect_content' => [
                        'id'          => 'google_redirect_content',
                        'title'       => 'Content Page Text',
                        'type'        => 'wysiwyg',
                        'default'     => '<h2>Important Notice</h2><p>You are about to be redirected. Please click the button below to continue and click the link like images below :</p><p><img src="'.wpsafelink_plugin_url().'/assets/google-redirect-screenshot.png"/></p>',
                        'description' => 'Use the editor to create your content. You can add text, images, and formatting.'
                    ],
                    'google_redirect_button_text' => [
                        'id'          => 'google_redirect_button_text',
                        'title'       => 'Button Text',
                        'type'        => 'text',
                        'default'     => 'Continue',
                        'description' => 'Text displayed on the confirmation button at the bottom of the content page.'
                    ]
                ];
            } else if ($type == 'multiple_pages') {
                $options = [
                    'title_multiple_pages'              => [
                        'title'       => 'Multiple Pages',
                        'type'        => 'title',
                        'description' => 'Split the safelink process across several intermediate pages to increase ad impressions or engagement.'
                    ],
                    'multiple_pages'                    => [
                        'id'      => 'multiple_pages',
                        'title'   => 'Multiple Pages',
                        'type'    => 'checkbox',
                        'label'   => 'Activate Multiple Pages',
                        'default' => 'no'
                    ],
                    'multiple_pages_max'                => [
                        'id'      => 'multiple_pages_max',
                        'title'   => 'Maximum Pages',
                        'type'    => 'number',
                        'default' => 3
                    ],
                    'multiple_pages_text'               => [
                        'id'      => 'multiple_pages_text',
                        'title'   => 'Text',
                        'type'    => 'text',
                        'default' => 'Pages {current}/{max}'
                    ],
                    'multiple_pages_button'             => [
                        'id'      => 'multiple_pages_button',
                        'title'   => 'Button Text',
                        'type'    => 'text',
                        'default' => 'Continue'
                    ],
                    // Styling controls for progress display
                    'multiple_pages_style_current_bg'   => [
                        'id'      => 'multiple_pages_style_current_bg',
                        'title'   => 'Current Step Background',
                        'type'    => 'color',
                        'default' => '#1ABC9C'
                    ],
                    'multiple_pages_style_current_text' => [
                        'id'      => 'multiple_pages_style_current_text',
                        'title'   => 'Current Step Text',
                        'type'    => 'color',
                        'default' => '#ffffff'
                    ],
                    'multiple_pages_style_max_text'     => [
                        'id'      => 'multiple_pages_style_max_text',
                        'title'   => 'Remaining/Max Text',
                        'type'    => 'color',
                        'default' => '#6b7280'
                    ],
                    'multiple_pages_style_font_family'  => [
                        'id'      => 'multiple_pages_style_font_family',
                        'title'   => 'Progress Font Family',
                        'type'    => 'select',
                        'options' => [
                            'system-ui' => 'System UI',
                            'inter'     => 'Inter',
                            'arial'     => 'Arial',
                            'roboto'    => 'Roboto',
                            'georgia'   => 'Georgia',
                        ],
                        'default' => 'system-ui'
                    ],
                    'multiple_pages_style_font_size'    => [
                        'id'          => 'multiple_pages_style_font_size',
                        'title'       => 'Progress Font Size',
                        'type'        => 'text',
                        'description' => 'CSS size value (e.g. 14px)',
                        'default'     => '14px'
                    ],
                    // Behavior when clicking Continue before final step
                    'multiple_pages_next_behavior'      => [
                        'id'      => 'multiple_pages_next_behavior',
                        'title'   => 'Continue Button Behavior',
                        'type'    => 'select',
                        'options' => [
                            'open_random_post' => 'Open Another Article (recommended)',
                            'repeat_safelink'  => 'Restart Safelink Step',
                        ],
                        'default' => 'open_random_post'
                    ]
                ];
            } else if ($type == 'adlinkfly_pro') {
                $options = array(
                    'title_adlinkfly'      => [
                        'title'       => 'Adlinkfly PRO',
                        'type'        => 'title',
                        'description' => 'The seamless integration of AdLinkFly with WP Safelink. Use the guide below to connect both products properly.'
                    ],
                    'adlinkfly'            => array(
                        'title' => 'Adlinkfly',
                        'id'    => 'adlinkfly',
                        'type'  => 'checkbox',
                        'label' => 'Enable Adlinkfly',
                    ),
                    'adlinkfly_url' => [
                        'id'          => 'adlinkfly_url',
                        'title'       => 'Adlinkfly URL',
                        'type'        => 'text',
                        'default'     => '',
                        'description' => 'example : https://redir.themeson.com'
                    ],
                    'adlinkfly_secret_key' => [
                        'id'       => 'adlinkfly_secret_key',
                        'title'    => 'Adlinkfly Secret Key',
                        'type'     => 'text',
                        'default'  => md5( get_bloginfo( 'url' ) ),
                        'readonly' => true
                    ],
                    'tutorial_adlinkfly'   => [
                        'title'       => 'Tutorial',
                        'type'        => 'title',
                        'description' => '<div class="wpsl-tutorial">'
                                         . '<style>.wpsl-tutorial{font-size:14px;line-height:1.6;color:#1f2937}.wpsl-tutorial h4{margin:10px 0 6px;font-size:15px;line-height:1.4;color:#111827}.wpsl-tutorial ol{margin:6px 0 12px 18px}.wpsl-tutorial ul{margin:6px 0 12px 18px; list-style: initial;}.wpsl-tutorial li{margin:4px 0}.wpsl-tutorial code{background:#f3f4f6;border:1px solid #e5e7eb;border-radius:4px;padding:1px 4px;font-size:12px}.wpsl-tutorial .tip{background:#f8fafc;border:1px solid #e5e7eb;border-radius:6px;padding:8px 10px;margin:10px 0}.wpsl-tutorial .muted{color:#6b7280}.wpsl-tutorial .kbd{display:inline-block;border:1px solid #cbd5e1;border-bottom-width:2px;background:#fff;border-radius:4px;padding:0 5px;font-size:12px;font-family:ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace}</style>'
                                         . '<h4>What you need</h4>'
                                         . '<ul>'
                                         . '<li>An active AdLinkFly installation (admin access).</li>'
                                         . '<li>Your WP Safelink site URL: <code>' . get_bloginfo( 'url' ) . '</code></li>'
                                         . '<li>WordPress Secret Key (generated from your site URL): <code>' . md5( get_bloginfo( 'url' ) ) . '</code></li>'
                                         . '</ul>'
                                         . '<h4>Step 1 — Configure AdLinkFly</h4>'
                                         . '<ol>'
                                         . '<li>Log in to your AdLinkFly Administrative Area.</li>'
                                         . '<li>Go to <span class="kbd">Settings</span> → <span class="kbd">Settings</span> → <span class="kbd">Links</span>.</li>'
                                         . '<li>Find <strong>Short Link Page External Integration</strong> and choose <strong>WordPress</strong>.</li>'
                                         . '<li>Set <strong>WordPress Access URL</strong> to <code>' . get_bloginfo( 'url' ) . '</code>.</li>'
                                         . '<li>Set <strong>WordPress Secret Key</strong> to <code>' . md5( get_bloginfo( 'url' ) ) . '</code>.</li>'
                                         . '<li>Click <strong>Save Changes</strong>.</li>'
                                         . '</ol>'
                                         . '<div class="tip"><strong>Why these values?</strong> AdLinkFly uses your site URL and the secret key to validate that requests really come from this WordPress site.</div>'
                                         . '<h4>Step 2 — Connect WP Safelink to AdLinkFly</h4>'
                                         . '<ol>'
                                         . '<li>Enable the <strong>Adlinkfly</strong> toggle above.</li>'
                                         . '<li>Enter your AdLinkFly base URL (for example: <code>https://redir.example.com</code>) in <strong>Adlinkfly URL</strong>.</li>'
                                         . '<li>Click <strong>Save Changes</strong> at the bottom of this page.</li>'
                                         . '</ol>'
                                         . '<h4>How the handoff works</h4>'
                                         . '<ul>'
                                         . '<li>When a safelink uses an AdLinkFly handoff, the plugin builds a link like <code>[AdLinkFly URL]/&lt;slug_or_id&gt;</code> and continues the normal safelink flow.</li>'
                                         . '<li>Clicks and views are still tracked in WP Safelink; the final redirect is served by AdLinkFly.</li>'
                                         . '</ul>'
                                         . '<h4>Quick test</h4>'
                                         . '<ol>'
                                         . '<li>Create (or identify) a short link slug/ID in AdLinkFly.</li>'
                                         . '<li>Open: <code>' . home_url( '/?adlinkfly=YOUR_SLUG_OR_ID' ) . '</code> in your browser.</li>'
                                         . '<li>You should see the safelink landing, then get redirected through AdLinkFly to the final target.</li>'
                                         . '</ol>'
                                         . '<h4>Troubleshooting</h4>'
                                         . '<ul>'
                                         . '<li><strong>403/Unauthorized from AdLinkFly:</strong> Re-check the <em>WordPress Secret Key</em> matches <code>' . md5( get_bloginfo( 'url' ) ) . '</code>.</li>'
                                         . '<li><strong>Wrong domain after redirect:</strong> Confirm <em>WordPress Access URL</em> is exactly <code>' . get_bloginfo( 'url' ) . '</code> and uses HTTPS if your site does.</li>'
                                         . '<li><strong>No redirect happens:</strong> Ensure the <em>Adlinkfly</em> option is enabled here and <em>Adlinkfly URL</em> is set correctly.</li>'
                                         . '</ul>'
                                         . '</div>'
                    ]
                );
            }

	        $options = apply_filters( 'wpsafelink_general_options', $options, $type );

            return $options;
        }

	    function check_theme_integration( $type ) {
		    if ( $type == 'header' ) {
			    $theme       = wp_get_theme();
			    $theme_root  = get_theme_root();
			    $header_file = $theme_root . '/' . $theme->get_stylesheet() . '/header.php';

			    if ( file_exists( $header_file ) ) {
				    $header_content = file_get_contents( $header_file );

				    return ( strpos( $header_content, 'newwpsafelink_top' ) !== false );
			    }

			    return false;
		    } else if ( $type == 'footer' ) {
			    $theme       = wp_get_theme();
			    $theme_root  = get_theme_root();
			    $footer_file = $theme_root . '/' . $theme->get_stylesheet() . '/footer.php';
			    if ( file_exists( $footer_file ) ) {
				    $footer_content = file_get_contents( $footer_file );

				    return ( strpos( $footer_content, 'newwpsafelink_bottom' ) !== false );
			    }

			    return false;
		    }

		    return false;
	    }

        /**
         * AJAX handler for license validation
         *
         * @access  public
         * @return  void
         * @since   5.1.3
         */
        public function ajax_validate_license()
        {
            // Verify nonce
            if (!wp_verify_nonce($_POST['nonce'], 'wpsafelink_license_activation')) {
                wp_send_json_error(array('message' => 'Security check failed.'));
            }

            global $wpsafelink_core;

	        $license_key = sanitize_text_field($_POST['license_key']);
            $domain = sanitize_text_field($_POST['domain']);

	        if (empty($license_key)) {
                wp_send_json_error(array('message' => 'License key is required.'));
            }

	        // Validate license using existing core method
            $check_license = $wpsafelink_core->license($license_key);

	        if ($check_license['success']) {
                // Initialize default settings if first time
                $this->initialize_default_settings();

		        wp_send_json_success(array('message' => 'License activated successfully!'));
            } else {
                $error_message = $check_license['data']->msg ?? 'License validation failed.';
                wp_send_json_error(array('message' => $error_message));
            }
        }

        /**
         * AJAX handler for license change
         *
         * @access  public
         * @return  void
         * @since   5.1.3
         */
        public function ajax_change_license()
        {
            // Verify nonce
            if (!wp_verify_nonce($_POST['nonce'], 'wpsafelink_change_license')) {
                wp_send_json_error(array('message' => 'Security check failed.'));
            }

	        $this->clear_cached_license_script();

	        wp_send_json_success(array('message' => 'License cleared successfully.'));
        }

        /**
         * AJAX handler for integration checking
         *
         * @access  public
         * @return  void
         * @since   5.1.3
         */
        public function ajax_check_integration()
        {
            // Verify nonce
            if (!wp_verify_nonce($_POST['nonce'], 'wpsafelink_check_integration')) {
                wp_send_json_error(array('message' => 'Security check failed.'));
            }

	        $header_integration = $this->check_theme_integration('header');
            $footer_integration = $this->check_theme_integration('footer');

	        wp_send_json_success(array(
                'header' => $header_integration,
                'footer' => $footer_integration,
                'completed' => $header_integration && $footer_integration
            ));
        }

	    /**
	     * AJAX handler for license status checking
	     *
	     * @access  public
	     * @return  void
	     * @since   5.2.2
	     */
	    public function ajax_license_status() {
		    check_ajax_referer( 'wpsafelink_ajax_nonce', '_wpnonce' );

		    $output = array(
			    'status'  => 'error',
			    'message' => 'Invalid request'
		    );

		    global $wpsafelink_core;

		    // Measure response time
		    $start_time = microtime( true );

		    // Force check from API (bypass cache)
		    $license_response = $wpsafelink_core->license( '', false, true );

		    // Calculate response time
		    $end_time      = microtime( true );
		    $response_time = round( ( $end_time - $start_time ) * 1000 ); // Convert to milliseconds

		    if ( $license_response['success'] ) {
			    $data = $license_response['data'];

			    // Determine response time status
			    $response_class = 'yes';
			    if ( $response_time > 1000 ) {
				    $response_class = 'no';
			    }

			    // Format the response data - handle both object and array formats
			    $msg_data       = is_array( $data->msg ) ? $data->msg : (array) $data->msg;
			    $formatted_data = array(
				    'message' => 'License is Active and Valid',
				    'success' => true,
				    'result'  => array(
					    'license_key'   => substr( $msg_data['license'], 0, 10 ) . '*********',
					    'product'       => $msg_data['title'] ?? 'WP Safelink WordPress Plugin',
					    'status'        => $msg_data['status'] ?? 'active',
					    'domain'        => $msg_data['domain'] ?? str_replace( [
							    'https://',
							    'http://'
						    ], '', home_url() ),
					    'response_time' => $response_time . 'ms',
					    'last_check'    => date( 'd M Y, H:i:s' ),
				    )
			    );

			    ob_start();
			    require_once wpsafelink_plugin_path() . '/views/settings/license-product-status.php';
			    $output['message'] = ob_get_clean();
			    $output['status']  = 'success';
		    } else {
			    // License is not valid
			    $formatted_data = array(
				    'message' => 'License is Invalid or Expired',
				    'success' => false,
				    'result'  => array()
			    );

			    ob_start();
			    require_once wpsafelink_plugin_path() . '/views/settings/license-product-status.php';
			    $output['message'] = ob_get_clean();
			    $output['status']  = 'error';
		    }

		    wp_send_json( $output );
		    wp_die();
	    }

	    /**
	     * AJAX handler for checking theme integration status
	     *
	     * @access  public
	     * @return  void
	     * @since   5.2.3
	     */
	    public function ajax_check_integration_status() {
		    // Check nonce
		    if ( ! check_ajax_referer( 'wpsafelink_ajax', '_wpnonce', false ) ) {
			    wp_die( 'Security check failed' );
		    }
		    
		    // Get current settings
		    $options = wpsafelink_options();
		    $current_template = $options['template'] ?? 'template1';
		    
		    // Templates that require integration
		    $integration_templates = array( 'template2', 'template3' );
		    $integration_required = in_array( $current_template, $integration_templates );
		    
		    // Get theme info
		    $theme = wp_get_theme();
		    
		    // Use existing check_theme_integration method
		    $status = array(
			    'header_integrated' => $this->check_theme_integration('header'),
			    'footer_integrated' => $this->check_theme_integration('footer'),
			    'integration_required' => $integration_required,
			    'current_template' => $current_template,
			    'theme_name' => $theme->get( 'Name' ),
			    'theme_version' => $theme->get( 'Version' )
		    );
		    
		    wp_send_json( array(
			    'success' => true,
			    'data'    => $status
		    ) );
		    wp_die();
	    }

	    /**
	     * Register REST API routes
	     *
	     * @access  public
	     * @return  void
	     * @since   5.1.5
	     */
	    public function register_rest_routes() {
		    register_rest_route( 'v1/wpsafelink', '/validate', array(
			    'methods'             => 'POST',
			    'callback'            => array( $this, 'handle_api_validation' ),
			    'permission_callback' => '__return_true',
			    'args'                => array(
				    'key' => array(
					    'required'          => true,
					    'type'              => 'string',
					    'sanitize_callback' => 'sanitize_text_field'
				    )
			    )
		    ) );
	    }

	    /**
	     * Handle API validation endpoint
	     *
	     * @access  public
	     *
	     * @param WP_REST_Request $request
	     *
	     * @return  WP_REST_Response
	     * @since   5.1.5
	     */
	    public function handle_api_validation( $request ) {
		    $api_key = $request->get_param( 'key' );

		    if ( empty( $api_key ) ) {
			    return new WP_REST_Response( array( 'success' => false ), 200 );
		    }

		    $is_valid = $this->validate_api_key( $api_key );

		    return new WP_REST_Response( array( 'success' => $is_valid ), 200 );
	    }

	    /**
	     * Get first 3 digits from license
	     *
	     * @access  private
	     * @return  string
	     * @since   5.1.5
	     */
	    private function get_license_first_3_digits() {
		    global $wpsafelink_core;

		    // Get the full license key
		    $license_data = $wpsafelink_core->license( '', true );

		    if ( ! $license_data || strlen( $license_data ) < 3 ) {
			    // Fallback to a default if no license
			    return '000';
		    }

		    // Extract first 3 alphanumeric characters
		    $clean_license = preg_replace( '/[^a-zA-Z0-9]/', '', $license_data );

		    return substr( $clean_license, 0, 3 );
	    }

	    /**
	     * Simple encryption function
	     *
	     * @access  private
	     *
	     * @param string $string String to encrypt
	     * @param string $key Encryption key (3 digits)
	     *
	     * @return  string
	     * @since   5.1.5
	     */
	    private function encrypt_string( $string, $key ) {
		    // Simple XOR-based encryption with base64 encoding
		    $result     = '';
		    $key_length = strlen( $key );

		    for ( $i = 0; $i < strlen( $string ); $i ++ ) {
			    $char     = $string[ $i ];
			    $key_char = $key[ $i % $key_length ];
			    $result   .= chr( ord( $char ) ^ ord( $key_char ) );
		    }

		    return base64_encode( $result );
	    }

	    /**
	     * Simple decryption function
	     *
	     * @access  private
	     *
	     * @param string $encrypted Encrypted string
	     * @param string $key Decryption key (3 digits)
	     *
	     * @return  string|false
	     * @since   5.1.5
	     */
	    private function decrypt_string( $encrypted, $key ) {
		    $decoded = base64_decode( $encrypted );

		    if ( $decoded === false ) {
			    return false;
		    }

		    // XOR decryption (same as encryption for XOR cipher)
		    $result     = '';
		    $key_length = strlen( $key );

		    for ( $i = 0; $i < strlen( $decoded ); $i ++ ) {
			    $char     = $decoded[ $i ];
			    $key_char = $key[ $i % $key_length ];
			    $result   .= chr( ord( $char ) ^ ord( $key_char ) );
		    }

		    return $result;
	    }

	    /**
	     * Generate API key
	     *
	     * @access  public
	     * @return  string
	     * @since   5.1.5
	     */
	    public function generate_api_key() {
		    $prefix    = $this->get_license_first_3_digits();
		    $encrypted = $this->encrypt_string( home_url(), $prefix );

		    return $prefix . '-' . $encrypted;
	    }

	    /**
	     * Generate integration key for this site (used by second safelink)
	     *
	     * @access  public
	     * @return  string
	     * @since   5.1.5
	     */
	    public function generate_integration_key() {
		    $url = get_bloginfo( 'url' );
		    
		    // Generate a random 3-character key
		    $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
		    $key   = '';
		    for ( $i = 0; $i < 3; $i ++ ) {
			    $key .= $chars[ rand( 0, strlen( $chars ) - 1 ) ];
		    }
		    
		    // XOR encryption
		    $encrypted  = '';
		    $key_length = strlen( $key );
		    
		    for ( $i = 0; $i < strlen( $url ); $i ++ ) {
			    $char      = $url[ $i ];
			    $key_char  = $key[ $i % $key_length ];
			    $encrypted .= chr( ord( $char ) ^ ord( $key_char ) );
		    }
		    
		    // Base64 encode and combine
		    return $key . '-' . base64_encode( $encrypted );
	    }

	    /**
	     * Validate API key
	     *
	     * @access  public
	     *
	     * @param string $api_key
	     *
	     * @return  bool
	     * @since   5.1.5
	     */
	    public function validate_api_key( $api_key ) {
		    if ( strpos( $api_key, '-' ) === false ) {
			    return false;
		    }

		    list( $prefix, $encrypted ) = explode( '-', $api_key, 2 );

		    if ( strlen( $prefix ) !== 3 ) {
			    return false;
		    }

		    $decrypted = $this->decrypt_string( $encrypted, $prefix );

		    return $decrypted === home_url();
	    }
	    
	    /**
	     * Get help system CSS styles
	     *
	     * @access  private
	     * @return  string
	     * @since   5.2.3
	     */
	    private function get_help_system_styles() {
		    return '
		    /* WP Safelink Help System - Ultra Thin Design */
		    .wpsafelink-help-icon {
			    display: inline-block;
			    width: 16px;
			    height: 16px;
			    margin-left: 6px;
			    background-color: #e5e7eb;
			    border-radius: 50%;
			    cursor: pointer;
			    position: relative;
			    vertical-align: middle;
			    transition: all 0.2s ease;
			    font-family: system-ui, -apple-system, sans-serif;
			    font-size: 12px;
			    font-weight: 500;
			    text-align: center;
			    line-height: 16px;
			    color: #6b7280;
			    user-select: none;
		    }
		    
		    .wpsafelink-help-icon:hover {
			    background-color: #1ABC9C;
			    color: #ffffff;
			    transform: scale(1.1);
		    }
		    
		    .wpsafelink-help-icon::before {
			    content: "?";
		    }
		    
		    /* Popup Overlay */
		    .wpsafelink-help-overlay {
			    position: fixed;
			    top: 0;
			    left: 0;
			    right: 0;
			    bottom: 0;
			    background: rgba(17, 24, 39, 0.4);
			    backdrop-filter: blur(2px);
			    z-index: 999999;
			    display: none;
			    align-items: center;
			    justify-content: center;
			    animation: fadeIn 0.2s ease;
		    }
		    
		    .wpsafelink-help-overlay.active {
			    display: flex;
		    }
		    
		    .wpsafelink-help-popup {
			    background: #ffffff;
			    border-radius: 12px;
			    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
			    max-width: 520px;
			    width: 90%;
			    max-height: 80vh;
			    overflow: hidden;
			    animation: slideUp 0.3s ease;
		    }
		    
		    .wpsafelink-help-header {
			    padding: 20px 24px;
			    border-bottom: 1px solid #e5e7eb;
			    display: flex;
			    align-items: center;
			    justify-content: space-between;
		    }
		    
		    .wpsafelink-help-title {
			    font-size: 18px;
			    font-weight: 600;
			    color: #111827;
			    margin: 0;
			    font-family: system-ui, -apple-system, sans-serif;
		    }
		    
		    .wpsafelink-help-close {
			    width: 32px;
			    height: 32px;
			    border: none;
			    background: #f3f4f6;
			    border-radius: 8px;
			    cursor: pointer;
			    display: flex;
			    align-items: center;
			    justify-content: center;
			    transition: all 0.2s ease;
			    color: #6b7280;
		    }
		    
		    .wpsafelink-help-close:hover {
			    background: #e5e7eb;
			    color: #111827;
		    }
		    
		    .wpsafelink-help-close::before {
			    content: "✕";
			    font-size: 18px;
		    }
		    
		    .wpsafelink-help-content {
			    padding: 24px;
			    overflow-y: auto;
			    max-height: calc(80vh - 73px);
			    font-family: system-ui, -apple-system, sans-serif;
			    font-size: 14px;
			    line-height: 1.6;
			    color: #4b5563;
		    }
		    
		    .wpsafelink-help-content p {
			    margin: 0 0 16px 0;
		    }
		    
		    .wpsafelink-help-content p:last-child {
			    margin-bottom: 0;
		    }
		    
		    .wpsafelink-help-content strong {
			    color: #111827;
			    font-weight: 600;
		    }
		    
		    .wpsafelink-help-content ul {
			    margin: 12px 0;
			    padding-left: 20px;
		    }
		    
		    .wpsafelink-help-content li {
			    margin: 6px 0;
			    color: #4b5563;
		    }
		    
		    .wpsafelink-help-content .highlight {
			    background: #fef3c7;
			    padding: 2px 6px;
			    border-radius: 4px;
			    color: #92400e;
			    font-weight: 500;
		    }
		    
		    @keyframes fadeIn {
			    from { opacity: 0; }
			    to { opacity: 1; }
		    }
		    
		    @keyframes slideUp {
			    from {
				    transform: translateY(20px);
				    opacity: 0;
			    }
			    to {
				    transform: translateY(0);
				    opacity: 1;
			    }
		    }
		    
		    @media (max-width: 640px) {
			    .wpsafelink-help-popup {
				    width: 95%;
				    max-width: none;
				    margin: 20px;
			    }
			    
			    .wpsafelink-help-content {
				    padding: 16px;
			    }
			    
			    .wpsafelink-help-header {
				    padding: 16px;
			    }
		    }
		    ';
	    }
	    
	    /**
	     * Get help system JavaScript
	     *
	     * @access  private
	     * @return  string
	     * @since   5.2.3
	     */
	    private function get_help_system_script() {
		    return '
		    jQuery(document).ready(function($) {
			    // Initialize WP Safelink Help System
			    function initWPSafelinkHelpSystem() {
				    // Create overlay container if not exists
				    if (!$("#wpsafelink-help-overlay").length) {
					    $("body").append(\'<div id="wpsafelink-help-overlay" class="wpsafelink-help-overlay"></div>\');
				    }
				    
				    // Handle help icon clicks
				    $(document).on("click", ".wpsafelink-help-icon", function(e) {
					    e.preventDefault();
					    e.stopPropagation();
					    
					    var helpContent = $(this).data("help");
					    var helpTitle = $(this).data("help-title") || "Help Information";
					    
					    if (!helpContent) return;
					    
					    // Create popup HTML
					    var popupHtml = \'<div class="wpsafelink-help-popup">\' +
						    \'<div class="wpsafelink-help-header">\' +
							    \'<h3 class="wpsafelink-help-title">\' + helpTitle + \'</h3>\' +
							    \'<button class="wpsafelink-help-close"></button>\' +
						    \'</div>\' +
						    \'<div class="wpsafelink-help-content">\' +
							    helpContent +
						    \'</div>\' +
					    \'</div>\';
					    
					    // Add popup to overlay
					    $("#wpsafelink-help-overlay").html(popupHtml).addClass("active");
				    });
				    
				    // Close popup on overlay click
				    $(document).on("click", "#wpsafelink-help-overlay", function(e) {
					    if ($(e.target).hasClass("wpsafelink-help-overlay")) {
						    closeHelpPopup();
					    }
				    });
				    
				    // Close popup on close button click
				    $(document).on("click", ".wpsafelink-help-close", function(e) {
					    e.preventDefault();
					    closeHelpPopup();
				    });
				    
				    // Close popup on ESC key
				    $(document).on("keydown", function(e) {
					    if (e.keyCode === 27 && $("#wpsafelink-help-overlay").hasClass("active")) {
						    closeHelpPopup();
					    }
				    });
				    
				    // Close popup function
				    function closeHelpPopup() {
					    $("#wpsafelink-help-overlay").removeClass("active");
					    setTimeout(function() {
						    $("#wpsafelink-help-overlay").html("");
					    }, 300);
				    }
			    }
	
			    // Initialize the help system
			    initWPSafelinkHelpSystem();

			    // Template-based field visibility
			    function handleTemplateSpecificFields() {
				    var $template = $("#template");

				    if (!$template.length) return;

				    function updateFieldVisibility() {
					    var currentTemplate = $template.val();

					    // Auto Integration fields (template2 and template3 only)
					    var $autoIntegrationTitle = $(".auto-integration-section").closest(".wpsafelink-form-row");
					    var $autoIntegrationFields = $(".wpsafelink-form-row").filter(function() {
						    return $(this).find("[id^=\"auto_integration_\"]").length > 0;
					    });

					    // Skip Verification field (template3 only)
					    var $skipVerificationField = $("#skip_verification").closest(".wpsafelink-form-row");

					    // Verification on Homepage field (template2 only)
					    var $verificationHomepageField = $("#verification_homepage").closest(".wpsafelink-form-row");

					    // Hide all conditional fields first
					    $autoIntegrationTitle.hide();
					    $autoIntegrationFields.hide();

					    // Remove any existing messages
					    $(".auto-integration-info, .auto-integration-warning, #auto-integration-not-needed").remove();

					    // Show/hide based on template
					    if (currentTemplate === "template2" || currentTemplate === "template3") {
						    // Show Auto Integration for template2 and template3
						    $autoIntegrationTitle.slideDown(200);
						    $("#auto_integration_enable").closest(".wpsafelink-form-row").slideDown(200);

						    // If auto integration is enabled, show its settings
						    if ($("#auto_integration_enable").is(":checked")) {
							    $autoIntegrationFields.filter(function() {
								    return $(this).find("#auto_integration_enable").length === 0;
							    }).slideDown(200);
						    }

						    // Template-specific fields
						    if (currentTemplate === "template2") {
							    $verificationHomepageField.slideDown(200);
							    $skipVerificationField.hide();
						    } else if (currentTemplate === "template3") {
							    $skipVerificationField.slideDown(200);
							    $verificationHomepageField.hide();
						    }
					    } else {
						    // Hide template2/3 specific fields
						    $skipVerificationField.hide();
						    $verificationHomepageField.hide();

						    // Show informational message for template1/4
						    if (currentTemplate) {
							    var templateNum = currentTemplate.replace("template", "");
							    var infoMessage = \'<div id="auto-integration-not-needed" class="notice notice-info" style="margin: 15px 0;">\' +
								    \'<p><strong>Note:</strong> Template \' + templateNum +
								    \' works independently without theme integration. Auto Integration is only available for Template 2 and Template 3.</p></div>\';

							    // Insert message after the countdown settings
							    var $countdownShowText = $("#countdown_show_text").closest(".wpsafelink-form-row");
							    if ($countdownShowText.length) {
								    $countdownShowText.after(infoMessage);
							    }
						    }
					    }
				    }

				    // Handle Auto Integration enable checkbox
				    function handleAutoIntegrationToggle() {
					    var $checkbox = $("#auto_integration_enable");

					    if (!$checkbox.length) return;

					    $checkbox.on("change", function() {
						    var $settingsRows = $(".wpsafelink-form-row").filter(function() {
							    var $input = $(this).find("[id^=\"auto_integration_\"]");
							    return $input.length > 0 && $input.attr("id") !== "auto_integration_enable";
						    });

						    if ($(this).is(":checked")) {
							    $settingsRows.slideDown(200);
						    } else {
							    $settingsRows.slideUp(200);
						    }
					    });
				    }

				    // Initialize
				    updateFieldVisibility();
				    handleAutoIntegrationToggle();

				    // Handle template change
				    $template.on("change", function() {
					    updateFieldVisibility();
				    });
			    }

			    // Initialize template-specific field handling
			    handleTemplateSpecificFields();
		    });
		    ';
	    }
    }

    new WPSafelink_Settings();

endif;
