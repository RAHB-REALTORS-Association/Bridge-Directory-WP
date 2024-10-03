<?php
/**
 * Plugin Name: Bridge API Directory
 * Plugin URI: https://github.com/RAHB-REALTORS-Association/Bridge-Directory-WP
 * Description: Displays a searchable directory of offices using the Bridge Interactive API.
 * Version: 0.2.2
 * Author: Cornerstone Association of REALTORS
 * Author URI: https://www.cornerstone.inc
 * License: GPL-2.0
 * Text Domain: bridge-directory
 */
 
defined( 'ABSPATH' ) || exit;

require_once plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';

use BridgeDirectory\AJAX_Handler;
use BridgeDirectory\API_Client;
use BridgeDirectory\DB_Handler;
use BridgeDirectory\Search_Handler;
use BridgeDirectory\Block_Register;
use BridgeDirectory\Settings_Page;
use BridgeDirectory\Data_Sync;

// Initialize Settings Page
$settings_page = new Settings_Page();
$settings_page->register();

// Initialize DB Handler
$db_handler = new DB_Handler();

// Initialize Search Handler
$search_handler = new Search_Handler( $db_handler );

// Initialize Block Register
$block_register = new Block_Register( $search_handler );
$block_register->register();

// Initialize Data Sync
$data_sync = new Data_Sync( $db_handler );
$data_sync->schedule_incremental_sync();

// Initialize AJAX Handler
$ajax_handler = new AJAX_Handler( $search_handler );

// Activation and Deactivation Hooks
register_activation_hook( __FILE__, [ 'BridgeDirectory\DB_Handler', 'activate' ] );
register_deactivation_hook( __FILE__, [ 'BridgeDirectory\DB_Handler', 'deactivate' ] );

// Cron Hook for Incremental Sync
add_action( 'bridge_directory_incremental_sync', [ $data_sync, 'incremental_sync' ] );

// Add custom cron schedule
add_filter( 'cron_schedules', 'bridge_directory_custom_cron_schedule' );
function bridge_directory_custom_cron_schedule( $schedules ) {
    $interval = get_option( 'bridge_directory_sync_interval', 24 ) * HOUR_IN_SECONDS;
    $schedules['bridge_directory_sync_interval'] = [
        'interval' => $interval,
        'display'  => 'Bridge Directory Sync Interval',
    ];
    return $schedules;
}
