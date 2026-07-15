<?php
/*
Plugin Name: WP Safelink
Plugin URI: https://themeson.com
Description: Converter Your Download Link to Adsense. <strong>Requires ionCube Loader 14.0.0 and PHP v8.3</strong>
Version: 5.2.8
Author: ThemesON
Author URI:  https://themeson.com
*/

if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

// Check ionCube requirement (graceful handling - don't block activation)
$wpsafelink_ioncube_version = phpversion('ionCube Loader');
$wpsafelink_ioncube_installed = extension_loaded('ionCube Loader');
$wpsafelink_ioncube_ok = $wpsafelink_ioncube_installed && version_compare($wpsafelink_ioncube_version, '14.0.0', '>=');
define('WPSAFELINK_IONCUBE_OK', $wpsafelink_ioncube_ok);

/**
 * Retreive plugin url
 *
 * @access  public
 * @return  string  Plugin URL with plugin slug
 * @since   1.0.0
 */
function wpsafelink_plugin_url()
{
    return untrailingslashit(plugins_url('/', __FILE__));
}

/**
 * Retreive plugin path directory
 *
 * @access  public
 * @return  string  Plugin directory
 * @since   1.0.0
 */
function wpsafelink_plugin_path()
{
    return __DIR__;
}

/**
 * Retreive plugin file
 *
 * @access  public
 * @return  string  Plugin file
 * @since   1.0.0
 */
function wpsafelink_plugin_file()
{
    return __FILE__;
}

add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'wpsafelink_settings_links');
function wpsafelink_settings_links($links)
{
    $plugin_links = array(
        '<a href="' . admin_url('admin.php?page=wpsafelink') . '">' . __('Settings', 'wp-safelink') . '</a>',
    );

    return array_merge($plugin_links, $links);
}

register_activation_hook(__FILE__, 'wpsafelink_revamp_activation');
function wpsafelink_revamp_activation()
{
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    $table_name = $wpdb->prefix . 'wpsafelink';
    $sql = "CREATE TABLE $table_name (
		ID bigint(0) NOT NULL AUTO_INCREMENT, 
		date datetime DEFAULT '0000-00-00 00:00:00' NOT NULL, 
		date_view datetime DEFAULT '0000-00-00 00:00:00' NOT NULL, 
		date_click datetime DEFAULT '0000-00-00 00:00:00' NOT NULL, 
		safe_id varchar(8) NOT NULL,
		link longtext NOT NULL,
		view bigint(0) NOT NULL,
		click bigint(0) NOT NULL,
		UNIQUE KEY id (ID)
	) $charset_collate;";
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

function wpsafelink_options()
{
    return get_option('wpsafelink_settings', array());
}

// Always load vendor libraries (they don't require ionCube)
require_once wpsafelink_plugin_path() . '/vendor/simple_html_dom.php';
require_once wpsafelink_plugin_path() . '/vendor/plugin-update-checker/plugin-update-checker.php';
require_once wpsafelink_plugin_path() . '/vendor/autoload.php';

// Only load class-core.php if ionCube is OK (it contains encoded functionality)
if (WPSAFELINK_IONCUBE_OK) {
    require_once wpsafelink_plugin_path() . '/includes/class-core.php';
} else {
    // Load fallback stub for $wpsafelink_core when ionCube unavailable
    require_once wpsafelink_plugin_path() . '/includes/class-core-stub.php';
}

// Always load these classes (plain PHP, needed for admin UI)
require_once wpsafelink_plugin_path() . '/includes/class-ajax.php';
require_once wpsafelink_plugin_path() . '/includes/class-settings.php';
require_once wpsafelink_plugin_path() . '/includes/class-functions.php';
require_once wpsafelink_plugin_path() . '/includes/class-auto-integration.php';
require_once wpsafelink_plugin_path() . '/includes/class-setup-wizard.php';