<?php
namespace BridgeDirectory;

defined( 'ABSPATH' ) || exit;

class Data_Sync {
    private $api_client;
    private $db_handler;

    public function __construct( $db_handler ) {
        $this->api_client = new API_Client();
        $this->db_handler = $db_handler;
    }

    public function full_sync() {
        $offices = $this->api_client->fetch_all_offices();
        if ( is_wp_error( $offices ) ) {
            // Handle error (e.g., log it)
            error_log( 'Bridge Directory Full Sync Error: ' . $offices->get_error_message() );
            return;
        }
        $this->db_handler->save_offices( $offices );
        update_option( 'bridge_directory_last_full_sync', gmdate( 'Y-m-d\TH:i:s\Z' ) );
    }

    public function incremental_sync() {
        $last_sync = get_option( 'bridge_directory_last_sync', '1970-01-01T00:00:00Z' );

        // Ensure the timestamp is in the correct format and in UTC
        $last_sync_formatted = gmdate( 'Y-m-d\TH:i:s\Z', strtotime( $last_sync ) );

        $updated_offices = $this->api_client->fetch_updated_offices( $last_sync_formatted );
        $inactive_offices = $this->api_client->fetch_inactive_offices( $last_sync_formatted );

        if ( ! is_wp_error( $updated_offices ) ) {
            $this->db_handler->update_offices( $updated_offices );
        } else {
            // Handle error (e.g., log it)
            error_log( 'Bridge Directory Incremental Sync Error (Updated Offices): ' . $updated_offices->get_error_message() );
        }

        if ( ! is_wp_error( $inactive_offices ) ) {
            $this->db_handler->remove_offices( $inactive_offices );
        } else {
            // Handle error (e.g., log it)
            error_log( 'Bridge Directory Incremental Sync Error (Inactive Offices): ' . $inactive_offices->get_error_message() );
        }

        update_option( 'bridge_directory_last_sync', gmdate( 'Y-m-d\TH:i:s\Z' ) );
    }

    public function schedule_incremental_sync() {
        if ( ! wp_next_scheduled( 'bridge_directory_incremental_sync' ) ) {
            wp_schedule_event( time(), 'bridge_directory_sync_interval', 'bridge_directory_incremental_sync' );
        }
    }

    public function unschedule_incremental_sync() {
        wp_clear_scheduled_hook( 'bridge_directory_incremental_sync' );
    }
}
