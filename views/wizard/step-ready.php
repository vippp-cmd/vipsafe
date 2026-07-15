<?php
/**
 * Ready step template
 *
 * @package WP_Safelink
 */

if (!defined('ABSPATH')) {
    exit;
}

// Get default settings from settings class
$settings_class = new WPSafelink_Settings();
$default_options = array();
$default_options = array_merge($default_options, $settings_class->get_general_options('general'));

// Extract default values
$defaults = array();
foreach ($default_options as $key => $option) {
    if (isset($option['default'])) {
        $defaults[$key] = $option['default'];
    }
}

// Get current options and merge with defaults
$options = wpsafelink_options();
if (empty($options)) {
    $options = $defaults;
} else {
    // Merge defaults with existing options (existing values take precedence)
    $options = array_merge($defaults, $options);
}
?>

<div class="wpsafelink-wizard-step wpsafelink-wizard-ready">
    <div class="wpsafelink-wizard-success-icon">
        <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="10" class="wpsafelink-success-circle"></circle>
            <path d="M8 12l2 2 4-4" class="wpsafelink-success-check"></path>
        </svg>
    </div>

    <h2><?php esc_html_e('You\'re All Set!', 'wp-safelink'); ?></h2>
    <p class="wpsafelink-wizard-subtitle">
        <?php esc_html_e('WP Safelink has been successfully configured and is ready to protect your links.', 'wp-safelink'); ?>
    </p>

    <div class="wpsafelink-wizard-summary">
        <h3><?php esc_html_e('Configuration Summary', 'wp-safelink'); ?></h3>
        <ul class="wpsafelink-wizard-summary-list">
            <li>
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="20 6 9 17 4 12"></polyline>
                </svg>
                <?php esc_html_e('License activated successfully', 'wp-safelink'); ?>
            </li>
            <li>
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="20 6 9 17 4 12"></polyline>
                </svg>
                <?php
                $permalink_mode = array(
                    1 => __('Path mode', 'wp-safelink'),
                    2 => __('Query mode', 'wp-safelink'),
                    3 => __('Raw mode', 'wp-safelink')
                );
                $permalink_value = isset($options['permalink']) ? $options['permalink'] : '1';
                printf(
                    esc_html__('URL structure configured (%s)', 'wp-safelink'),
                    esc_html($permalink_mode[$permalink_value] ?? __('Path mode', 'wp-safelink'))
                );
                ?>
            </li>
            <?php if (isset($options['enable_encrypt']) && $options['enable_encrypt'] === 'yes') : ?>
            <li>
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="20 6 9 17 4 12"></polyline>
                </svg>
                <?php esc_html_e('Link encryption enabled', 'wp-safelink'); ?>
            </li>
            <?php endif; ?>
            <?php if (isset($options['enable_captcha']) && $options['enable_captcha'] === 'yes') : ?>
            <li>
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="20 6 9 17 4 12"></polyline>
                </svg>
                <?php esc_html_e('CAPTCHA protection activated', 'wp-safelink'); ?>
            </li>
            <?php endif; ?>
            <?php if (isset($options['enable_countdown']) && $options['enable_countdown'] === 'yes') : ?>
            <li>
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="20 6 9 17 4 12"></polyline>
                </svg>
                <?php
                printf(
                    esc_html__('Countdown timer set to %d seconds', 'wp-safelink'),
                    intval($options['countdown_duration'] ?? 10)
                );
                ?>
            </li>
            <?php endif; ?>
        </ul>
    </div>

    <div class="wpsafelink-wizard-next-steps">
        <h3><?php esc_html_e('Next Steps', 'wp-safelink'); ?></h3>
        <div class="wpsafelink-wizard-next-steps-grid">
            <a href="<?php echo esc_url(admin_url('admin.php?page=wpsafelink&tab=generator')); ?>" class="wpsafelink-wizard-next-step-card">
                <div class="wpsafelink-wizard-next-step-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"></path>
                        <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"></path>
                    </svg>
                </div>
                <h4><?php esc_html_e('Generate Your First Link', 'wp-safelink'); ?></h4>
                <p><?php esc_html_e('Create your first protected safelink', 'wp-safelink'); ?></p>
            </a>

            <a href="<?php echo esc_url(admin_url('admin.php?page=wpsafelink&tab=template')); ?>" class="wpsafelink-wizard-next-step-card">
                <div class="wpsafelink-wizard-next-step-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                        <line x1="3" y1="9" x2="21" y2="9"></line>
                        <line x1="9" y1="21" x2="9" y2="9"></line>
                    </svg>
                </div>
                <h4><?php esc_html_e('Customize Templates', 'wp-safelink'); ?></h4>
                <p><?php esc_html_e('Design your safelink page appearance', 'wp-safelink'); ?></p>
            </a>

            <a href="<?php echo esc_url(admin_url('admin.php?page=wpsafelink&tab=adsense')); ?>" class="wpsafelink-wizard-next-step-card">
                <div class="wpsafelink-wizard-next-step-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="12" y1="1" x2="12" y2="23"></line>
                        <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                    </svg>
                </div>
                <h4><?php esc_html_e('Set Up Monetization', 'wp-safelink'); ?></h4>
                <p><?php esc_html_e('Configure ads to earn from your traffic', 'wp-safelink'); ?></p>
            </a>

            <a href="https://themeson.com/docs/wp-safelink/?utm_source=wp-admin&utm_medium=plugin&utm_campaign=wp-safelink&utm_content=wizard-complete" target="_blank" class="wpsafelink-wizard-next-step-card">
                <div class="wpsafelink-wizard-next-step-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path>
                        <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path>
                    </svg>
                </div>
                <h4><?php esc_html_e('Read Documentation', 'wp-safelink'); ?></h4>
                <p><?php esc_html_e('Learn advanced features and best practices', 'wp-safelink'); ?></p>
            </a>
        </div>
    </div>

    <div class="wpsafelink-wizard-support-box">
        <div class="wpsafelink-wizard-support-icon">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"></circle>
                <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path>
                <line x1="12" y1="17" x2="12.01" y2="17"></line>
            </svg>
        </div>
        <div class="wpsafelink-wizard-support-content">
            <h4><?php esc_html_e('Need Help?', 'wp-safelink'); ?></h4>
            <p><?php esc_html_e('Our support team is here to assist you with any questions.', 'wp-safelink'); ?></p>
            <div class="wpsafelink-wizard-support-links">
                <a href="https://themeson.com/support/?utm_source=wp-admin&utm_medium=plugin&utm_campaign=wp-safelink&utm_content=wizard-support" target="_blank">
                    <?php esc_html_e('Contact Support', 'wp-safelink'); ?> →
                </a>
                <a href="https://themeson.com/docs/wp-safelink/?utm_source=wp-admin&utm_medium=plugin&utm_campaign=wp-safelink&utm_content=wizard-faq" target="_blank">
                    <?php esc_html_e('View FAQ', 'wp-safelink'); ?> →
                </a>
            </div>
        </div>
    </div>

    <div class="wpsafelink-wizard-actions">
        <a href="<?php echo esc_url(admin_url('admin.php?page=wpsafelink&wizard_completed=1')); ?>" class="wpsafelink-button wpsafelink-button-primary wpsafelink-button-large">
            <?php esc_html_e('Go to Dashboard', 'wp-safelink'); ?>
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                <polyline points="9 22 9 12 15 12 15 22"></polyline>
            </svg>
        </a>
    </div>
</div>