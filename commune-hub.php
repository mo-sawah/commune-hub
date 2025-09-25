<?php
/**
 * Plugin Name:         Commune Hub
 * Description:         A modern, performant community discussion hub (forums + voting + memberships).
 * Version:             1.0.0
 * Author:              Mohamed Sawah
 * Author URI:          https://sawahsolutions.com
 * Text Domain:         commune-hub
 * Domain Path:         /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'COMMUNE_HUB_VERSION', '1.0.0' );
define( 'COMMUNE_HUB_FILE', __FILE__ );
define( 'COMMUNE_HUB_DIR', plugin_dir_path( __FILE__ ) );
define( 'COMMUNE_HUB_URL', plugin_dir_url( __FILE__ ) );
define( 'COMMUNE_HUB_MIN_PHP', '7.4' );

require_once COMMUNE_HUB_DIR . 'includes/helpers.php';

register_activation_hook( __FILE__, function() {
    require_once COMMUNE_HUB_DIR . 'includes/class-communehub-install.php';
    CommuneHub_Install::activate();
} );

register_deactivation_hook( __FILE__, function() {
    // (Optional) On deactivate we DO NOT drop tables. Data preservation.
} );

add_action( 'plugins_loaded', function() {

    if ( version_compare( PHP_VERSION, COMMUNE_HUB_MIN_PHP, '<' ) ) {
        add_action( 'admin_notices', function() {
            echo '<div class="notice notice-error"><p>';
            esc_html_e( 'Commune Hub requires a newer PHP version.', 'commune-hub' );
            echo '</p></div>';
        } );
        return;
    }

    load_plugin_textdomain( 'commune-hub', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );

    // Loader
    require_once COMMUNE_HUB_DIR . 'includes/class-communehub-loader.php';
    require_once COMMUNE_HUB_DIR . 'includes/class-communehub-post-types.php';
    require_once COMMUNE_HUB_DIR . 'includes/class-communehub-votes.php';
    require_once COMMUNE_HUB_DIR . 'includes/class-communehub-membership.php';
    require_once COMMUNE_HUB_DIR . 'includes/class-communehub-query.php';
    require_once COMMUNE_HUB_DIR . 'includes/class-communehub-rest.php';
    require_once COMMUNE_HUB_DIR . 'includes/class-communehub-assets.php';
    require_once COMMUNE_HUB_DIR . 'includes/class-communehub-shortcodes.php';

    CommuneHub_Loader::init();
} );