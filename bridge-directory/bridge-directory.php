<?php
/**
 * Plugin Name: Bridge API Directory
 * Plugin URI: https://github.com/RAHB-REALTORS-Association/Bridge-API-WP
 * Description: Displays a searchable directory of offices using the Bridge Data Output API.
 * Version: 0.0.1
 * Author: RAHB
 * Author URI: https://lab.rahb.ca
 * License: GPL-2.0
 * Text Domain: bridge-directory
 */

defined( 'ABSPATH' ) || exit;

require_once plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';

use BridgeDirectory\API_Client;
use BridgeDirectory\Cache_Handler;
use BridgeDirectory\Search_Handler;
use BridgeDirectory\Block_Register;
use BridgeDirectory\Settings_Page;

// Initialize Settings Page
$settings_page = new Settings_Page();
$settings_page->register();

// Initialize API Client
$api_client = new API_Client();

// Initialize Cache Handler
$cache_handler = new Cache_Handler();

// Initialize Search Handler
$search_handler = new Search_Handler( $api_client, $cache_handler );

// Initialize Block Register
$block_register = new Block_Register( $search_handler );
$block_register->register();

