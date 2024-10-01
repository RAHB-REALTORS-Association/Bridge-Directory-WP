<?php
namespace BridgeDirectory;

defined( 'ABSPATH' ) || exit;

class Cache_Handler {
    private $transient_key = 'bridge_directory_offices';

    public function get_cached_offices() {
        $offices = get_transient( $this->transient_key );
        if ( false === $offices ) {
            return null;
        }
        return $offices;
    }

    public function cache_offices( $data ) {
        $cache_lifetime = get_option( 'bridge_directory_cache_lifetime', 24 ) * HOUR_IN_SECONDS;
        set_transient( $this->transient_key, $data, $cache_lifetime );
    }
}
