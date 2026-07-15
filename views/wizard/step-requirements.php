<?php
/**
 * Wizard Requirements Step Template
 *
 * Displays when ionCube Loader 14.0.0+ is not installed.
 * This template mimics the wizard UI to provide a consistent experience.
 *
 * @package WP_Safelink
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title><?php esc_html_e('WP Safelink Setup', 'wp-safelink'); ?></title>
    <link rel="stylesheet" href="<?php echo esc_url(wpsafelink_plugin_url() . '/assets/css/wizard.css'); ?>">
    <style>
        /* Requirements Step Specific Styles */
        .wpsafelink-requirements-alert {
            display: flex;
            align-items: flex-start;
            gap: 16px;
            padding: 20px 24px;
            background: #fef2f2;
            border: 1px solid #fca5a5;
            border-radius: 12px;
            margin-bottom: 32px;
        }
        .wpsafelink-requirements-alert-icon {
            flex-shrink: 0;
            width: 48px;
            height: 48px;
            background: #fee2e2;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .wpsafelink-requirements-alert-icon svg {
            color: #dc2626;
        }
        .wpsafelink-requirements-alert-content h3 {
            margin: 0 0 8px 0;
            font-size: 18px;
            font-weight: 600;
            color: #991b1b;
        }
        .wpsafelink-requirements-alert-content p {
            margin: 0;
            color: #b91c1c;
            font-size: 14px;
            line-height: 1.5;
        }
        .wpsafelink-requirements-table {
            width: 100%;
            border-collapse: collapse;
            margin: 24px 0;
            background: #fff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .wpsafelink-requirements-table th,
        .wpsafelink-requirements-table td {
            padding: 16px 20px;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
        }
        .wpsafelink-requirements-table th {
            background: #f9fafb;
            font-weight: 600;
            color: #374151;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .wpsafelink-requirements-table td {
            color: #4b5563;
            font-size: 15px;
        }
        .wpsafelink-requirements-table tr:last-child td {
            border-bottom: none;
        }
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
        }
        .status-badge.error {
            background: #fee2e2;
            color: #dc2626;
        }
        .status-badge.success {
            background: #dcfce7;
            color: #16a34a;
        }
        .wpsafelink-requirements-info {
            background: #eff6ff;
            border: 1px solid #93c5fd;
            border-radius: 12px;
            padding: 20px 24px;
            margin: 24px 0;
        }
        .wpsafelink-requirements-info h4 {
            margin: 0 0 12px 0;
            font-size: 16px;
            font-weight: 600;
            color: #1e40af;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .wpsafelink-requirements-info p {
            margin: 0;
            color: #1e3a8a;
            font-size: 14px;
            line-height: 1.6;
        }
        .wpsafelink-requirements-info ul {
            margin: 12px 0 0 0;
            padding-left: 20px;
            color: #1e3a8a;
            font-size: 14px;
            line-height: 1.8;
        }
        .wpsafelink-wizard-actions {
            display: flex;
            justify-content: center;
            gap: 16px;
            margin-top: 32px;
            padding-top: 24px;
            border-top: 1px solid #e5e7eb;
        }
        .wpsafelink-button {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 14px 28px;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s ease;
            cursor: pointer;
            border: none;
        }
        .wpsafelink-button-primary {
            background: #1ABC9C;
            color: #fff;
            box-shadow: 0 4px 12px rgba(26, 188, 156, 0.3);
        }
        .wpsafelink-button-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(26, 188, 156, 0.4);
            color: #fff;
        }
        .wpsafelink-button-secondary {
            background: #f3f4f6;
            color: #374151;
            border: 1px solid #d1d5db;
        }
        .wpsafelink-button-secondary:hover {
            background: #e5e7eb;
            color: #1f2937;
        }
    </style>
