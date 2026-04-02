<?php
namespace BridgeDirectory;

defined( 'ABSPATH' ) || exit;

class AJAX_Handler {
    private $search_handler;

    public function __construct( $search_handler ) {
        $this->search_handler = $search_handler;

        add_action( 'wp_ajax_bridge_directory_load_offices', [ $this, 'load_offices' ] );
        add_action( 'wp_ajax_nopriv_bridge_directory_load_offices', [ $this, 'load_offices' ] );
    }

    public function load_offices() {
        check_ajax_referer( 'bridge_directory_nonce', 'nonce' );

        if ( $this->is_rate_limited() ) {
            wp_send_json_error( [ 'message' => 'Too many requests. Please try again shortly.' ], 429 );
        }

        $page = isset( $_POST['page'] ) ? absint( $_POST['page'] ) : 1;
        $query = isset( $_POST['query'] ) ? sanitize_text_field( wp_unslash( $_POST['query'] ) ) : '';
        $limit = 20; // Number of results per page

        $offices = $this->search_handler->search_offices( $query, $page, $limit );

        // Escape all string values before sending to the client
        $offices = array_map( [ $this, 'escape_office_data' ], $offices );

        wp_send_json_success( [ 'offices' => $offices ] );
    }

    private function is_rate_limited() {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $key = 'bd_rate_' . md5( $ip );
        $max_requests = 30;
        $window = 60; // seconds

        $current = get_transient( $key );
        if ( false === $current ) {
            set_transient( $key, 1, $window );
            return false;
        }

        if ( (int) $current >= $max_requests ) {
            return true;
        }

        set_transient( $key, (int) $current + 1, $window );
        return false;
    }

    private function escape_office_data( $office ) {
        $string_fields = [
            'OfficeKey', 'OfficeName', 'OfficeAddress1', 'OfficeAddress2',
            'OfficeCity', 'OfficeStateOrProvince', 'OfficePostalCode',
            'OfficePhone', 'OfficePhoneNormalized', 'OfficeFax',
            'OfficeEmail', 'SocialMediaWebsiteUrlOrId',
        ];
        foreach ( $string_fields as $field ) {
            if ( isset( $office[ $field ] ) ) {
                $office[ $field ] = esc_html( $office[ $field ] );
            }
        }
        return $office;
    }
}
