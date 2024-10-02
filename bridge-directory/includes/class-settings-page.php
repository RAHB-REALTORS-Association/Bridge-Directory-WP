<?php
namespace BridgeDirectory;

defined( 'ABSPATH' ) || exit;

class Settings_Page {
    public function register() {
        add_action( 'admin_menu', [ $this, 'register_settings_page' ] );
        add_action( 'admin_init', [ $this, 'register_settings' ] );
    }

    public function register_settings_page() {
        add_options_page(
            'Bridge Directory Settings',
            'Bridge Directory',
            'manage_options',
            'bridge-directory',
            [ $this, 'settings_page_html' ]
        );
    }

    public function settings_page_html() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        // Process sync and clear cache actions
        if ( isset( $_POST['bridge_directory_full_sync'] ) ) {
            check_admin_referer( 'bridge_directory_full_sync' );
            $data_sync = new Data_Sync();
            $data_sync->full_sync();
            echo '<div class="updated"><p>Full sync initiated.</p></div>';
        }

        if ( isset( $_POST['bridge_directory_clear_cache'] ) ) {
            check_admin_referer( 'bridge_directory_clear_cache' );
            $cache_handler = new Cache_Handler();
            $cache_handler->clear_cache();
            echo '<div class="updated"><p>Cache cleared.</p></div>';
        }

        $cache_handler = new DB_Handler();
        $total_records = $cache_handler->get_total_records();

        ?>
        <div class="wrap">
            <h1>Bridge Directory Settings</h1>
            <form method="post" action="options.php">
                <?php
                settings_fields( 'bridge_directory_settings' );
                do_settings_sections( 'bridge_directory_settings' );
                submit_button();
                ?>
            </form>

            <h2>Data Management</h2>
            <p>Total Cached Records: <?php echo esc_html( $total_records ); ?></p>
            <form method="post">
                <?php wp_nonce_field( 'bridge_directory_full_sync' ); ?>
                <input type="hidden" name="bridge_directory_full_sync" value="1">
                <?php submit_button( 'Full Sync', 'primary', 'submit', false ); ?>
            </form>
            <form method="post" style="margin-top: 10px;">
                <?php wp_nonce_field( 'bridge_directory_clear_cache' ); ?>
                <input type="hidden" name="bridge_directory_clear_cache" value="1">
                <?php submit_button( 'Clear Cache', 'secondary', 'submit', false ); ?>
            </form>
        </div>
        <?php
    }

    public function register_settings() {
        register_setting( 'bridge_directory_settings', 'bridge_directory_access_token', [
            'sanitize_callback' => [ $this, 'validate_input' ],
        ] );

        register_setting( 'bridge_directory_settings', 'bridge_directory_dataset_name', [
            'sanitize_callback' => [ $this, 'validate_input' ],
        ] );

        register_setting( 'bridge_directory_settings', 'bridge_directory_cache_lifetime', [
            'sanitize_callback' => 'absint',
            'default'           => 24,
        ] );

        register_setting( 'bridge_directory_settings', 'bridge_directory_sync_interval', [
            'sanitize_callback' => 'absint',
            'default'           => 24,
        ] );

        add_settings_section(
            'bridge_directory_main',
            'API Settings',
            null,
            'bridge_directory_settings'
        );

        add_settings_field(
            'bridge_directory_access_token',
            'Access Token',
            [ $this, 'access_token_field_html' ],
            'bridge_directory_settings',
            'bridge_directory_main'
        );

        add_settings_field(
            'bridge_directory_dataset_name',
            'Dataset Name',
            [ $this, 'dataset_name_field_html' ],
            'bridge_directory_settings',
            'bridge_directory_main'
        );

        add_settings_field(
            'bridge_directory_sync_interval',
            'Sync Interval (hours)',
            [ $this, 'sync_interval_field_html' ],
            'bridge_directory_settings',
            'bridge_directory_main'
        );
    }

    public function access_token_field_html() {
        $value = get_option( 'bridge_directory_access_token', '' );
        echo '<input type="text" name="bridge_directory_access_token" value="' . esc_attr( $value ) . '" />';
    }

    public function dataset_name_field_html() {
        $value = get_option( 'bridge_directory_dataset_name', '' );
        echo '<input type="text" name="bridge_directory_dataset_name" value="' . esc_attr( $value ) . '" />';
    }

    public function sync_interval_field_html() {
        $value = get_option( 'bridge_directory_sync_interval', 24 );
        echo '<input type="number" name="bridge_directory_sync_interval" value="' . esc_attr( $value ) . '" min="1" />';
    }

    public function validate_input( $input ) {
        return sanitize_text_field( $input );
    }
}
