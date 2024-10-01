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
    }

    public function register_blocks() {
        register_block_type( 'bridge-directory/office-list', [
            'attributes'      => [
                'columns'  => [
                    'type'    => 'number',
                    'default' => 3,
                ],
                'rows'     => [
                    'type'    => 'number',
                    'default' => 5,
                ],
            ],
            'render_callback' => [ $this, 'render_block' ],
        ] );

        wp_register_script(
            'bridge-directory-block',
            plugins_url( 'build/blocks.js', __DIR__ ),
            [ 'wp-blocks', 'wp-element', 'wp-editor' ],
            '1.0.0',
            true
        );

        register_block_type( 'bridge-directory/office-list', [
            'editor_script'   => 'bridge-directory-block',
            'attributes'      => [ /* ... */ ],
            'render_callback' => [ $this, 'render_block' ],
        ] );
    }

    public function render_block( $attributes ) {
        $query   = isset( $_GET['bridge_search'] ) ? sanitize_text_field( $_GET['bridge_search'] ) : '';
        $offices = $this->search_handler->search_offices( $query );

        ob_start();
        ?>

        <form method="get">
            <input type="text" name="bridge_search" placeholder="Search offices..." value="<?php echo esc_attr( $query ); ?>" />
            <button type="submit">Search</button>
        </form>

        <div class="bridge-directory" style="display: grid; grid-template-columns: repeat(<?php echo esc_attr( $attributes['columns'] ); ?>, 1fr); gap: 20px;">
            <?php foreach ( array_slice( $offices, 0, $attributes['rows'] * $attributes['columns'] ) as $office ) : ?>
                <div class="office-item">
                    <h3><?php echo esc_html( $office['OfficeName'] ); ?></h3>
                    <p>Phone: <?php echo esc_html( $office['OfficePhone'] ); ?></p>
                    <p>Email: <?php echo esc_html( $office['OfficeEmail'] ); ?></p>
                    <p>City: <?php echo esc_html( $office['OfficeCity'] ); ?></p>
                </div>
            <?php endforeach; ?>
        </div>

        <?php
        return ob_get_clean();
    }
}
