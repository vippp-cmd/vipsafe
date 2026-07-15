<?php
/**
 * Setup Wizard Class
 *
 * @package WP_Safelink
 * @since 5.3.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * WPSafelink_Setup_Wizard class.
 */
class WPSafelink_Setup_Wizard {

    /**
     * Current step
     *
     * @var string
     */
    private $step = '';

    /**
     * Steps for the wizard
     *
     * @var array
     */
    private $steps = array();

    /**
     * Constructor
     */
    public function __construct() {
        $this->steps = array(
            'welcome' => array(
                'name' => __('Welcome', 'wp-safelink'),
                'view' => array($this, 'welcome_step'),
                'handler' => '',
            ),
            'license' => array(
                'name' => __('License Activation', 'wp-safelink'),
                'view' => array($this, 'license_step'),
                'handler' => array($this, 'license_save'),
            ),
            'configuration' => array(
                'name' => __('Configuration', 'wp-safelink'),
                'view' => array($this, 'configuration_step'),
                'handler' => array($this, 'configuration_save'),
            ),
            'features' => array(
                'name' => __('Test Link', 'wp-safelink'),
                'view' => array($this, 'features_step'),
                'handler' => array($this, 'features_save'),
            ),
        );

        add_action('admin_menu', array($this, 'admin_menus'));
        add_action('admin_init', array($this, 'setup_wizard'));
        add_action('wp_ajax_wpsafelink_wizard_save_step', array($this, 'save_step'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
    }

    /**
     * Add admin menus/screens
     */
    public function admin_menus() {
        add_dashboard_page('', '', 'manage_options', 'wpsafelink-setup', '');
    }

    /**
     * Show the setup wizard
     */
    public function setup_wizard() {
        if (empty($_GET['page']) || 'wpsafelink-setup' !== $_GET['page']) {
            return;
        }

        $this->step = isset($_GET['step']) ? sanitize_key($_GET['step']) : current(array_keys($this->steps));

        if (!empty($_POST['save_step']) && isset($this->steps[$this->step]['handler'])) {
            call_user_func($this->steps[$this->step]['handler']);
        }

        ob_start();
        $this->setup_wizard_header();
        $this->setup_wizard_steps();
        $this->setup_wizard_content();
        $this->setup_wizard_footer();
        exit;
    }

    /**
     * Enqueue scripts and styles
     */
    public function enqueue_scripts($hook) {
        // Only enqueue on the wizard page
        if (empty($_GET['page']) || 'wpsafelink-setup' !== $_GET['page']) {
            return;
        }

        // Set current step if not already set
        if (empty($this->step)) {
            $this->step = isset($_GET['step']) ? sanitize_key($_GET['step']) : 'welcome';
        }

        wp_enqueue_style('wpsafelink-wizard', wpsafelink_plugin_url() . '/assets/css/wizard.css', array(), '5.3.0');
        wp_enqueue_script('wpsafelink-wizard', wpsafelink_plugin_url() . '/assets/js/wizard.js', array('jquery'), '5.3.0', true);

        wp_localize_script('wpsafelink-wizard', 'wpsafelink_wizard', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wpsafelink-wizard-nonce'),
            'generate_nonce' => wp_create_nonce('wpsafelink_generate_link'),
            'steps' => array_keys($this->steps),
            'current_step' => $this->step,
            'i18n' => array(
                'error' => __('An error occurred. Please try again.', 'wp-safelink'),
                'saving' => __('Saving...', 'wp-safelink'),
                'saved' => __('Saved!', 'wp-safelink'),
                'activating' => __('Activating license...', 'wp-safelink'),
                'activated' => __('License activated!', 'wp-safelink'),
                'invalid_license' => __('Invalid license key. Please check and try again.', 'wp-safelink'),
            ),
        ));
    }

    /**
     * Setup wizard header
     */
    public function setup_wizard_header() {
        set_current_screen();
        ?>
        <!DOCTYPE html>
        <html <?php language_attributes(); ?>>
        <head>
            <meta name="viewport" content="width=device-width, initial-scale=1.0" />
            <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
            <title><?php esc_html_e('WP Safelink Setup', 'wp-safelink'); ?></title>
            <?php do_action('admin_enqueue_scripts'); ?>
            <?php do_action('admin_print_styles'); ?>
            <?php do_action('admin_head'); ?>

            <style id="wpsafelink-wizard-inline-styles">
                /* Loading Spinner */
                .wpsafelink-wizard-loading {
                    display: inline-block;
                    width: 16px;
                    height: 16px;
                    border: 2px solid rgba(255, 255, 255, 0.3);
                    border-radius: 50%;
                    border-top-color: #fff;
                    animation: spin 0.6s linear infinite;
                }

                @keyframes spin {
                    to { transform: rotate(360deg); }
                }

                /* License Status Styles */
                .wpsafelink-wizard-license-status {
                    margin-top: 20px;
                    padding: 16px;
                    border-radius: 8px;
                    background: #f0f9ff;
                    border: 1px solid #bae6fd;
                }

                .wpsafelink-wizard-license-status.success {
                    background: #f0fdf4;
                    border-color: #86efac;
                }

                .wpsafelink-wizard-license-status.error {
                    background: #fef2f2;
                    border-color: #fca5a5;
                }

                .wpsafelink-wizard-license-status-content {
                    display: flex;
                    align-items: center;
                    gap: 12px;
                }

                .wpsafelink-wizard-license-status-icon {
                    flex-shrink: 0;
                }

                .wpsafelink-wizard-license-status-icon.success {
                    color: #22c55e;
                }

                .wpsafelink-wizard-license-status-icon.error {
                    color: #ef4444;
                }

                .wpsafelink-wizard-license-status-message {
                    flex-grow: 1;
                    font-size: 14px;
                    font-weight: 500;
                }

                .wpsafelink-wizard-license-status.success .status-text {
                    color: #16a34a;
                }

                .wpsafelink-wizard-license-status.error .status-text {
                    color: #dc2626;
                }

                /* Pricing Card Hover Effects */
                .wpsafelink-pricing-card {
                    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                }

                .wpsafelink-pricing-card:hover {
                    transform: translateY(-8px) scale(1.02);
                }

                .wpsafelink-pricing-featured:hover {
                    box-shadow: 0 20px 40px rgba(79, 70, 229, 0.2);
                }

                /* Button Press Effect */
                .wpsafelink-button:active {
                    transform: scale(0.98);
                }

                /* Help Tooltip Styles */
                .wpsafelink-help-tooltip {
                    background: #ffffff;
                    border: 1px solid #e5e7eb;
                    border-radius: 8px;
                    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
                    max-width: 300px;
                    animation: fadeInUp 0.2s ease-out;
                }

                @keyframes fadeInUp {
                    from {
                        opacity: 0;
                        transform: translateY(10px);
                    }
                    to {
                        opacity: 1;
                        transform: translateY(0);
                    }
                }

                .wpsafelink-help-tooltip-header {
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    padding: 12px 16px;
                    border-bottom: 1px solid #e5e7eb;
                    background: #f9fafb;
                    border-radius: 8px 8px 0 0;
                }

                .wpsafelink-help-tooltip-header h4 {
                    margin: 0;
                    font-size: 14px;
                    font-weight: 600;
                    color: #111827;
                }

                .wpsafelink-help-tooltip-close {
                    background: none;
                    border: none;
                    font-size: 20px;
                    line-height: 1;
                    color: #6b7280;
                    cursor: pointer;
                    padding: 0;
                    width: 24px;
                    height: 24px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    border-radius: 4px;
                    transition: all 0.2s;
                }

                .wpsafelink-help-tooltip-close:hover {
                    background: #e5e7eb;
                    color: #374151;
                }

                .wpsafelink-help-tooltip-content {
                    padding: 16px;
                    font-size: 13px;
                    line-height: 1.6;
                    color: #4b5563;
                }

                .wpsafelink-help-tooltip-content code {
                    background: #f3f4f6;
                    padding: 2px 4px;
                    border-radius: 3px;
                    font-size: 12px;
                    color: #111827;
                }

                .wpsafelink-help-icon {
                    cursor: pointer;
                    transition: transform 0.2s;
                }

                .wpsafelink-help-icon:hover {
                    transform: scale(1.1);
                }

                /* Notification Styles */
                .wpsafelink-wizard-notification {
                    position: fixed;
                    top: 20px;
                    right: 20px;
                    padding: 12px 20px;
                    background: #10b981;
                    color: #ffffff;
                    border-radius: 8px;
                    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                    z-index: 999999;
                    font-size: 14px;
                    font-weight: 500;
                }

                .wpsafelink-wizard-notification-error {
                    background: #ef4444;
                }
            </style>
        </head>
        <body class="wpsafelink-setup wp-core-ui">
        <?php
    }

    /**
     * Setup wizard footer
     */
    public function setup_wizard_footer() {
        ?>
            <?php do_action('admin_print_footer_scripts'); ?>
        </body>
        </html>
        <?php
    }

    /**
     * Setup wizard steps
     */
    public function setup_wizard_steps() {
        $output_steps = $this->steps;
        $plugin_data = get_plugin_data(wpsafelink_plugin_file());
        ?>
        <div class="wpsafelink-setup-wizard">
            <div class="wpsafelink-setup-wizard-header">
                <h1 class="wpsafelink-setup-wizard-logo">
                    <?php echo $plugin_data['Title']; ?>
                </h1>
            </div>
            <ul class="wpsafelink-setup-wizard-steps">
                <?php foreach ($output_steps as $step_key => $step) : ?>
                    <li class="<?php
                        if ($step_key === $this->step) {
                            echo 'active';
                        } elseif (array_search($this->step, array_keys($this->steps)) > array_search($step_key, array_keys($this->steps))) {
                            echo 'done';
                        }
                    ?>">
                        <span class="step-number"><?php echo array_search($step_key, array_keys($this->steps)) + 1; ?></span>
                        <span class="step-name"><?php echo esc_html($step['name']); ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php
    }

    /**
     * Setup wizard content
     */
    public function setup_wizard_content() {
        ?>
        <div class="wpsafelink-setup-wizard-content">
            <form method="post" class="wpsafelink-setup-wizard-form">
                <?php
                if (!empty($this->steps[$this->step]['view'])) {
                    call_user_func($this->steps[$this->step]['view']);
                }
                ?>
            </form>
        </div>
        </div>
        <?php
    }

    /**
     * Welcome step
     */
    public function welcome_step() {
        $wizard = $this; // Make $this available in included files
        include wpsafelink_plugin_path() . '/views/wizard/step-welcome.php';
    }

    /**
     * License step
     */
    public function license_step() {
        $wizard = $this;
        include wpsafelink_plugin_path() . '/views/wizard/step-license.php';
    }

    /**
     * Configuration step
     */
    public function configuration_step() {
        $wizard = $this;
        include wpsafelink_plugin_path() . '/views/wizard/step-configuration.php';
    }

    /**
     * Features step
     */
    public function features_step() {
        $wizard = $this;
        include wpsafelink_plugin_path() . '/views/wizard/step-features.php';
    }

    /**
     * Ready step
     */
    public function ready_step() {
        $wizard = $this;
        include wpsafelink_plugin_path() . '/views/wizard/step-ready.php';
    }

    /**
     * Get next step link
     */
    public function get_next_step_link() {
        $keys = array_keys($this->steps);
        $current_index = array_search($this->step, $keys);
        $next_index = $current_index + 1;

        if (isset($keys[$next_index])) {
            return add_query_arg('step', $keys[$next_index], remove_query_arg('save_step'));
        }

        // Mark wizard as completed
        update_option('wpsafelink_wizard_completed', true);

        return admin_url('admin.php?page=wpsafelink&wizard_completed=1');
    }

    /**
     * Save step via AJAX
     */
    public function save_step() {
        if (!check_ajax_referer('wpsafelink-wizard-nonce', 'nonce', false)) {
            wp_send_json_error('Invalid nonce');
        }

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }

        $step = isset($_POST['step']) ? sanitize_text_field($_POST['step']) : '';
        $data = isset($_POST['data']) ? $_POST['data'] : array();

        // Process step data
        switch ($step) {
            case 'license':
                // Get license key directly from POST data
                $license_key = isset($_POST['license_key']) ? sanitize_text_field($_POST['license_key']) : '';
                $domain = isset($_POST['domain']) ? sanitize_text_field($_POST['domain']) : '';

                if (empty($license_key)) {
                    wp_send_json_error('License key is required');
                    return;
                }

                $options = wpsafelink_options();
                $options['license'] = $license_key;

                // Validate license using core function
                $validation = $this->validate_license($license_key);

                if ($validation['success']) {
                    $options['license_status'] = 'active';
                    $options['license_data'] = $validation['data'];

                    // Store the license type (PRO or FULL)
                    $license_type = isset($validation['data']['license_type']) ? $validation['data']['license_type'] : 'FULL';
                    $options['license_type'] = $license_type;

                    // Get default settings from settings class
                    $settings_class = new WPSafelink_Settings();

                    // Get all default options groups
                    $default_options = array();
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
                    foreach ($all_settings as $setting) {
                        $default_options = array_merge($default_options, $settings_class->get_general_options($setting));
                    }
                    $update_options = [];
                    foreach ($default_options as $key => $value) {
                        $disabled = $value['disabled'] ?? false;
                        if ($disabled) {
                            continue;
                        }
                        $update_options[$key] = $value['default'] ?? "";
                    }

                    if (empty($options)) {
                        $options = array();
                    } else {
                        $options = array_merge($update_options, $options);
                    }
                    update_option('wpsafelink_settings', $options);

                    $success_message = sprintf(
                        'License activated successfully! You have the %s version.',
                        $license_type
                    );

                    wp_send_json_success(array(
                        'message' => $success_message,
                        'license_type' => $license_type,
                        'next_step' => 'configuration'
                    ));
                } else {
                    wp_send_json_error($validation['message']);
                }
                return;
                break;

            case 'configuration':
                // Get default settings from settings class
                $settings_class = new WPSafelink_Settings();

                // Get all default options groups
                $default_options = array();
                $default_options = array_merge($default_options, $settings_class->get_general_options('general'));
                $default_options = array_merge($default_options, $settings_class->get_general_options('templates'));

                // Extract default values
                $defaults = array();
                foreach ($default_options as $key => $option) {
                    if (isset($option['default'])) {
                        $defaults[$key] = $option['default'];
                    }
                }

                // Start with current options or defaults
                $options = wpsafelink_options();
                if (empty($options)) {
                    $options = $defaults;
                } else {
                    // Merge defaults with existing options (existing values take precedence)
                    $options = array_merge($defaults, $options);
                }

                // Parse the serialized form data
                parse_str($data, $parsed_data);

                // Save permalink settings
                if (isset($parsed_data['permalink'])) {
                    $options['permalink'] = intval($parsed_data['permalink']);
                }
                if (isset($parsed_data['permalink_parameter'])) {
                    $options['permalink_parameter'] = sanitize_text_field($parsed_data['permalink_parameter']);
                }

                // Save template settings
                if (isset($parsed_data['template'])) {
                    $options['template'] = sanitize_text_field($parsed_data['template']);
                }

                // Save skip verification for template3
                if (isset($parsed_data['skip_verification'])) {
                    $options['skip_verification'] = sanitize_text_field($parsed_data['skip_verification']);
                }

                // Save auto integration settings for template2/3
                if (isset($parsed_data['auto_integration_enable'])) {
                    $options['auto_integration_enable'] = sanitize_text_field($parsed_data['auto_integration_enable']);
                }
                if (isset($parsed_data['auto_integration_top_placement'])) {
                    $options['auto_integration_top_placement'] = sanitize_text_field($parsed_data['auto_integration_top_placement']);
                }
                if (isset($parsed_data['auto_integration_bottom_placement'])) {
                    $options['auto_integration_bottom_placement'] = sanitize_text_field($parsed_data['auto_integration_bottom_placement']);
                }

                update_option('wpsafelink_settings', $options);
                break;

            case 'features':
                // Get default settings from settings class
                $settings_class = new WPSafelink_Settings();

                // Get all default options groups
                $default_options = array();
                $default_options = array_merge($default_options, $settings_class->get_general_options('general'));
                $default_options = array_merge($default_options, $settings_class->get_general_options('auto-generate-link'));
                $default_options = array_merge($default_options, $settings_class->get_general_options('captcha'));
                $default_options = array_merge($default_options, $settings_class->get_general_options('advertisement'));
                $default_options = array_merge($default_options, $settings_class->get_general_options('styles'));

                // Extract default values
                $defaults = array();
                foreach ($default_options as $key => $option) {
                    if (isset($option['default'])) {
                        $defaults[$key] = $option['default'];
                    }
                }

                // Start with current options or defaults
                $options = wpsafelink_options();
                if (empty($options)) {
                    $options = $defaults;
                } else {
                    // Merge defaults with existing options (existing values take precedence)
                    $options = array_merge($defaults, $options);
                }

                // Parse the serialized form data if it's a string
                if (is_string($data)) {
                    parse_str($data, $parsed_data);
                } else {
                    $parsed_data = $data;
                }

                // Save features - use parsed_data instead of $data
                if (isset($parsed_data['enable_encrypt'])) {
                    $options['enable_encrypt'] = sanitize_text_field($parsed_data['enable_encrypt']);
                }
                if (isset($parsed_data['enable_captcha'])) {
                    $options['enable_captcha'] = sanitize_text_field($parsed_data['enable_captcha']);
                }
                if (isset($parsed_data['enable_ads'])) {
                    $options['enable_ads'] = sanitize_text_field($parsed_data['enable_ads']);
                }
                if (isset($parsed_data['enable_countdown'])) {
                    $options['enable_countdown'] = sanitize_text_field($parsed_data['enable_countdown']);
                }
                if (isset($parsed_data['countdown_duration'])) {
                    $options['countdown_duration'] = intval($parsed_data['countdown_duration']);
                }

                update_option('wpsafelink_settings', $options);
                break;
        }

        // Get next step
        $keys = array_keys($this->steps);
        $current_index = array_search($step, $keys);
        $next_index = $current_index + 1;

        if (isset($keys[$next_index])) {
            wp_send_json_success(array(
                'next_step' => $keys[$next_index],
            ));
        } else {
            // Wizard completed
            update_option('wpsafelink_wizard_completed', true);
            wp_send_json_success(array(
                'redirect' => admin_url('admin.php?page=wpsafelink&wizard_completed=1'),
            ));
        }
    }