</head>
<body class="wpsafelink-setup">
    <div class="wpsafelink-setup-wizard">
        <!-- Header -->
        <div class="wpsafelink-setup-wizard-header">
            <h1 class="wpsafelink-setup-wizard-logo">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none">
                    <path d="M12 2L2 7v10c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V7l-10-5z" fill="#1ABC9C"/>
                    <path d="M9 12l2 2 4-4" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <span>WP Safelink</span>
            </h1>
        </div>

        <!-- Steps (showing Requirements as blocked step) -->
        <ul class="wpsafelink-setup-wizard-steps">
            <li class="active">
                <span class="step-number">!</span>
                <span class="step-name"><?php esc_html_e('Requirements', 'wp-safelink'); ?></span>
            </li>
            <li>
                <span class="step-number">1</span>
                <span class="step-name"><?php esc_html_e('Welcome', 'wp-safelink'); ?></span>
            </li>
            <li>
                <span class="step-number">2</span>
                <span class="step-name"><?php esc_html_e('License', 'wp-safelink'); ?></span>
            </li>
            <li>
                <span class="step-number">3</span>
                <span class="step-name"><?php esc_html_e('Configuration', 'wp-safelink'); ?></span>
            </li>
            <li>
                <span class="step-number">4</span>
                <span class="step-name"><?php esc_html_e('Test', 'wp-safelink'); ?></span>
            </li>
        </ul>

        <!-- Content -->
        <div class="wpsafelink-setup-wizard-content">
            <div class="wpsafelink-wizard-step">
                <!-- Alert -->
                <div class="wpsafelink-requirements-alert">
                    <div class="wpsafelink-requirements-alert-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" y1="8" x2="12" y2="12"></line>
                            <line x1="12" y1="16" x2="12.01" y2="16"></line>
                        </svg>
                    </div>
                    <div class="wpsafelink-requirements-alert-content">
                        <h3><?php esc_html_e('System Requirements Not Met', 'wp-safelink'); ?></h3>
                        <p><?php esc_html_e('WP Safelink requires ionCube Loader to be installed on your server. The setup wizard cannot continue until this requirement is met.', 'wp-safelink'); ?></p>
                    </div>
                </div>

                <!-- Requirements Table -->
                <table class="wpsafelink-requirements-table">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('Requirement', 'wp-safelink'); ?></th>
                            <th><?php esc_html_e('Required', 'wp-safelink'); ?></th>
                            <th><?php esc_html_e('Current', 'wp-safelink'); ?></th>
                            <th><?php esc_html_e('Status', 'wp-safelink'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong><?php esc_html_e('ionCube Loader', 'wp-safelink'); ?></strong></td>
                            <td>14.0.0+</td>
                            <td>
                                <?php
                                if ($ioncube_installed) {
                                    echo esc_html($ioncube_version);
                                } else {
                                    esc_html_e('Not Installed', 'wp-safelink');
                                }
                                ?>
                            </td>
                            <td>
                                <span class="status-badge error">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <line x1="18" y1="6" x2="6" y2="18"></line>
                                        <line x1="6" y1="6" x2="18" y2="18"></line>
                                    </svg>
                                    <?php esc_html_e('Required', 'wp-safelink'); ?>
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <!-- Info Box -->
                <div class="wpsafelink-requirements-info">
                    <h4>
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" y1="16" x2="12" y2="12"></line>
                            <line x1="12" y1="8" x2="12.01" y2="8"></line>
                        </svg>
                        <?php esc_html_e('How to Install ionCube Loader', 'wp-safelink'); ?>
                    </h4>
                    <p><?php esc_html_e('ionCube Loader is a PHP extension that needs to be installed on your server. Here\'s how to get it set up:', 'wp-safelink'); ?></p>
                    <ul>
                        <li><?php esc_html_e('Contact your hosting provider and request them to install ionCube Loader version 14.0.0 or higher.', 'wp-safelink'); ?></li>
                        <li><?php esc_html_e('Most hosting providers (cPanel, Plesk, etc.) can enable this within a few hours.', 'wp-safelink'); ?></li>
                        <li><?php esc_html_e('If you manage your own server, visit ioncube.com/loaders.php for installation instructions.', 'wp-safelink'); ?></li>
                    </ul>
                </div>

                <!-- Actions -->
                <div class="wpsafelink-wizard-actions">
                    <a href="<?php echo esc_url(admin_url('admin.php?page=wpsafelink-setup')); ?>" class="wpsafelink-button wpsafelink-button-primary">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="23 4 23 10 17 10"></polyline>
                            <path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"></path>
                        </svg>
                        <?php esc_html_e('Check Again', 'wp-safelink'); ?>
                    </a>
                    <a href="<?php echo esc_url(admin_url()); ?>" class="wpsafelink-button wpsafelink-button-secondary">
                        <?php esc_html_e('Back to Dashboard', 'wp-safelink'); ?>
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
