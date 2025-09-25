<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class CommuneHub_Assets {

    public static function init() {
        add_action( 'wp_enqueue_scripts', [ __CLASS__, 'frontend' ] );
    }

    public static function frontend() {
        // Styles
        wp_register_style( 'commune-hub-frontend', COMMUNE_HUB_URL . 'assets/css/frontend.css', [], COMMUNE_HUB_VERSION );
        wp_enqueue_style( 'commune-hub-frontend' );

        // JS
        wp_register_script( 'commune-hub-app', COMMUNE_HUB_URL . 'assets/js/app.js', [ 'wp-element', 'wp-api-fetch' ], COMMUNE_HUB_VERSION, true );

        wp_localize_script( 'commune-hub-app', 'communeHub', [
            'root' => esc_url_raw( rest_url( CommuneHub_REST::NS ) ),
            'nonce' => wp_create_nonce( 'wp_rest' ),
            'currentUserId' => get_current_user_id(),
            'i18n' => [
                'createPost' => __( 'Create Post', 'commune-hub' ),
            ]
        ] );

        wp_enqueue_script( 'commune-hub-app' );
    }
}