    /**
     * Validate license with Themeson servers
     *
     * @param string $license_key
     * @return array
     */
    private function validate_license($license_key) {
        global $wpsafelink_core;

        // Use the core license validation function with force check
        $validation = $wpsafelink_core->license($license_key, false, true);

        if (isset($validation['success']) && $validation['success']) {
            // License is valid
            $license_data = isset($validation['data']) ? (array)$validation['data'] : array();

            // Check if this is a PRO license
            $is_pro = $wpsafelink_core->is_pro();
            $license_type = 'FULL';

            if ($is_pro) {
                $license_type = 'PRO';
            }

            return array(
                'success' => true,
                'data' => array(
                    'license_key' => $license_key,
                    'status' => 'active',
                    'license_type' => $license_type,
                    'domain' => str_replace(['https://', 'http://'], '', home_url()),
                    'last_check' => current_time('mysql'),
                    'msg' => isset($license_data['msg']) ? $license_data['msg'] : array()
                )
            );
        }

        // Check for specific error messages
        $error_message = 'Invalid license key or license has expired';

        if (isset($validation['data']) && is_object($validation['data'])) {
            $data = (array)$validation['data'];
            if (isset($data['msg']) && is_string($data['msg'])) {
                $error_message = $data['msg'];
            } elseif (isset($data['status']) && $data['status'] === 'error') {
                $error_message = 'License validation failed. Please check your license key.';
            }
        }

        return array(
            'success' => false,
            'message' => $error_message
        );
    }

    /**
     * Store license cache
     *
     * @param array $license_data
     * @deprecated Use core license storage instead
     */
    private function store_license_cache($license_data) {
        // This method is deprecated as the core class handles license storage
        // in wp_options with proper encryption
        return;
    }
}

// Initialize the wizard
new WPSafelink_Setup_Wizard();