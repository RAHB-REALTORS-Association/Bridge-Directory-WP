<?php
namespace BridgeDirectory;

defined( 'ABSPATH' ) || exit;

class Cache_Handler {
    private $option_key = 'bridge_directory_offices';

    public function get_offices() {
        $offices = get_option( $this->option_key, [] );
        return $offices;
    }

    public function save_offices( $data ) {
        update_option( $this->option_key, $data, false );
    }

    public function update_offices( $updated_offices ) {
        $offices = $this->get_offices();
        foreach ( $updated_offices as $key => $office ) {
            $offices[ $key ] = $office;
        }
        $this->save_offices( $offices );
    }

    public function remove_offices( $inactive_office_keys ) {
        $offices = $this->get_offices();
        foreach ( $inactive_office_keys as $key ) {
            unset( $offices[ $key ] );
        }
        $this->save_offices( $offices );
    }

    public function clear_cache() {
        delete_option( $this->option_key );
    }

    public function get_total_records() {
        $offices = $this->get_offices();
        return count( $offices );
    }
}
