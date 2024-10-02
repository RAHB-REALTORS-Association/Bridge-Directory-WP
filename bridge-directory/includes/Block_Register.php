<?php
namespace BridgeDirectory;

defined( 'ABSPATH' ) || exit;

class Block_Register {
    private $search_handler;

    public function __construct( $search_handler ) {
        $this->search_handler = $search_handler;
    }

    public function register() {
        add_action( 'init', [ $this, 'register_blocks' ] );
        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
    }

    public function register_blocks() {
        wp_register_script(
            'bridge-directory-block',
            plugins_url( 'build/blocks.js', __DIR__ ),
            [ 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components' ],
            '1.0.0',
            true
        );

        register_block_type( 'bridge-directory/office-list', [
            'editor_script'   => 'bridge-directory-block',
            'attributes'      => [
                'columns'  => [
                    'type'    => 'number',
                    'default' => 3,
                ],
            ],
            'render_callback' => [ $this, 'render_block' ],
        ] );
    }

    public function enqueue_scripts() {
        if ( has_block( 'bridge-directory/office-list' ) ) {
            wp_enqueue_script(
                'bridge-directory-frontend',
                plugins_url( 'assets/js/bridge-directory.js', __DIR__ ),
                [ 'jquery' ],
                '1.0.0',
                true
            );

            wp_enqueue_style(
                'bridge-directory-style',
                plugins_url( 'assets/css/bridge-directory.css', __DIR__ ),
                [],
                '1.0.0'
            );

            wp_localize_script( 'bridge-directory-frontend', 'bridgeDirectory', [
                'ajax_url'   => admin_url( 'admin-ajax.php' ),
                'columns'    => get_option( 'bridge_directory_columns', 3 ),
                'nonce'      => wp_create_nonce( 'bridge_directory_nonce' ),
            ] );
        }
    }

    public function render_block( $attributes ) {
        ob_start();
        ?>
        <div class="bridge-directory-grid">
            <div class="bridge-directory-search">
                <input type="text" id="bridge-directory-search-input" placeholder="Search...">
            </div>
            <div id="bridge-directory-cards" class="bridge-directory-cards">
                <!-- Cards will be dynamically added here -->
            </div>
            <div id="bridge-directory-loader" class="bridge-directory-loader" style="display: none;">
                Loading...
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}
