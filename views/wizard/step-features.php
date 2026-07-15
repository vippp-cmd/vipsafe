<?php
/**
 * Test Safelink step template
 *
 * @package WP_Safelink
 */

if (!defined('ABSPATH')) {
    exit;
}

$options = wpsafelink_options();
?>

<div class="wpsafelink-wizard-step wpsafelink-wizard-features">
    <h2><?php esc_html_e('Test Your Safelinks', 'wp-safelink'); ?></h2>
    <p class="wpsafelink-wizard-subtitle">
        <?php esc_html_e('Generate and test safelinks to ensure your configuration is working correctly.', 'wp-safelink'); ?>
    </p>

    <div class="wpsafelink-wizard-form">
        <!-- Link Generation Section -->
        <div class="wpsafelink-wizard-section">
            <h3 class="wpsafelink-wizard-section-title">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"></path>
                    <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"></path>
                </svg>
                <?php esc_html_e('Generate Test Link', 'wp-safelink'); ?>
            </h3>

            <div class="wpsafelink-wizard-field-group">
                <label for="wpsafelink_test_url" class="wpsafelink-wizard-label">
                    <?php esc_html_e('Enter URL to Convert', 'wp-safelink'); ?>
                </label>
                <div class="wpsafelink-wizard-test-input-group">
                    <input type="url"
                           id="wpsafelink_test_url"
                           name="test_url"
                           class="wpsafelink-wizard-input"
                           placeholder="https://example.com/download.zip"
                           required>
                    <button type="button" id="generate_test_link" class="wpsafelink-button wpsafelink-button-primary">
                        <?php esc_html_e('Generate', 'wp-safelink'); ?>
                    </button>
                </div>
                <p class="wpsafelink-wizard-field-description">
                    <?php esc_html_e('Quick test URLs:', 'wp-safelink'); ?>
                    <span class="wpsafelink-wizard-quick-links">
                        <a href="#" class="wpsafelink-wizard-quick-link" data-url="https://wordpress.org/latest.zip">WordPress.zip</a>
                        <a href="#" class="wpsafelink-wizard-quick-link" data-url="https://www.w3.org/WAI/ER/tests/xhtml/testfiles/resources/pdf/dummy.pdf">Sample.pdf</a>
                        <a href="#" class="wpsafelink-wizard-quick-link" data-url="https://demo-movie.themeson.com/movies/tt26743210/">Movie Page</a>
                    </span>
                </p>
            </div>

            <!-- Generated Link Display -->
            <div id="generated_link_display" class="wpsafelink-wizard-result-box" style="display: none;">
                <div class="wpsafelink-wizard-result-header">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                        <polyline points="22 4 12 14.01 9 11.01"></polyline>
                    </svg>
                    <strong><?php esc_html_e('Safelink Generated Successfully!', 'wp-safelink'); ?></strong>
                </div>
                <div class="wpsafelink-wizard-result-content">
                    <div class="wpsafelink-wizard-field-group">
                        <label class="wpsafelink-wizard-label">
                            <?php esc_html_e('Safelink URL', 'wp-safelink'); ?>
                        </label>
                        <div class="wpsafelink-wizard-result-input-group">
                            <input type="text" id="generated_safelink" class="wpsafelink-wizard-input" readonly>
                            <button type="button" class="wpsafelink-button-icon copy-link-btn" data-target="generated_safelink">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
                                    <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
                                </svg>
                                <span class="copy-text"><?php esc_html_e('Copy', 'wp-safelink'); ?></span>
                            </button>
                            <a href="#" id="test_safelink" target="_blank" class="wpsafelink-button-icon">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path>
                                    <polyline points="15 3 21 3 21 9"></polyline>
                                    <line x1="10" y1="14" x2="21" y2="3"></line>
                                </svg>
                                <?php esc_html_e('Test', 'wp-safelink'); ?>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Testing Instructions -->
        <div class="wpsafelink-wizard-info-box">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"></circle>
                <path d="M12 6v6l4 2"></path>
            </svg>
            <div>
                <strong><?php esc_html_e('Testing Your Configuration', 'wp-safelink'); ?></strong>
                <p><?php esc_html_e('Follow these steps to verify your safelink setup:', 'wp-safelink'); ?></p>
                <ul>
                    <li><?php esc_html_e('Generate a test safelink using any URL', 'wp-safelink'); ?></li>
                    <li><?php esc_html_e('Click "Test" to preview your safelink page', 'wp-safelink'); ?></li>
                    <li><?php esc_html_e('Verify the countdown timer and redirect work correctly', 'wp-safelink'); ?></li>
                    <li><?php esc_html_e('Check the statistics to confirm tracking is working', 'wp-safelink'); ?></li>
                </ul>
            </div>
        </div>
    </div>

    <div class="wpsafelink-wizard-actions">
        <a href="<?php echo admin_url('admin.php?page=wpsafelink'); ?>" class="wpsafelink-button wpsafelink-button-primary wpsafelink-button-large">
            <?php esc_html_e('Complete Setup', 'wp-safelink'); ?>
        </a>
    </div>
</div>


