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
        $table_name = $this->db_handler->get_table_name();

        $sql = "SELECT * FROM {$table_name}";
        $where_clauses = [];
        $parameters = [];

        if ( ! empty( $query ) ) {
            $like_query = '%' . $wpdb->esc_like( $query ) . '%';

            // Normalize the search query for phone number comparison
            $normalized_query = preg_replace( '/\D/', '', $query );
            $normalized_like_query = '%' . $wpdb->esc_like( $normalized_query ) . '%';

            // Always search by OfficeName and OfficeEmail
            $where_clauses[] = "(OfficeName LIKE %s OR OfficeEmail LIKE %s)";
            $parameters[] = $like_query;
            $parameters[] = $like_query;

            // Search by normalized phone number if the normalized query has at least 7 digits
            if ( strlen( $normalized_query ) >= 7 ) {
                $where_clauses[] = "OfficePhoneNormalized LIKE %s";
                $parameters[] = $normalized_like_query;
            }

            // Remove the digit check to allow address searches without digits
            $where_clauses[] = "(OfficeAddress1 LIKE %s OR OfficeAddress2 LIKE %s OR OfficeCity LIKE %s OR OfficePostalCode LIKE %s)";
            $parameters[] = $like_query;
            $parameters[] = $like_query;
            $parameters[] = $like_query;
            $parameters[] = $like_query;
        }

        if ( ! empty( $where_clauses ) ) {
            $sql .= ' WHERE ' . implode( ' OR ', $where_clauses );
        }

        $sql .= ' ORDER BY OfficeName ASC';
        $sql .= ' LIMIT %d OFFSET %d';
        $parameters[] = (int) $limit;
        $parameters[] = (int) $offset;

        // Prepare the entire SQL statement with all parameters at once
        $prepared_sql = $wpdb->prepare( $sql, $parameters );

        // Optional: Log the final SQL for debugging
        // error_log( $prepared_sql );

        $results = $wpdb->get_results( $prepared_sql, ARRAY_A );
        return $results;
    }
}
