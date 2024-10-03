<?php
namespace BridgeDirectory;

defined( 'ABSPATH' ) || exit;

class Data_Sync {
    private $api_client;
    private $db_handler;

    public function __construct( $db_handler ) {
        $this->api_client = new API_Client();
        $this->db_handler = $db_handler;

        // Hook into WordPress actions and filters
        add_filter( 'cron_schedules', [ $this, 'add_custom_cron_schedule' ] );
        add_action( 'bridge_directory_incremental_sync', [ $this, 'incremental_sync' ] );
        add_action( 'update_option_bridge_directory_sync_interval', [ $this, 'sync_interval_updated' ], 10, 2 );
    }

    /**
     * Define Custom Cron Schedule Based on Settings
     */
    public function add_custom_cron_schedule( $schedules ) {
        $interval_hours = get_option( 'bridge_directory_sync_interval', 24 );
        $interval_seconds = absint( $interval_hours ) * HOUR_IN_SECONDS;

        $schedules['bridge_directory_sync_interval'] = [
            'interval' => $interval_seconds,
            'display'  => sprintf( __( 'Every %d Hours', 'bridge-directory' ), $interval_hours ),
        ];

        return $schedules;
    }

    /**
     * Activation Hook: Schedule the Incremental Sync Cron Event
     */
    public function activate_plugin() {
        // Ensure the custom cron schedule is added before scheduling
        $this->add_custom_cron_schedule( wp_get_schedules() );

        $this->schedule_incremental_sync();

        // Optionally, perform a full sync upon activation
        $this->full_sync();
    }

    /**
     * Deactivation Hook: Unschedule the Incremental Sync Cron Event
     */
    public function deactivate_plugin() {
        $this->unschedule_incremental_sync();
    }

    /**
     * Schedule the Incremental Sync Cron Event
     */
    public function schedule_incremental_sync() {
        if ( ! wp_next_scheduled( 'bridge_directory_incremental_sync' ) ) {
            wp_schedule_event( time(), 'bridge_directory_sync_interval', 'bridge_directory_incremental_sync' );
            error_log( 'Bridge Directory: Incremental sync scheduled.' );
        } else {
            error_log( 'Bridge Directory: Incremental sync already scheduled.' );
        }
    }

    /**
     * Unschedule the Incremental Sync Cron Event
     */
    public function unschedule_incremental_sync() {
        $timestamp = wp_next_scheduled( 'bridge_directory_incremental_sync' );
        if ( $timestamp ) {
            wp_unschedule_event( $timestamp, 'bridge_directory_incremental_sync' );
            error_log( 'Bridge Directory: Incremental sync unscheduled.' );
        }
    }

    /**
     * Reschedule the Incremental Sync Cron Event When Interval Changes
     */
    public function sync_interval_updated( $old_value, $new_value ) {
        if ( $old_value !== $new_value ) {
            $this->reschedule_incremental_sync();
            error_log( 'Bridge Directory: Sync interval updated from ' . $old_value . ' to ' . $new_value . ' hours.' );
        }
    }

    /**
     * Reschedule the Incremental Sync Cron Event
     */
    public function reschedule_incremental_sync() {
        $this->unschedule_incremental_sync();
        $this->schedule_incremental_sync();
        error_log( 'Bridge Directory: Incremental sync rescheduled with new interval.' );
    }

    /**
     * Perform a Full Synchronization
     */
    public function full_sync() {
        error_log( 'Bridge Directory: Starting full sync.' );
        $offices = $this->api_client->fetch_all_offices();
        if ( is_wp_error( $offices ) ) {
            // Handle error (e.g., log it)
            error_log( 'Bridge Directory Full Sync Error: ' . $offices->get_error_message() );
            return;
        }
        $this->db_handler->save_offices( $offices );
        update_option( 'bridge_directory_last_full_sync', gmdate( 'Y-m-d\TH:i:s\Z' ) );
        error_log( 'Bridge Directory: Full sync completed.' );
    }

    /**
     * Perform an Incremental Synchronization
     */
    public function incremental_sync() {
        error_log( 'Bridge Directory: Starting incremental sync.' );
        $last_sync = get_option( 'bridge_directory_last_sync', '1970-01-01T00:00:00Z' );

        // Ensure the timestamp is in the correct format and in UTC
        $last_sync_formatted = gmdate( 'Y-m-d\TH:i:s\Z', strtotime( $last_sync ) );

        $updated_offices = $this->api_client->fetch_updated_offices( $last_sync_formatted );
        $inactive_offices = $this->api_client->fetch_inactive_offices( $last_sync_formatted );

        if ( ! is_wp_error( $updated_offices ) ) {
            $this->db_handler->update_offices( $updated_offices );
            error_log( 'Bridge Directory: Updated offices synchronized.' );
        } else {
            // Handle error (e.g., log it)
            error_log( 'Bridge Directory Incremental Sync Error (Updated Offices): ' . $updated_offices->get_error_message() );
        }

        if ( ! is_wp_error( $inactive_offices ) ) {
            $this->db_handler->remove_offices( $inactive_offices );
            error_log( 'Bridge Directory: Inactive offices removed.' );
        } else {
            // Handle error (e.g., log it)
            error_log( 'Bridge Directory Incremental Sync Error (Inactive Offices): ' . $inactive_offices->get_error_message() );
        }

        update_option( 'bridge_directory_last_sync', gmdate( 'Y-m-d\TH:i:s\Z' ) );
        error_log( 'Bridge Directory: Incremental sync completed.' );
    }
}
