<?php
/**
 * License step template
 *
 * @package WP_Safelink
 */

if (!defined('ABSPATH')) {
    exit;
}

$error = get_transient('wpsafelink_wizard_license_error');
$domain = str_replace(['https://', 'http://'], '', home_url());
?>

<div class="wpsafelink-wizard-step wpsafelink-wizard-license">
    <h2><?php esc_html_e('Choose Your Plan', 'wp-safelink'); ?></h2>
    <p class="wpsafelink-wizard-subtitle">
        <?php esc_html_e('Stop losing money to adblockers and bot traffic. Pick the plan that fits your needs.', 'wp-safelink'); ?>
    </p>

    <?php if ($error) : ?>
        <div class="wpsafelink-wizard-notice wpsafelink-wizard-notice-error">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="12" y1="8" x2="12" y2="12"></line>
                <line x1="12" y1="16" x2="12.01" y2="16"></line>
            </svg>
            <?php echo esc_html($error); ?>
        </div>
        <?php delete_transient('wpsafelink_wizard_license_error'); ?>
    <?php endif; ?>

    <!-- Social Proof -->
    <div class="wpsafelink-social-proof">
        <div class="wpsafelink-social-proof-item">
            <strong>1,400+</strong>
            <span><?php esc_html_e('Active Users', 'wp-safelink'); ?></span>
        </div>
        <div class="wpsafelink-social-proof-divider"></div>
        <div class="wpsafelink-social-proof-item">
            <strong>4.8</strong>
            <span><?php esc_html_e('User Rating', 'wp-safelink'); ?></span>
        </div>
        <div class="wpsafelink-social-proof-divider"></div>
        <div class="wpsafelink-social-proof-item">
            <strong><?php esc_html_e('Since 2017', 'wp-safelink'); ?></strong>
            <span><?php esc_html_e('Trusted Plugin', 'wp-safelink'); ?></span>
        </div>
    </div>

    <!-- Main Pricing Cards (3 tiers) -->
    <div class="wpsafelink-pricing-container">
        <!-- Lite Plan -->
        <div class="wpsafelink-pricing-card">
            <div class="wpsafelink-pricing-header">
                <h3 class="wpsafelink-pricing-title">Lite</h3>
                <div class="wpsafelink-pricing-price">
                    <span class="wpsafelink-price-current">$9</span>
                    <span class="wpsafelink-price-period">/year</span>
                </div>
            </div>
            <ul class="wpsafelink-pricing-features">
                <li><span class="wpsafelink-feature-icon wpsafelink-icon-check"></span> Auto Safelink</li>
                <li><span class="wpsafelink-feature-icon wpsafelink-icon-check"></span> Link Encryption</li>
                <li><span class="wpsafelink-feature-icon wpsafelink-icon-check"></span> 6 Page Templates</li>
                <li><span class="wpsafelink-feature-icon wpsafelink-icon-check"></span> 1 Domain License</li>
                <li><span class="wpsafelink-feature-icon wpsafelink-icon-check"></span> Community Support</li>
                <li class="wpsafelink-feature-disabled"><span class="wpsafelink-feature-icon wpsafelink-icon-x"></span> Anti-Adblock</li>
                <li class="wpsafelink-feature-disabled"><span class="wpsafelink-feature-icon wpsafelink-icon-x"></span> Multiple Pages</li>
            </ul>
            <a href="https://themeson.com/member/signup?product_id=29&utm_source=wp-admin&utm_medium=plugin&utm_campaign=wp-safelink&utm_content=wizard-lite" target="_blank" class="wpsafelink-pricing-button">
                <?php esc_html_e('Get Lite', 'wp-safelink'); ?>
            </a>
        </div>

        <!-- Standard Plan -->
        <div class="wpsafelink-pricing-card wpsafelink-pricing-popular">
            <div class="wpsafelink-pricing-badge"><?php esc_html_e('POPULAR', 'wp-safelink'); ?></div>
            <div class="wpsafelink-pricing-header">
                <h3 class="wpsafelink-pricing-title">Standard</h3>
                <div class="wpsafelink-pricing-price">
                    <span class="wpsafelink-price-current">$19</span>
                    <span class="wpsafelink-price-period">/year</span>
                </div>
            </div>
            <ul class="wpsafelink-pricing-features">
                <li class="wpsafelink-feature-highlight"><span class="wpsafelink-feature-icon wpsafelink-icon-check"></span> Everything in Lite, plus:</li>
                <li><span class="wpsafelink-feature-icon wpsafelink-icon-check"></span> Anti-Adblock Technology</li>
                <li><span class="wpsafelink-feature-icon wpsafelink-icon-check"></span> 3 Multiple Pages</li>
                <li><span class="wpsafelink-feature-icon wpsafelink-icon-check"></span> hCaptcha / reCaptcha</li>
                <li><span class="wpsafelink-feature-icon wpsafelink-icon-check"></span> Adlinkfly Integration</li>
                <li><span class="wpsafelink-feature-icon wpsafelink-icon-check"></span> 10 Domains</li>
                <li><span class="wpsafelink-feature-icon wpsafelink-icon-check"></span> Email Support</li>
            </ul>
            <a href="https://themeson.com/member/signup?product_id=7&utm_source=wp-admin&utm_medium=plugin&utm_campaign=wp-safelink&utm_content=wizard-standard" target="_blank" class="wpsafelink-pricing-button wpsafelink-pricing-button-popular">
                <?php esc_html_e('Get Standard', 'wp-safelink'); ?>
            </a>
        </div>

        <!-- PRO Plan -->
        <div class="wpsafelink-pricing-card wpsafelink-pricing-featured">
            <div class="wpsafelink-pricing-badge"><?php esc_html_e('RECOMMENDED', 'wp-safelink'); ?></div>
            <div class="wpsafelink-pricing-header">
                <h3 class="wpsafelink-pricing-title">PRO</h3>
                <div class="wpsafelink-pricing-price">
                    <span class="wpsafelink-price-current">$49</span>
                    <span class="wpsafelink-price-period">/year</span>
                </div>
            </div>
            <ul class="wpsafelink-pricing-features">
                <li class="wpsafelink-feature-highlight"><span class="wpsafelink-feature-icon wpsafelink-icon-star"></span> Everything in Standard, plus:</li>
                <li><span class="wpsafelink-feature-icon wpsafelink-icon-star"></span> Anti-Bot/VPN Protection</li>
                <li><span class="wpsafelink-feature-icon wpsafelink-icon-star"></span> Google Redirect PRO</li>
                <li><span class="wpsafelink-feature-icon wpsafelink-icon-star"></span> Infinite Multiple Pages</li>
                <li><span class="wpsafelink-feature-icon wpsafelink-icon-star"></span> API Access</li>
                <li><span class="wpsafelink-feature-icon wpsafelink-icon-star"></span> Priority Support</li>
                <li><span class="wpsafelink-feature-icon wpsafelink-icon-star"></span> Future PRO Features</li>
            </ul>
            <a href="https://themeson.com/member/signup?product_id=25&utm_source=wp-admin&utm_medium=plugin&utm_campaign=wp-safelink&utm_content=wizard-pro" target="_blank" class="wpsafelink-pricing-button wpsafelink-pricing-button-pro">
                <?php esc_html_e('Get PRO', 'wp-safelink'); ?>
            </a>
        </div>
    </div>

    <!-- Additional Plans -->
    <div class="wpsafelink-pricing-extras">
        <div class="wpsafelink-pricing-extra-card">
            <div class="wpsafelink-pricing-extra-content">
                <h4>Agency <span class="wpsafelink-pricing-extra-price">$99/year</span></h4>
                <p><?php esc_html_e('Everything in PRO + Unlimited Domains, White-Label Ready, Client Management, Dedicated Support', 'wp-safelink'); ?></p>
            </div>
            <a href="https://themeson.com/member/signup?product_id=30&utm_source=wp-admin&utm_medium=plugin&utm_campaign=wp-safelink&utm_content=wizard-agency" target="_blank" class="wpsafelink-pricing-extra-button">
                <?php esc_html_e('Get Agency', 'wp-safelink'); ?>
            </a>
        </div>
        <div class="wpsafelink-pricing-extra-card wpsafelink-pricing-extra-lifetime">
            <div class="wpsafelink-pricing-extra-content">
                <h4>Lifetime <span class="wpsafelink-pricing-extra-price">$199 one-time</span></h4>
                <p><?php esc_html_e('PRO features forever. No renewals, lifetime updates, 10 domains, priority support.', 'wp-safelink'); ?></p>
            </div>
            <a href="https://themeson.com/member/signup?product_id=31&utm_source=wp-admin&utm_medium=plugin&utm_campaign=wp-safelink&utm_content=wizard-lifetime" target="_blank" class="wpsafelink-pricing-extra-button">
                <?php esc_html_e('Get Lifetime', 'wp-safelink'); ?>
            </a>
        </div>
    </div>

    <!-- License Activation Form -->
    <div class="wpsafelink-wizard-form wpsafelink-license-form">
        <h3 class="wpsafelink-form-title"><?php esc_html_e('Already have a license?', 'wp-safelink'); ?></h3>

        <div class="wpsafelink-wizard-field-group">
            <label for="domain" class="wpsafelink-wizard-label">
                <?php esc_html_e('Domain', 'wp-safelink'); ?>
            </label>
            <input type="text" id="domain" name="domain" class="wpsafelink-wizard-input" value="<?php echo esc_attr($domain); ?>" readonly />
        </div>

        <div class="wpsafelink-wizard-field-group">
            <label for="license_key" class="wpsafelink-wizard-label">
                <?php esc_html_e('License Key', 'wp-safelink'); ?>
                <span class="wpsafelink-wizard-required">*</span>
            </label>
            <div class="wpsafelink-wizard-license-input-wrapper">
                <input type="text"
                       id="license_key"
                       name="license_key"
                       class="wpsafelink-wizard-input wpsafelink-wizard-license-input"
                       placeholder="Enter your license key here"
                       autocomplete="off"
                       required />
            </div>
        </div>

        <div class="wpsafelink-wizard-license-status" id="license-status" style="display: none;">
            <div class="wpsafelink-wizard-license-status-content">
                <svg class="wpsafelink-wizard-license-status-icon success" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display: none;">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                    <polyline points="22 4 12 14.01 9 11.01"></polyline>
                </svg>
                <svg class="wpsafelink-wizard-license-status-icon error" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display: none;">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="8" x2="12" y2="12"></line>
                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                </svg>
                <div class="wpsafelink-wizard-license-status-message">
                    <span class="status-text"></span>
                </div>
            </div>
        </div>
    </div>

    <style>
    /* Social Proof Bar */
    .wpsafelink-social-proof {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 24px;
        margin: 0 auto 32px;
        padding: 16px 24px;
        background: #f9fafb;
        border-radius: 12px;
        border: 1px solid #e5e7eb;
    }

    .wpsafelink-social-proof-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 2px;
    }

    .wpsafelink-social-proof-item strong {
        font-size: 18px;
        font-weight: 700;
        color: #111827;
        letter-spacing: -0.5px;
    }

    .wpsafelink-social-proof-item span {
        font-size: 12px;
        color: #6b7280;
        font-weight: 500;
    }

    .wpsafelink-social-proof-divider {
        width: 1px;
        height: 32px;
        background: #e5e7eb;
    }

    /* Pricing Cards Styles */
    .wpsafelink-pricing-container {
        display: flex;
        gap: 20px;
        margin: 32px 0 24px;
        justify-content: center;
        flex-wrap: wrap;
    }

    .wpsafelink-pricing-card {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 28px 20px;
        flex: 1;
        min-width: 220px;
        max-width: 280px;
        position: relative;
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .wpsafelink-pricing-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 24px rgba(0, 0, 0, 0.1);
    }

    .wpsafelink-pricing-popular {
        border-color: #1ABC9C;
        box-shadow: 0 4px 12px rgba(26, 188, 156, 0.15);
    }

    .wpsafelink-pricing-featured {
        border-color: #1ABC9C;
        box-shadow: 0 4px 12px rgba(26, 188, 156, 0.15);
    }

    .wpsafelink-pricing-badge {
        position: absolute;
        top: -12px;
        left: 50%;
        transform: translateX(-50%);
        color: white;
        padding: 4px 16px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 600;
        letter-spacing: 0.5px;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .wpsafelink-pricing-popular .wpsafelink-pricing-badge {
        background: #1ABC9C;
    }

    .wpsafelink-pricing-featured .wpsafelink-pricing-badge {
        background: #1ABC9C;
    }

    .wpsafelink-pricing-header {
        text-align: center;
        padding-bottom: 20px;
        border-bottom: 1px solid #f3f4f6;
        margin-bottom: 20px;
    }

    .wpsafelink-pricing-title {
        font-size: 18px;
        font-weight: 600;
        color: #111827;
        margin: 0 0 12px;
        letter-spacing: -0.5px;
    }

    .wpsafelink-pricing-price {
        display: flex;
        align-items: baseline;
        justify-content: center;
        gap: 4px;
    }

    .wpsafelink-price-current {
        font-size: 32px;
        font-weight: 700;
        color: #111827;
        letter-spacing: -1px;
    }

    .wpsafelink-price-period {
        font-size: 14px;
        color: #6b7280;
        font-weight: 400;
    }

    .wpsafelink-pricing-features {
        list-style: none;
        padding: 0;
        margin: 0 0 24px;
    }

    .wpsafelink-pricing-features li {
        padding: 8px 0;
        font-size: 13px;
        color: #4b5563;
        display: flex;
        align-items: center;
        gap: 8px;
        font-weight: 400;
        letter-spacing: 0.1px;
    }

    .wpsafelink-feature-icon {
        flex-shrink: 0;
        width: 16px;
        height: 16px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        font-weight: 700;
        border-radius: 50%;
    }

    .wpsafelink-icon-check {
        background: #dcfce7;
        color: #16a34a;
    }

    .wpsafelink-icon-check::before {
        content: "\2713";
    }

    .wpsafelink-icon-star {
        background: #fef3c7;
        color: #d97706;
    }

    .wpsafelink-icon-star::before {
        content: "\2605";
        font-size: 10px;
    }

    .wpsafelink-icon-x {
        background: #f3f4f6;
        color: #9ca3af;
    }

    .wpsafelink-icon-x::before {
        content: "\2715";
        font-size: 9px;
    }

    .wpsafelink-feature-disabled {
        opacity: 0.5;
    }

    .wpsafelink-feature-highlight {
        font-weight: 600 !important;
        color: #111827 !important;
        padding-bottom: 6px !important;
        margin-bottom: 4px;
        border-bottom: 1px solid #f3f4f6;
    }

    .wpsafelink-pricing-button {
        display: block;
        padding: 10px 20px;
        background: #fff;
        color: #374151;
        border: 2px solid #d1d5db;
        border-radius: 8px;
        text-align: center;
        font-weight: 600;
        font-size: 14px;
        text-decoration: none;
        transition: all 0.2s;
        letter-spacing: 0.3px;
    }

    .wpsafelink-pricing-button:hover {
        background: #f9fafb;
        border-color: #9ca3af;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .wpsafelink-pricing-button-popular {
        background: #1ABC9C;
        color: white;
        border-color: #1ABC9C;
    }

    .wpsafelink-pricing-button-popular:hover {
        background: #16A085;
        border-color: #16A085;
        color: white;
        box-shadow: 0 4px 12px rgba(26, 188, 156, 0.3);
    }

    .wpsafelink-pricing-button-pro {
        background: #1ABC9C;
        color: white;
        border: none;
    }

    .wpsafelink-pricing-button-pro:hover {
        background: #16A085;
        color: white;
        transform: translateY(-1px);
        box-shadow: 0 6px 20px rgba(26, 188, 156, 0.4);
    }

    /* Additional Plans (Agency & Lifetime) */
    .wpsafelink-pricing-extras {
        display: flex;
        gap: 16px;
        margin-bottom: 24px;
    }

    .wpsafelink-pricing-extra-card {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        padding: 20px 24px;
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        transition: all 0.2s;
    }

    .wpsafelink-pricing-extra-card:hover {
        border-color: #d1d5db;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
    }

    .wpsafelink-pricing-extra-lifetime {
        border-color: #fbbf24;
        background: #fffbeb;
    }

    .wpsafelink-pricing-extra-content h4 {
        margin: 0 0 4px;
        font-size: 15px;
        font-weight: 600;
        color: #111827;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .wpsafelink-pricing-extra-price {
        font-size: 13px;
        font-weight: 700;
        color: #1ABC9C;
        background: #eef2ff;
        padding: 2px 8px;
        border-radius: 4px;
    }

    .wpsafelink-pricing-extra-lifetime .wpsafelink-pricing-extra-price {
        color: #92400e;
        background: #fef3c7;
    }

    .wpsafelink-pricing-extra-content p {
        margin: 0;
        font-size: 13px;
        color: #6b7280;
        line-height: 1.4;
    }

    .wpsafelink-pricing-extra-button {
        flex-shrink: 0;
        padding: 8px 20px;
        background: #fff;
        color: #374151;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        text-decoration: none;
        font-size: 13px;
        font-weight: 600;
        transition: all 0.2s;
        white-space: nowrap;
    }

    .wpsafelink-pricing-extra-button:hover {
        background: #f9fafb;
        border-color: #9ca3af;
        color: #111827;
    }

    /* Discount Code */
    .wpsafelink-pricing-discount {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 12px 24px;
        background: #ecfdf5;
        border: 1px solid #6ee7b7;
        border-radius: 8px;
        margin-bottom: 32px;
        font-size: 14px;
        color: #065f46;
        font-weight: 500;
    }

    .wpsafelink-pricing-discount svg {
        color: #059669;
    }

    .wpsafelink-pricing-discount strong {
        font-weight: 700;
        color: #047857;
        background: #a7f3d0;
        padding: 2px 8px;
        border-radius: 4px;
        letter-spacing: 0.5px;
    }

    /* License Form Styles */
    .wpsafelink-license-form {
        padding: 32px;
        background: #f9fafb;
        border-radius: 12px;
        border: 1px solid #e5e7eb;
    }

    .wpsafelink-form-title {
        font-size: 18px;
        font-weight: 600;
        color: #111827;
        margin: 0 0 24px;
        text-align: center;
        letter-spacing: -0.3px;
    }

    .wpsafelink-wizard-label {
        font-weight: 500;
        font-size: 14px;
        color: #374151;
        letter-spacing: 0.2px;
    }

    .wpsafelink-wizard-input {
        border: 1px solid #d1d5db;
        border-radius: 6px;
        font-weight: 400;
        transition: border-color 0.2s, box-shadow 0.2s;
    }

    .wpsafelink-wizard-input:focus {
        border-color: #1ABC9C;
        box-shadow: 0 0 0 3px rgba(26, 188, 156, 0.1);
    }

    /* Responsive Design */
    @media (max-width: 900px) {
        .wpsafelink-pricing-container {
            flex-direction: column;
            align-items: center;
        }

        .wpsafelink-pricing-card {
            max-width: 100%;
            width: 100%;
        }
    }

    @media (max-width: 768px) {
        .wpsafelink-pricing-extras {
            flex-direction: column;
        }

        .wpsafelink-pricing-extra-card {
            flex-direction: column;
            text-align: center;
        }

        .wpsafelink-social-proof {
            flex-direction: column;
            gap: 12px;
        }

        .wpsafelink-social-proof-divider {
            width: 48px;
            height: 1px;
        }
    }
    </style>

    <div class="wpsafelink-wizard-actions">
        <button type="submit" name="save_step" class="wpsafelink-button wpsafelink-button-primary wpsafelink-button-large" id="activate-license">
            <?php esc_html_e('Activate License', 'wp-safelink'); ?>
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="5" y1="12" x2="19" y2="12"></line>
                <polyline points="12 5 19 12 12 19"></polyline>
            </svg>
        </button>
    </div>
</div>
