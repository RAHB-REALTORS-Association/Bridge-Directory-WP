<?php
namespace BridgeDirectory;

defined( 'ABSPATH' ) || exit;

class API_Client {
    private $access_token;
    private $dataset_name;
    private $advanced_query;

    public function __construct() {
        $this->access_token   = get_option( 'bridge_directory_access_token' );
        $this->dataset_name   = get_option( 'bridge_directory_dataset_name' );
        $this->advanced_query = get_option( 'bridge_directory_advanced_query', '' );
    }

    public function fetch_all_offices() {
        $all_offices = [];
        $offset = 0;
        $limit = 200;
        $fields = implode( ',', [
            'OfficeKey',
            'OfficeName',
            'OfficeAddress1',
            'OfficeAddress2',
            'OfficeCity',
            'OfficeStateOrProvince',
            'OfficePostalCode',
            'OfficePhone',
            'OfficeFax',
            'OfficeEmail',
            'SocialMediaWebsiteUrlOrId',
        ] );

        do {
            $response = $this->fetch_offices( [
                'OfficeStatus' => 'Active',
                'fields'       => $fields,
                'limit'        => $limit,
                'offset'       => $offset,
            ] );

            if ( is_wp_error( $response ) ) {
                return $response;
            }

            $offices = $response;
            foreach ( $offices as $office ) {
                $all_offices[ $office['OfficeKey'] ] = $office;
            }
            $offset += $limit;
        } while ( count( $offices ) === $limit );

        return $all_offices;
    }

    public function fetch_updated_offices( $since ) {
        $updated_offices = [];
        $offset = 0;
        $limit = 200;
        $fields = implode( ',', [
            'OfficeKey',
            'OfficeName',
            'OfficeAddress1',
            'OfficeAddress2',
            'OfficeCity',
            'OfficeStateOrProvince',
            'OfficePostalCode',
            'OfficePhone',
            'OfficeFax',
            'OfficeEmail',
            'SocialMediaWebsiteUrlOrId',
        ] );

        do {
            $response = $this->fetch_offices( [
                'OfficeStatus'             => 'Active',
                'ModificationTimestamp.gt' => $since,
                'fields'                   => $fields,
                'limit'                    => $limit,
                'offset'                   => $offset,
            ] );

            if ( is_wp_error( $response ) ) {
                return $response;
            }

            $offices = $response;
            foreach ( $offices as $office ) {
                $updated_offices[ $office['OfficeKey'] ] = $office;
            }
            $offset += $limit;
        } while ( count( $offices ) === $limit );

        return $updated_offices;
    }

    public function fetch_inactive_offices( $since ) {
        $inactive_offices = [];
        $offset = 0;
        $limit = 200;

        do {
            $response = $this->fetch_offices( [
                'OfficeStatus'             => 'Inactive',
                'ModificationTimestamp.gt' => $since,
                'fields'                   => 'OfficeKey',
                'limit'                    => $limit,
                'offset'                   => $offset,
            ] );

            if ( is_wp_error( $response ) ) {
                return $response;
            }

            $offices = $response;
            foreach ( $offices as $office ) {
                $inactive_offices[] = $office['OfficeKey'];
            }
            $offset += $limit;
        } while ( count( $offices ) === $limit );

        return $inactive_offices;
    }

    private function fetch_offices( $args = [] ) {
        if ( empty( $this->access_token ) || empty( $this->dataset_name ) ) {
            return new \WP_Error( 'missing_credentials', 'API credentials are missing.' );
        }

        // Build base query string from $args (token sent via header, not URL)
        $base_query_string = http_build_query( $args, '', '&', PHP_QUERY_RFC3986 );

        // Process advanced query string
        $advanced_query_string = $this->advanced_query;
        if ( ! empty( $advanced_query_string ) ) {
            // Remove OfficeStatus and ModificationTimestamp.gt from advanced query string
            $params_to_remove = ['OfficeStatus', 'ModificationTimestamp.gt'];

            // Split advanced query string into parameters
            $pairs = explode('&', $advanced_query_string);
            $filtered_pairs = [];
            foreach ( $pairs as $pair ) {
                $pair = trim($pair);
                if ( $pair === '' ) continue;
                $kv = explode('=', $pair, 2);
                $key = urldecode($kv[0]);
                if ( in_array( $key, $params_to_remove ) ) continue;
                $value = isset($kv[1]) ? str_replace(' ', '+', urldecode($kv[1])) : '';
                $filtered_pairs[] = urlencode($key) . '=' . urlencode($value);
            }
            // Rebuild advanced query string
            $advanced_query_string = implode('&', $filtered_pairs);
        }

        // Combine base query string and advanced query string
        if ( ! empty( $advanced_query_string ) ) {
            $query_string = $base_query_string . '&' . $advanced_query_string;
        } else {
            $query_string = $base_query_string;
        }

        $url = sprintf(
            'https://api.bridgedataoutput.com/api/v2/%s/offices',
            rawurlencode( $this->dataset_name )
        );

        $full_url = $url . '?' . $query_string;

        $response = wp_remote_get( $full_url, [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->access_token,
            ],
        ] );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        $status_code = wp_remote_retrieve_response_code( $response );
        if ( $status_code < 200 || $status_code >= 300 ) {
            return new \WP_Error( 'api_http_error', sprintf( 'API returned HTTP %d.', $status_code ) );
        }

        $body = wp_remote_retrieve_body( $response );
        $data = json_decode( $body, true );

        if ( null === $data ) {
            return new \WP_Error( 'api_json_error', 'API returned invalid JSON.' );
        }

        if ( isset( $data['success'] ) && $data['success'] ) {
            return $data['bundle'];
        }

        return new \WP_Error( 'api_error', 'Failed to fetch data from the API.' );
    }
}
