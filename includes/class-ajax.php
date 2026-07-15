<?php
/**
 * WP Safelink Ajax
 *
 * @author ThemesOn
 * @package WP Safelink (Server Version)
 * @since 1.0.0
 */
if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

if (!class_exists('WPSafelink_Ajax_Revamp')):

    class WPSafelink_Ajax_Revamp
    {

        /* @var string */
        private static $nonce_admin = 'wpsafelink_revamp_nonce';

        /**
         * Constructor
         *
         * @return void
         * @since 1.0.0
         */
        public function __construct()
        {
            // ajax_event => nopriv
            $ajax_event_loop = array(
                'generate_link' => false,
                'generate_link_ajax' => false,
                'process_link' => false
            );
            foreach ($ajax_event_loop as $ajax_event => $nopriv) {
                add_action('wp_ajax_wpsafelink_revamp_' . $ajax_event, array(__CLASS__, $ajax_event));
                if ($nopriv) {
                    add_action('wp_ajax_nopriv_wpsafelink_revamp_' . $ajax_event, array(__CLASS__, $ajax_event));
                }
            }
        }

        /**
         * Generate link
         *
         * @access public
         * @since 1.0.0
         **/
        public static function generate_link()
        {
            global $wpdb, $wpsafelink_core;
            $wpsaf = wpsafelink_options();

            $output = [];
            $output['data'] = [];

            /*
             * Paging
             */
            $sLimit = "";
	        if ( ! empty( $_GET['start'] ) && $_GET['length'] != '-1' ) {
                $sLimit = "LIMIT " . $_GET['start'] . ", " .
                    $_GET['length'];
            }

            /*
             * Ordering
             */
	        if ( ! empty( $_GET['order'] ) ) {
		        $aColumns = [ "date", "safe_id", "link", "view", "click" ];
                $sOrder = "ORDER BY  ";
                for ($i = 0; $i < intval($_GET['order']); $i++) {

                    $sOrder .= $aColumns[$_GET['order'][$i]['column']] . "
				 	" . $_GET['order'][$i]['dir'] . ", ";

                }

                $sOrder = substr_replace($sOrder, "", -2);
                if ($sOrder == "ORDER BY  ") {
                    $sOrder = "ORDER BY date DESC";
                }
            }
            /*
             * Search
             */
	        if ( isset( $_GET['search'] ) && ! empty( $_GET['search']['value'] ) ) {
                $qSearch = "WHERE link LIKE '%{$_GET['search']['value']}%' ";
            }

            $sql = "SELECT * FROM {$wpdb->prefix}wpsafelink $qSearch $sOrder $sLimit";

	        $safe_lists = $wpdb->get_results($sql, 'ARRAY_A');
            foreach ($safe_lists as $d) {
                $encrypted = $wpsafelink_core->encrypt_link($d['link'], $d['safe_id']);
                if ($wpsaf['permalink'] == 1) {
                    $safelink_link = home_url() . '/' . $wpsaf['permalink_parameter'] . '/' . $d['safe_id'];
                    $encrypt_link = home_url() . '/' . $wpsaf['permalink_parameter'] . '/' . $encrypted;
                } else if ($wpsaf['permalink'] == 2) {
                    $safelink_link = home_url() . '/?' . $wpsaf['permalink_parameter'] . '=' . $d['safe_id'];
                    $encrypt_link = home_url() . '/?' . $wpsaf['permalink_parameter'] . '=' . $encrypted;
                } else {
                    $safelink_link = home_url() . '/?' . $d['safe_id'];
                    $encrypt_link = home_url() . '/?' . $encrypted;
                }

                $temp = [];
                $temp[] = date('Y-m-d H:i', strtotime($d['date']));
                $temp[] = ($d['safe_id'] != "" ? "<a class='elips' href='" . $safelink_link . "' target='_blank'>" . $safelink_link . "</a>" : "");
                $temp[] = ($d['link'] != "" ? "<a class='elips' href='" . $d['link'] . "' target='_blank'>" . $d['link'] . "</a>" : "");
                $temp[] = $d['view'];
                $temp[] = $d['click'];
                $temp[] = '<a href="?page=wpsafelink&delete=' . $d['ID'] . '">delete</a>';

                $output['data'][] = $temp;
            }

            $count_query = "select count(*) from {$wpdb->prefix}wpsafelink";
            $num = $wpdb->get_var($count_query);

            $output['recordsTotal'] = $num;
            $output['recordsFiltered'] = $num;
            wp_send_json($output);

            die();
        }

        /**
         * Generate link via AJAX (for wizard testing)
         *
         * @access public
         * @since 1.0.0
         **/
        public static function generate_link_ajax()
        {
            // Verify nonce
            if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'wpsafelink_generate_link')) {
                wp_send_json_error(array('message' => 'Security check failed'));
            }

            // Check if user has permission
            if (!current_user_can('manage_options')) {
                wp_send_json_error(array('message' => 'Insufficient permissions'));
            }

            // Get the URL from POST
            $url = isset($_POST['url']) ? sanitize_url($_POST['url']) : '';

            if (empty($url)) {
                wp_send_json_error(array('message' => 'Please provide a valid URL'));
            }

            // Validate URL
            if (!filter_var($url, FILTER_VALIDATE_URL)) {
                wp_send_json_error(array('message' => 'Invalid URL format'));
            }

            global $wpsafelink_core;

            try {
                // Generate the safelink
                $result = $wpsafelink_core->postGenerateLink($url);

                if ($result && isset($result['generated3'])) {
                    wp_send_json_success(array(
                        'safelink' => $result['generated3'],
                        'safe_id' => $result['safe_id'] ?? '',
                        'encrypted' => $result['encrypted'] ?? '',
                        'original_url' => $url
                    ));
                } else {
                    wp_send_json_error(array('message' => 'Failed to generate safelink'));
                }
            } catch (Exception $e) {
                wp_send_json_error(array('message' => 'Error: ' . $e->getMessage()));
            }
        }
    }

    new WPSafelink_Ajax_Revamp();
endif;
