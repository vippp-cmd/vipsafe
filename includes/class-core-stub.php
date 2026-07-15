<?php
/**
 * Fallback stub for WPSafelink_Core when ionCube is not available
 *
 * This stub provides the same interface as the real WPSafelink_Core class
 * but returns safe defaults. This allows the settings UI to load without
 * fatal errors when ionCube Loader is not installed.
 *
 * @package WP_Safelink
 * @since 5.2.5
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Stub class for WPSafelink_Core
 */
class WPSafelink_Core {

    /**
     * License validation stub
     *
     * @param string $key License key or 'key' to get current key
     * @param bool $cached Whether to use cached value
     * @param bool $force Force revalidation
     * @return mixed
     */
    public function license($key = '', $cached = false, $force = false) {
        // When requesting the license key, return empty
        if ($key === 'key') {
            return '';
        }

        // Return a failure response indicating ionCube is required
        return [
            'success' => false,
            'data' => (object)[
                'msg' => __('ionCube Loader 14.0.0+ is required for this feature.', 'wp-safelink')
            ]
        ];
    }

    /**
     * Check if PRO version stub
     *
     * @return bool Always false when ionCube unavailable
     */
    public function is_pro() {
        return false;
    }

    /**
     * Generate safelink stub
     *
     * @param string $url Target URL
     * @return array Empty link data
     */
    public function postGenerateLink($url) {
        return [
            'generated3' => '',
            'generated2' => '',
            'encrypt_link' => ''
        ];
    }

    /**
     * Encrypt link stub
     *
     * @param string $data Data to encrypt
     * @param string $key Encryption key
     * @return string Empty string (cannot encrypt without ionCube)
     */
    public function encrypt_link($data, $key = '') {
        return '';
    }

    /**
     * Decrypt link stub
     *
     * @param string $data Data to decrypt
     * @param bool $return_array Whether to return array
     * @return mixed Empty result (cannot decrypt without ionCube)
     */
    public function decrypt_link($data, $return_array = false) {
        return $return_array ? [] : '';
    }

    /**
     * Get license option stub
     *
     * @return null
     */
    public function get_license_option() {
        return null;
    }
}

// Instantiate the global stub
$wpsafelink_core = new WPSafelink_Core();
