<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class CommuneHub_Shortcodes {

    public static function init() {
        add_shortcode( 'commune_hub', [ __CLASS__, 'render' ] );
    }

    public static function render( $atts = [] ) {
        ob_start();
        ?>
        <div id="commune-hub-app" class="commune-hub-root" data-props='{}'>
            <div class="commune-hub-loading">
                <div class="ch-spinner"></div>
                <p><?php esc_html_e( 'Loading community...', 'commune-hub' ); ?></p>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}