<style>
/* Ultra-thin Design System for Features Step */
.wpsafelink-wizard-features .wpsafelink-wizard-section {
    background: #fafbfc;
    border: 1px solid #e2e8f0;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    transition: all 0.3s ease;
}

.wpsafelink-wizard-features .wpsafelink-wizard-section:hover {
    background: #ffffff;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
}

/* Test Input Group */
.wpsafelink-wizard-test-input-group {
    display: flex;
    gap: 8px;
    align-items: stretch;
}

.wpsafelink-wizard-test-input-group .wpsafelink-wizard-input {
    flex: 1;
}

.wpsafelink-wizard-test-input-group .wpsafelink-button {
    white-space: nowrap;
}

/* Quick Links - Ultra-thin Style */
.wpsafelink-wizard-quick-links {
    display: inline-flex;
    gap: 12px;
    margin-left: 8px;
}

.wpsafelink-wizard-quick-link {
    color: #1ABC9C;
    text-decoration: none;
    font-size: 13px;
    font-weight: 500;
    padding: 4px 12px;
    border-radius: 6px;
    background: #f7fafc;
    transition: all 0.2s ease;
    border: 1px solid #e2e8f0;
}

.wpsafelink-wizard-quick-link:hover {
    background: #f1f5f9;
    border-color: #cbd5e0;
    text-decoration: none;
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.wpsafelink-wizard-quick-link:active {
    transform: scale(0.98);
}

/* Result Box - Ultra-thin */
.wpsafelink-wizard-result-box {
    margin-top: 24px;
    padding: 20px;
    background: #f0fdf4;
    border: 1px solid #86efac;
    border-radius: 12px;
    animation: slideDown 0.3s ease;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.wpsafelink-wizard-result-header {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 20px;
    padding-bottom: 16px;
    border-bottom: 1px solid #86efac;
}

.wpsafelink-wizard-result-header svg {
    color: #10b981;
}

.wpsafelink-wizard-result-header strong {
    font-size: 16px;
    color: #059669;
    font-weight: 600;
}

.wpsafelink-wizard-result-content {
    margin: 0;
}

.wpsafelink-wizard-result-input-group {
    display: flex;
    gap: 8px;
    align-items: stretch;
}

.wpsafelink-wizard-result-input-group .wpsafelink-wizard-input {
    flex: 1;
    font-family: 'SF Mono', Monaco, Consolas, monospace;
    font-size: 13px;
    background: white;
    border: 1px solid #d1d5db;
}

.wpsafelink-wizard-result-input-group .wpsafelink-button-icon {
    white-space: nowrap;
}

/* Button Icon Style Override */
.wpsafelink-wizard-result-input-group .wpsafelink-button-icon {
    padding: 12px 20px;
}

/* Copy Button States */
.copy-link-btn.copied {
    background: #10b981;
    border-color: #10b981;
    color: white;
    animation: successPulse 0.4s ease;
}

@keyframes successPulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.05); }
}

.copy-link-btn.copied svg {
    color: white;
}

/* Loading States */
.wpsafelink-wizard-section.generating {
    position: relative;
    overflow: hidden;
}

.wpsafelink-wizard-section.generating::after {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 2px;
    background: #1ABC9C;
    animation: loading 1.5s infinite;
}

@keyframes loading {
    to { left: 100%; }
}

/* Button Loading */
.wpsafelink-button.loading {
    pointer-events: none;
    opacity: 0.8;
    position: relative;
}

.wpsafelink-button.loading::after {
    content: '';
    position: absolute;
    width: 16px;
    height: 16px;
    margin: auto;
    border: 2px solid transparent;
    border-top-color: white;
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

/* Input States */
#generated_safelink.highlight {
    background: #fef3c7 !important;
    animation: highlight-fade 1s ease-out;
}

@keyframes highlight-fade {
    from { background: #fef3c7; }
    to { background: white; }
}

/* Error Shake */
.shake {
    animation: shake 0.5s;
    border-color: #ef4444 !important;
}

@keyframes shake {
    0%, 100% { transform: translateX(0); }
    10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
    20%, 40%, 60%, 80% { transform: translateX(5px); }
}

/* JavaScript Enhanced Classes */
.wpsafelink-wizard-quick-link.active {
    background: #1ABC9C;
    color: white;
    border-color: #1ABC9C;
}

/* Responsive Design */
@media (max-width: 768px) {
    .wpsafelink-wizard-test-input-group,
    .wpsafelink-wizard-result-input-group {
        flex-direction: column;
    }

    .wpsafelink-wizard-quick-links {
        display: flex;
        flex-direction: column;
        gap: 8px;
        margin-left: 0;
        margin-top: 8px;
    }

    .wpsafelink-wizard-quick-link {
        display: block;
        text-align: center;
        padding: 8px 12px;
    }

    .wpsafelink-wizard-result-input-group .wpsafelink-button-icon {
        width: 100%;
        justify-content: center;
    }
}

@media (max-width: 480px) {
    .wpsafelink-wizard-result-box {
        padding: 16px;
    }

    .wpsafelink-wizard-result-header {
        flex-direction: column;
        text-align: center;
    }
}
</style>