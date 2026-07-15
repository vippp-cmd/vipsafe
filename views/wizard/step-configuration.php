<?php
/**
 * Configuration step template
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
$default_options = array_merge($default_options, $settings_class->get_general_options('templates'));

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

// Ensure critical keys exist
$permalink_param = isset($options['permalink_parameter']) ? $options['permalink_parameter'] : 'go';
$permalink_type = isset($options['permalink']) ? $options['permalink'] : '1';

// Get available templates
$default_template = 'installed';
$templates['installed'] = 'Installed WordPress Theme';
$temps = glob(wpsafelink_plugin_path() . '/template/*.php');
$default_template = '';
foreach ($temps as $t) {
    $t = explode('/', $t);
    $t = $t[count($t) - 1];
    $t = str_replace('.php', '', $t);
    $templates[$t] = $t;
}
$selected_template = isset($options['template']) ? $options['template'] : $default_template;
?>

<div class="wpsafelink-wizard-step wpsafelink-wizard-configuration">
    <h2><?php esc_html_e('Configuration Settings', 'wp-safelink'); ?></h2>
    <p class="wpsafelink-wizard-subtitle">
        <?php esc_html_e('Configure your safelink URL structure, templates and protection settings.', 'wp-safelink'); ?>
    </p>

    <div class="wpsafelink-wizard-form">
        <!-- URL Structure Section -->
        <div class="wpsafelink-wizard-section">
            <h3 class="wpsafelink-wizard-section-title">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"></path>
                    <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"></path>
                </svg>
                <?php esc_html_e('URL Structure', 'wp-safelink'); ?>
            </h3>

            <div class="wpsafelink-wizard-field-group">
                <label class="wpsafelink-wizard-label">
                    <?php esc_html_e('Permalink Type', 'wp-safelink'); ?>
                    <span class="wpsafelink-help-icon" data-help="Choose your safelink URL structure:<br><br><strong>Mode 1 (Path):</strong> Clean URLs using path structure - Best for SEO<br><strong>Mode 2 (Query):</strong> Query parameter structure - Compatible with all servers<br><strong>Mode 3 (Raw):</strong> Direct query without parameter name - Shortest URLs<br><br><em>Mode 1 requires pretty permalinks to be enabled in WordPress settings.</em>" data-help-title="Permalink Structure Options"></span>
                </label>
                <div class="wpsafelink-wizard-radio-group">
                    <label class="wpsafelink-wizard-radio">
                        <input type="radio" name="permalink" value="1" <?php checked($permalink_type, '1'); ?> />
                        <span class="wpsafelink-wizard-radio-label">
                            <span class="wpsafelink-wizard-radio-title"><?php esc_html_e('Path Mode', 'wp-safelink'); ?></span>
                            <span class="wpsafelink-wizard-radio-description">
                                <?php echo esc_html(home_url('/' . $permalink_param . '/safelink_code')); ?>
                            </span>
                        </span>
                    </label>
                    <label class="wpsafelink-wizard-radio">
                        <input type="radio" name="permalink" value="2" <?php checked($permalink_type, '2'); ?> />
                        <span class="wpsafelink-wizard-radio-label">
                            <span class="wpsafelink-wizard-radio-title"><?php esc_html_e('Query Mode', 'wp-safelink'); ?></span>
                            <span class="wpsafelink-wizard-radio-description">
                                <?php echo esc_html(home_url('/?' . $permalink_param . '=safelink_code')); ?>
                            </span>
                        </span>
                    </label>
                    <label class="wpsafelink-wizard-radio">
                        <input type="radio" name="permalink" value="3" <?php checked($permalink_type, '3'); ?> />
                        <span class="wpsafelink-wizard-radio-label">
                            <span class="wpsafelink-wizard-radio-title"><?php esc_html_e('Raw Mode', 'wp-safelink'); ?></span>
                            <span class="wpsafelink-wizard-radio-description">
                                <?php echo esc_html(home_url('/?safelink_code')); ?>
                            </span>
                        </span>
                    </label>
                </div>
            </div>

            <div class="wpsafelink-wizard-field-group wrap-permalink_parameter">
                <label for="permalink_parameter" class="wpsafelink-wizard-label">
                    <?php esc_html_e('URL Parameter', 'wp-safelink'); ?>
                    <span class="wpsafelink-help-icon" data-help="Customize the URL parameter used in your safelinks. This defines how your links appear to visitors. For example, using &quot;go&quot; creates URLs like <code>/go/abc123</code> or <code>/?go=abc123</code>. You can use any word like &quot;download&quot;, &quot;link&quot;, &quot;redirect&quot;, etc. Keep it short and SEO-friendly." data-help-title="Customizing Your URL Parameter"></span>
                </label>
                <input type="text"
                       id="permalink_parameter"
                       name="permalink_parameter"
                       class="wpsafelink-wizard-input"
                       value="<?php echo esc_attr($permalink_param); ?>"
                       placeholder="go" />
                <p class="wpsafelink-wizard-field-description">
                    <?php esc_html_e('The parameter used in your safelink URLs (e.g., "go", "link", "download")', 'wp-safelink'); ?>
                </p>
            </div>
        </div>

        <!-- Template Settings Section -->
        <div class="wpsafelink-wizard-section">
            <h3 class="wpsafelink-wizard-section-title">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                    <line x1="3" y1="9" x2="21" y2="9"></line>
                    <line x1="9" y1="21" x2="9" y2="9"></line>
                </svg>
                <?php esc_html_e('Template Settings', 'wp-safelink'); ?>
            </h3>

            <div class="wpsafelink-wizard-field-group">
                <label for="template" class="wpsafelink-wizard-label">
                    <?php esc_html_e('Template', 'wp-safelink'); ?>
                    <span class="wpsafelink-help-icon" data-help="Select the visual template for your safelink pages. Each template offers different layouts and features:<br><br><strong>Template1:</strong> Clean, minimal design with centered content<br><strong>Template2:</strong> Integrated with your theme header/footer<br><strong>Template3:</strong> Full-page design with sidebar support<br><strong>Template4:</strong> Modern card-based layout<br><br><em>Note: Template2 and Template3 require theme integration.</em>" data-help-title="Choosing a Template"></span>
                </label>
                <select id="template" name="template" class="wpsafelink-wizard-select">
                    <?php foreach ($templates as $template_key => $template_name): ?>
                        <option value="<?php echo esc_attr($template_key); ?>" <?php selected($selected_template, $template_key); ?>>
                            <?php echo esc_html(ucfirst($template_name)); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <p class="wpsafelink-wizard-field-description">
                    <?php esc_html_e('Choose how your safelink pages will look', 'wp-safelink'); ?>
                </p>
            </div>
        </div>

        <!-- Auto Integration Section -->
        <div class="wpsafelink-wizard-section auto-integration-section">
            <h3 class="wpsafelink-wizard-section-title">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 2v6m0 4v6m0 4v2M8 6h8m-8 6h8m-8 6h8"></path>
                </svg>
                <?php esc_html_e('Auto Integration', 'wp-safelink'); ?>
            </h3>

            <div class="wpsafelink-wizard-field-group">
                <label class="wpsafelink-wizard-toggle">
                    <input type="hidden" name="auto_integration_enable" value="no" />
                    <input type="checkbox"
                           name="auto_integration_enable"
                           value="yes"
                           class="wpsafelink-wizard-toggle-input"
                           checked="checked"
                           <?php checked($options['auto_integration_enable'] ?? 'yes', 'yes'); ?> />
                    <span class="wpsafelink-wizard-toggle-slider"></span>
                    <span class="wpsafelink-wizard-toggle-label">
                        <span class="wpsafelink-wizard-toggle-title"><?php esc_html_e('Enable Auto Integration', 'wp-safelink'); ?></span>
                        <span class="wpsafelink-wizard-toggle-description">
                            <?php esc_html_e('Automatically inject safelink functions into your theme', 'wp-safelink'); ?>
                        </span>
                    </span>
                </label>
            </div>

            <div class="wpsafelink-wizard-field-group auto-integration-settings">
                <label for="auto_integration_top_placement" class="wpsafelink-wizard-label">
                    <?php esc_html_e('Top Section Placement', 'wp-safelink'); ?>
                </label>
                <select id="auto_integration_top_placement" name="auto_integration_top_placement" class="wpsafelink-wizard-select">
                    <option value="wp_body_open" <?php selected($options['auto_integration_top_placement'] ?? 'after_title', 'wp_body_open'); ?>>
                        <?php esc_html_e('After Body Tag (wp_body_open)', 'wp-safelink'); ?>
                    </option>
                    <option value="before_title" <?php selected($options['auto_integration_top_placement'] ?? 'after_title', 'before_title'); ?>>
                        <?php esc_html_e('Before Post Title', 'wp-safelink'); ?>
                    </option>
                    <option value="after_title" <?php selected($options['auto_integration_top_placement'] ?? 'after_title', 'after_title'); ?>>
                        <?php esc_html_e('After Post Title', 'wp-safelink'); ?>
                    </option>
                    <option value="content_start" <?php selected($options['auto_integration_top_placement'] ?? 'after_title', 'content_start'); ?>>
                        <?php esc_html_e('Start of Content', 'wp-safelink'); ?>
                    </option>
                </select>
            </div>

            <div class="wpsafelink-wizard-field-group auto-integration-settings">
                <label for="auto_integration_bottom_placement" class="wpsafelink-wizard-label">
                    <?php esc_html_e('Bottom Section Placement', 'wp-safelink'); ?>
                </label>
                <select id="auto_integration_bottom_placement" name="auto_integration_bottom_placement" class="wpsafelink-wizard-select">
                    <option value="wp_footer" <?php selected($options['auto_integration_bottom_placement'] ?? 'content_end', 'wp_footer'); ?>>
                        <?php esc_html_e('Footer (wp_footer)', 'wp-safelink'); ?>
                    </option>
                    <option value="content_end" <?php selected($options['auto_integration_bottom_placement'] ?? 'content_end', 'content_end'); ?>>
                        <?php esc_html_e('End of Content', 'wp-safelink'); ?>
                    </option>
                    <option value="after_content" <?php selected($options['auto_integration_bottom_placement'] ?? 'content_end', 'after_content'); ?>>
                        <?php esc_html_e('After Content', 'wp-safelink'); ?>
                    </option>
                </select>
            </div>
        </div>
    </div>

    <div class="wpsafelink-wizard-actions">
        <button type="submit" name="save_step" class="wpsafelink-button wpsafelink-button-primary wpsafelink-button-large">
            <?php esc_html_e('Continue', 'wp-safelink'); ?>
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="5" y1="12" x2="19" y2="12"></line>
                <polyline points="12 5 19 12 12 19"></polyline>
            </svg>
        </button>
    </div>
</div>