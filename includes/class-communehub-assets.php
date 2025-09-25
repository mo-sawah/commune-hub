<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class CommuneHub_Assets {

    public static function init() {
        add_action( 'wp_enqueue_scripts', [ __CLASS__, 'maybe_enqueue' ] );
    }

    /**
     * Decide whether to enqueue on this request.
     * By default only on pages containing the shortcode [commune_hub].
     * Use add_filter( 'commune_hub_always_enqueue', '__return_true' ); to force load everywhere.
     */
    public static function maybe_enqueue() {
        $always = apply_filters( 'commune_hub_always_enqueue', false );
        if ( $always ) {
            self::frontend();
            return;
        }

        // Try to detect shortcode in main queried object.
        if ( is_singular() ) {
            global $post;
            if ( $post && has_shortcode( $post->post_content, 'commune_hub' ) ) {
                self::frontend();
            }
        }
    }

    public static function frontend() {

        // Styles
        wp_register_style(
            'commune-hub-frontend',
            COMMUNE_HUB_URL . 'assets/css/frontend.css',
            [],
            COMMUNE_HUB_VERSION
        );
        wp_enqueue_style( 'commune-hub-frontend' );

        // Script (IIFE build)
        wp_register_script(
            'commune-hub-app',
            COMMUNE_HUB_URL . 'assets/js/app.js',
            [ 'wp-element', 'wp-api-fetch' ],
            COMMUNE_HUB_VERSION,
            true
        );

        $options = [];
        if ( class_exists( 'CommuneHub_Admin' ) ) {
            $options = CommuneHub_Admin::get_options();
        }

        // Provide basic context to front end
        wp_localize_script( 'commune-hub-app', 'communeHub', [
            'root'          => esc_url_raw( rest_url( CommuneHub_REST::NS ) ),
            'nonce'         => wp_create_nonce( 'wp_rest' ),
            'currentUserId' => get_current_user_id(),
            'options'       => $options,
            'i18n'          => [
                'createPost' => __( 'Create Post', 'commune-hub' ),
            ]
        ] );

        wp_enqueue_script( 'commune-hub-app' );
    }
}