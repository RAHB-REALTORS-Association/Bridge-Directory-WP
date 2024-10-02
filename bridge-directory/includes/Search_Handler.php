<?php
namespace BridgeDirectory;

defined( 'ABSPATH' ) || exit;

class Search_Handler {
    private $db_handler;

    public function __construct( $db_handler ) {
        $this->db_handler = $db_handler;
    }

    public function search_offices( $query = '', $page = 1, $limit = 20 ) {
        global $wpdb;

        $offset = ( $page - 1 ) * $limit;
        $table_name = $this->db_handler->get_table_name;

        $sql = "SELECT * FROM {$table_name}";
        $where_clauses = [];

        if ( ! empty( $query ) ) {
            $like_query = '%' . $wpdb->esc_like( $query ) . '%';
            $where_clauses[] = $wpdb->prepare(
                "(OfficeName LIKE %s OR OfficePhone LIKE %s OR OfficeEmail LIKE %s)",
                $like_query,
                $like_query,
                $like_query
            );
        }

        if ( ! empty( $where_clauses ) ) {
            $sql .= ' WHERE ' . implode( ' AND ', $where_clauses );
        }

        $sql .= $wpdb->prepare( ' LIMIT %d OFFSET %d', $limit, $offset );

        $results = $wpdb->get_results( $sql, ARRAY_A );
        return $results;
    }
}
