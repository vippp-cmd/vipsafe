<?php
/**
 * WP Safelink Auto Integration
 *
 * Automatically integrates safelink functions into themes without manual code changes.
 * Only available for Template 2 and Template 3, which traditionally require manual
 * theme integration (adding functions to header.php and footer.php).
 *
 * @author WP Safelink
 * @package WP Safelink
 * @since 5.2.4
 */

if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

if (!class_exists('WPSafelink_Auto_Integration')) :

    class WPSafelink_Auto_Integration {

        /**
         * Instance
         */
        private static $instance = null;

        /**
         * Settings
         */
        private $settings;

        /**
         * Track if top section has been rendered
         */
        private static $top_rendered = false;

        /**
         * Track if bottom section has been rendered
         */
        private static $bottom_rendered = false;

        /**
         * Constructor
         */
        public function __construct() {
            $this->settings = wpsafelink_options();
            $this->init();
        }

        /**
         * Get instance
         */
        public static function get_instance() {
            if (self::$instance === null) {
                self::$instance = new self();
            }
            return self::$instance;
        }

        /**
         * Initialize auto integration
         */
        private function init() {
            $current_template = isset($this->settings['template']) ? $this->settings['template'] : '';
            if (!in_array($current_template, ['installed', 'template2', 'template3'])) {
                return;
            }

            // Only initialize if auto integration is enabled
            if (!isset($this->settings['auto_integration_enable']) || $this->settings['auto_integration_enable'] !== 'yes') {
                return;
            }

            // Get priority setting
            $priority = isset($this->settings['auto_integration_priority']) ?
                        intval($this->settings['auto_integration_priority']) : 10;

            // Initialize top section placement
            $this->init_top_placement($priority);

            // Initialize bottom section placement
            $this->init_bottom_placement($priority);
        }

        /**
         * Initialize top section placement
         */
        private function init_top_placement($priority) {
            if (!function_exists('newwpsafelink_top')) {
                return;
            }

            $placement = isset($this->settings['auto_integration_top_placement']) ?
                        $this->settings['auto_integration_top_placement'] : 'after_title';

            switch ($placement) {
                case 'wp_body_open':
                    add_action('wp_body_open', array($this, 'render_top_section'), $priority);
                    break;

                case 'before_title':
                    add_filter('the_title', array($this, 'add_before_title'), $priority, 2);
                    break;

                case 'after_title':
                    add_filter('the_title', array($this, 'add_after_title'), $priority, 2);
                    break;

                case 'content_start':
                    add_filter('the_content', array($this, 'add_content_start'), $priority);
                    break;
            }
        }

        /**
         * Initialize bottom section placement
         */
        private function init_bottom_placement($priority) {
            if (!function_exists('newwpsafelink_bottom')) {
                return;
            }

            $placement = isset($this->settings['auto_integration_bottom_placement']) ?
                        $this->settings['auto_integration_bottom_placement'] : 'content_end';

            switch ($placement) {
                case 'wp_footer':
                    add_action('wp_footer', array($this, 'render_bottom_section'), $priority);
                    break;

                case 'content_end':
                    add_filter('the_content', array($this, 'add_content_end'), $priority + 1);
                    break;

                case 'after_content':
                    // Hook into loop_end for after content placement
                    add_action('loop_end', array($this, 'render_bottom_section'), $priority);
                    break;
            }
        }

        /**
         * Check if we should show safelink on current page
         */
        private function should_show_safelink() {
            // Check if safelink data exists
            $code = newpsafelink_data();
            return !empty($code);
        }

        /**
         * Render top section
         */
        public function render_top_section() {
            if (!self::$top_rendered && $this->should_show_safelink()) {
                newwpsafelink_top();
                self::$top_rendered = true;
            }
        }

        /**
         * Render top section fallback for older WordPress versions
         */
        public function render_top_section_fallback() {
            if (!self::$top_rendered && $this->should_show_safelink()) {
                echo '<script>
                    document.addEventListener("DOMContentLoaded", function() {
                        var safelink = document.querySelector(".wpsafe-top");
                        if (safelink && document.body.firstChild) {
                            document.body.insertBefore(safelink, document.body.firstChild);
                        }
                    });
                </script>';
                newwpsafelink_top();
                self::$top_rendered = true;
            }
        }

        /**
         * Render bottom section
         */
        public function render_bottom_section() {
            if (!self::$bottom_rendered && $this->should_show_safelink()) {
                newwpsafelink_bottom();
                self::$bottom_rendered = true;
            }
        }

        /**
         * Add content before title
         */
        public function add_before_title($title, $id = null) {
            // Skip if already rendered
            if (self::$top_rendered) {
                return $title;
            }

            // Only modify title in the main loop
            if (!in_the_loop() || !is_main_query()) {
                return $title;
            }

            // Only on single posts/pages
            if (!is_singular() || get_the_ID() !== $id) {
                return $title;
            }

            if ($this->should_show_safelink()) {
                ob_start();
                newwpsafelink_top();
                $safelink_content = ob_get_clean();
                self::$top_rendered = true;
                return $safelink_content . $title;
            }

            return $title;
        }

        /**
         * Add content after title
         */
        public function add_after_title($title, $id = null) {
            // Skip if already rendered
            if (self::$top_rendered) {
                return $title;
            }

            // Only modify title in the main loop
            if (!in_the_loop() || !is_main_query()) {
                return $title;
            }

            // Only on single posts/pages
            if (!is_singular() || get_the_ID() !== $id) {
                return $title;
            }

            if ($this->should_show_safelink()) {
                ob_start();
                newwpsafelink_top();
                $safelink_content = ob_get_clean();
                self::$top_rendered = true;
                return $title . $safelink_content;
            }

            return $title;
        }

        /**
         * Add content at start of content
         */
        public function add_content_start($content) {
            // Skip if already rendered
            if (self::$top_rendered) {
                return $content;
            }

            // Only modify content in the main loop
            if (!in_the_loop() || !is_main_query()) {
                return $content;
            }

            if ($this->should_show_safelink()) {
                ob_start();
                newwpsafelink_top();
                $safelink_content = ob_get_clean();
                self::$top_rendered = true;
                return $safelink_content . $content;
            }

            return $content;
        }

        /**
         * Add content at end of content
         */
        public function add_content_end($content) {
            // Skip if already rendered
            if (self::$bottom_rendered) {
                return $content;
            }

            // Only modify content in the main loop
            if (!in_the_loop() || !is_main_query()) {
                return $content;
            }

            if ($this->should_show_safelink()) {
                ob_start();
                newwpsafelink_bottom();
                $safelink_content = ob_get_clean();
                self::$bottom_rendered = true;
                return $content . $safelink_content;
            }

            return $content;
        }

        /**
         * Handle AJAX request to toggle auto integration
         */
        public static function ajax_toggle_integration() {
            check_ajax_referer('wpsafelink_ajax', 'nonce');

            $enable = isset($_POST['enable']) && $_POST['enable'] === 'true';
            $settings = wpsafelink_options();
            $settings['auto_integration_enable'] = $enable ? 'yes' : 'no';

            update_option('wpsafelink_settings', $settings);

            wp_send_json_success(array(
                'enabled' => $enable,
                'message' => $enable ?
                    'Auto integration enabled successfully' :
                    'Auto integration disabled'
            ));
        }
    }

endif;

// Initialize auto integration
add_action('init', function() {
    WPSafelink_Auto_Integration::get_instance();
}, 5);