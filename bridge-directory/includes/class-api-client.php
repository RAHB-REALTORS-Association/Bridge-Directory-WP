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

    public function fetch_all_offices() {
        $all_offices = [];
        $offset = 0;
        $limit = 1000;
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
        $limit = 1000;
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
                'OfficeStatus'          => 'Active',
                'ModificationTimestamp' => $since,
                'fields'                => $fields,
                'limit'                 => $limit,
                'offset'                => $offset,
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
        $limit = 1000;

        do {
            $response = $this->fetch_offices( [
                'OfficeStatus'          => 'Inactive',
                'ModificationTimestamp' => $since,
                'fields'                => 'OfficeKey',
                'limit'                 => $limit,
                'offset'                => $offset,
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

        $args['access_token'] = $this->access_token;

        $url = sprintf(
            'https://api.bridgedataoutput.com/api/v2/%s/offices',
            $this->dataset_name
        );

        $response = wp_remote_get( add_query_arg( $args, $url ) );

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
