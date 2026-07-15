<?php
/**
 * Welcome step template
 *
 * @package WP_Safelink
 */

if (!defined('ABSPATH')) {
    exit;
}

// Check ionCube requirement
$ioncube_installed = extension_loaded('ionCube Loader');
$ioncube_version = $ioncube_installed ? phpversion('ionCube Loader') : '';
$ioncube_ok = defined('WPSAFELINK_IONCUBE_OK') ? WPSAFELINK_IONCUBE_OK : false;
?>

<div class="wpsafelink-wizard-step wpsafelink-wizard-welcome">
    <div class="wpsafelink-wizard-hero">
        <h1><?php esc_html_e('Welcome to WP Safelink', 'wp-safelink'); ?></h1>
        <p class="wpsafelink-wizard-tagline">
            <?php esc_html_e('Convert every external link into monetized safelink pages with ad placements.', 'wp-safelink'); ?>
        </p>
    </div>

    <div class="wpsafelink-wizard-benefits">
        <div class="wpsafelink-wizard-benefit">
            <div class="wpsafelink-wizard-benefit-icon">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                    <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                </svg>
            </div>
            <h3><?php esc_html_e('Anti-Adblock Technology', 'wp-safelink'); ?></h3>
            <p><?php esc_html_e('Your ads display to 100% of visitors, not just the 60% without adblockers. All links are encrypted with AES-256 before redirection.', 'wp-safelink'); ?></p>
        </div>

        <div class="wpsafelink-wizard-benefit">
            <div class="wpsafelink-wizard-benefit-icon">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect>
                    <line x1="8" y1="21" x2="16" y2="21"></line>
                    <line x1="12" y1="17" x2="12" y2="21"></line>
                </svg>
            </div>
            <h3><?php esc_html_e('Multiple Page System', 'wp-safelink'); ?></h3>
            <p><?php esc_html_e('One click generates 3-5 page views with different ad placements, multiplying your revenue per visitor.', 'wp-safelink'); ?></p>
        </div>

        <div class="wpsafelink-wizard-benefit">
            <div class="wpsafelink-wizard-benefit-icon">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                </svg>
            </div>
            <h3><?php esc_html_e('Bot & VPN Protection', 'wp-safelink'); ?></h3>
            <p><?php esc_html_e('Intelligent detection filters out traffic from bots, proxies, and VPNs to protect your AdSense account.', 'wp-safelink'); ?></p>
        </div>
    </div>

    <?php if (!$ioncube_ok) : ?>
    <!-- ionCube Requirement Warning -->
    <div class="wpsafelink-wizard-ioncube-warning" style="
        background: #fef2f2;
        border: 1px solid #fca5a5;
        border-radius: 12px;
        padding: 24px;
        margin: 0 auto 32px;
        max-width: 600px;
        text-align: left;
    ">
        <div style="display: flex; align-items: flex-start; gap: 16px;">
            <div style="
                flex-shrink: 0;
                width: 48px;
                height: 48px;
                background: #fee2e2;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
            ">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="8" x2="12" y2="12"></line>
                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                </svg>
            </div>
            <div style="flex: 1;">
                <h3 style="margin: 0 0 8px; font-size: 16px; font-weight: 600; color: #991b1b;">
                    <?php esc_html_e('System Requirements Not Met', 'wp-safelink'); ?>
                </h3>
                <p style="margin: 0 0 16px; color: #b91c1c; font-size: 14px; line-height: 1.5;">
                    <?php esc_html_e('WP Safelink requires ionCube Loader to function. Please install ionCube Loader before continuing with the setup.', 'wp-safelink'); ?>
                </p>

                <table style="
                    width: 100%;
                    border-collapse: collapse;
                    background: #fff;
                    border-radius: 8px;
                    overflow: hidden;
                    margin-bottom: 16px;
                    font-size: 14px;
                ">
                    <tr style="border-bottom: 1px solid #fecaca;">
                        <td style="padding: 12px 16px; font-weight: 600; color: #374151; width: 40%;"><?php esc_html_e('Requirement', 'wp-safelink'); ?></td>
                        <td style="padding: 12px 16px; color: #374151;">ionCube Loader 14.0.0+</td>
                    </tr>
                    <tr style="border-bottom: 1px solid #fecaca;">
                        <td style="padding: 12px 16px; font-weight: 600; color: #374151;"><?php esc_html_e('Current Status', 'wp-safelink'); ?></td>
                        <td style="padding: 12px 16px; color: #dc2626;">
                            <?php if ($ioncube_installed) : ?>
                                <?php echo esc_html(sprintf(__('Installed (v%s) - Update Required', 'wp-safelink'), $ioncube_version)); ?>
                            <?php else : ?>
                                <?php esc_html_e('Not Installed', 'wp-safelink'); ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 12px 16px; font-weight: 600; color: #374151;"><?php esc_html_e('PHP Version', 'wp-safelink'); ?></td>
                        <td style="padding: 12px 16px; color: #374151;"><?php echo esc_html(PHP_VERSION); ?></td>
                    </tr>
                </table>

                <p style="margin: 0; font-size: 13px; color: #6b7280; line-height: 1.5;">
                    <strong><?php esc_html_e('How to fix:', 'wp-safelink'); ?></strong>
                    <?php esc_html_e('Contact your hosting provider to install ionCube Loader 14.0.0 or higher, or visit', 'wp-safelink'); ?>
                    <a href="https://www.ioncube.com/loaders.php" target="_blank" rel="noopener" style="color: #1ABC9C;">ioncube.com/loaders.php</a>
                </p>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div class="wpsafelink-wizard-actions">
        <?php if ($ioncube_ok) : ?>
            <a href="<?php echo esc_url($wizard->get_next_step_link()); ?>" class="wpsafelink-button wpsafelink-button-primary wpsafelink-button-hero">
                <?php esc_html_e('Let\'s Get Started', 'wp-safelink'); ?>
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                    <polyline points="12 5 19 12 12 19"></polyline>
                </svg>
            </a>
            <p class="wpsafelink-wizard-skip">
                <?php esc_html_e('Setup will take less than 2 minutes.', 'wp-safelink'); ?><br>
            </p>
        <?php else : ?>
            <!-- Disabled button when ionCube not available -->
            <span class="wpsafelink-button wpsafelink-button-hero" style="
                background: #9ca3af;
                color: #fff;
                cursor: not-allowed;
                opacity: 0.7;
                pointer-events: none;
            ">
                <?php esc_html_e('Let\'s Get Started', 'wp-safelink'); ?>
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                    <polyline points="12 5 19 12 12 19"></polyline>
                </svg>
            </span>

            <!-- Refresh button -->
            <button type="button" onclick="window.location.reload();" class="wpsafelink-button wpsafelink-button-secondary" style="
                display: inline-flex;
                align-items: center;
                gap: 8px;
                margin-top: 16px;
                background: #fff;
                border: 2px solid #1ABC9C;
                color: #1ABC9C;
                padding: 14px 28px;
                border-radius: 8px;
                font-size: 15px;
                font-weight: 600;
                cursor: pointer;
                transition: all 0.2s ease;
            ">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="23 4 23 10 17 10"></polyline>
                    <path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"></path>
                </svg>
                <?php esc_html_e('Check Again', 'wp-safelink'); ?>
            </button>

            <p class="wpsafelink-wizard-skip" style="margin-top: 16px;">
                <?php esc_html_e('Install ionCube Loader and click "Check Again" to continue.', 'wp-safelink'); ?>
            </p>
        <?php endif; ?>
    </div>
</div>