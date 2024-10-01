<?php
namespace BridgeDirectory;

defined( 'ABSPATH' ) || exit;

class Search_Handler {
    private $api_client;
    private $cache_handler;

    public function __construct( $api_client, $cache_handler ) {
        $this->api_client = $api_client;
        $this->cache_handler = $cache_handler;
    }

    public function get_offices() {
        $offices = $this->cache_handler->get_cached_offices();
        if ( null === $offices ) {
            $offices = $this->api_client->fetch_offices();
            if ( is_wp_error( $offices ) ) {
                return [];
            }
            $this->cache_handler->cache_offices( $offices );
        }
        return $offices;
    }

    public function search_offices( $query ) {
        $offices = $this->get_offices();
        if ( empty( $query ) ) {
            return $offices;
        }

        $filtered = array_filter( $offices, function( $office ) use ( $query ) {
            return stripos( $office['OfficeName'], $query ) !== false ||
                   stripos( $office['OfficePhone'], $query ) !== false ||
                   stripos( $office['OfficeEmail'], $query ) !== false;
        } );

        return $filtered;
    }
}
