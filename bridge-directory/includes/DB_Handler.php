<?php
namespace BridgeDirectory;

defined( 'ABSPATH' ) || exit;

class DB_Handler {
    private $table_name;

    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'bridge_directory_offices';
    }

    public function get_table_name() {
        return $this->table_name;
    }

    public static function activate() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'bridge_directory_offices';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            OfficeKey varchar(255) NOT NULL,
            OfficeName varchar(255) NOT NULL,
            OfficeAddress1 varchar(255),
            OfficeAddress2 varchar(255),
            OfficeCity varchar(100),
            OfficeStateOrProvince varchar(100),
            OfficePostalCode varchar(50),
            OfficePhone varchar(50),
            OfficeFax varchar(50),
            OfficeEmail varchar(100),
            SocialMediaWebsiteUrlOrId varchar(255),
            PRIMARY KEY  (OfficeKey)
        ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $sql );
    }

    public static function deactivate() {
        // Optionally, delete the table or leave it for future use
        // Uncomment the following lines to drop the table upon deactivation

        global $wpdb;
        $table_name = $wpdb->prefix . 'bridge_directory_offices';
        $wpdb->query( "DROP TABLE IF EXISTS $table_name" );
    }

    public function save_offices( $offices ) {
        global $wpdb;
        foreach ( $offices as $office ) {
            $wpdb->replace(
                $this->table_name,
                [
                    'OfficeKey'                 => $office['OfficeKey'],
                    'OfficeName'                => $office['OfficeName'],
                    'OfficeAddress1'            => $office['OfficeAddress1'],
                    'OfficeAddress2'            => $office['OfficeAddress2'],
                    'OfficeCity'                => $office['OfficeCity'],
                    'OfficeStateOrProvince'     => $office['OfficeStateOrProvince'],
                    'OfficePostalCode'          => $office['OfficePostalCode'],
                    'OfficePhone'               => $office['OfficePhone'],
                    'OfficeFax'                 => $office['OfficeFax'],
                    'OfficeEmail'               => $office['OfficeEmail'],
                    'SocialMediaWebsiteUrlOrId' => $office['SocialMediaWebsiteUrlOrId'],
                ],
                [ '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s' ]
            );
        }
    }

    public function update_offices( $offices ) {
        $this->save_offices( $offices );
    }

    public function remove_offices( $office_keys ) {
        global $wpdb;
        $placeholders = implode( ',', array_fill( 0, count( $office_keys ), '%s' ) );
        $wpdb->query( $wpdb->prepare(
            "DELETE FROM {$this->table_name} WHERE OfficeKey IN ($placeholders)",
            $office_keys
        ) );
    }

    public function get_offices() {
        global $wpdb;
        $results = $wpdb->get_results( "SELECT * FROM {$this->table_name}", ARRAY_A );
        return $results;
    }

    public function get_total_records() {
        global $wpdb;
        $count = $wpdb->get_var( "SELECT COUNT(*) FROM {$this->table_name}" );
        return $count;
    }

    public function clear_data() {
        global $wpdb;
        $wpdb->query( "TRUNCATE TABLE {$this->table_name}" );
    }
}
