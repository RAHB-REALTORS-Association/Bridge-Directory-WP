<?php
namespace BridgeDirectory;

defined( 'ABSPATH' ) || exit;

class API_Client {
    private $access_token;
    private $dataset_name;

    public function __construct() {
        $this->access_token = get_option( 'bridge_directory_access_token' );
        $this->dataset_name = get_option( 'bridge_directory_dataset_name' );
    }

    public function fetch_offices() {
        if ( empty( $this->access_token ) || empty( $this->dataset_name ) ) {
            return new \WP_Error( 'missing_credentials', 'API credentials are missing.' );
        }

        $url = sprintf(
            'https://api.bridgedataoutput.com/api/v2/%s/offices',
            $this->dataset_name
        );

        $response = wp_remote_get( add_query_arg(
            [
                'access_token' => $this->access_token,
                'OfficeStatus' => 'Active',
            ],
            $url
        ) );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        $body = wp_remote_retrieve_body( $response );
        $data = json_decode( $body, true );

        if ( isset( $data['success'] ) && $data['success'] ) {
            return $data['bundle'];
        }

        return new \WP_Error( 'api_error', 'Failed to fetch data from the API.' );
    }
}
