<?php
namespace BridgeDirectory;

defined( 'ABSPATH' ) || exit;

class Search_Handler {
    private $db_handler;

    public function __construct( $db_handler ) {
        $this->db_handler = $db_handler;
    }

    public function search_offices( $query ) {
        global $wpdb;

        $table_name = $this->db_handler->table_name;

        $sql = "SELECT * FROM {$table_name}";

        if ( ! empty( $query ) ) {
            $like_query = '%' . $wpdb->esc_like( $query ) . '%';
            $sql .= $wpdb->prepare(
                " WHERE OfficeName LIKE %s OR OfficePhone LIKE %s OR OfficeEmail LIKE %s",
                $like_query,
                $like_query,
                $like_query
            );
        }

        $results = $wpdb->get_results( $sql, ARRAY_A );
        return $results;
    }
}
