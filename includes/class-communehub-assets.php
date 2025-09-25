<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class CommuneHub_Assets {

    public static function init() {
        add_action( 'wp_enqueue_scripts', [ __CLASS__, 'frontend' ] );
    }

    public static function frontend() {
        wp_register_style( 'commune-hub-frontend', COMMUNE_HUB_URL . 'assets/css/frontend.css', [], COMMUNE_HUB_VERSION );
        wp_enqueue_style( 'commune-hub-frontend' );

        wp_register_script(
            'commune-hub-app',
            COMMUNE_HUB_URL . 'assets/js/app.js',
            [ 'wp-element', 'wp-api-fetch' ],
            COMMUNE_HUB_VERSION,
            true
        );

        $options = class_exists('CommuneHub_Admin') ? CommuneHub_Admin::get_options() : [];
        $default_sort = $options['default_sort'] ?? 'hot';
        $per_page_default = $options['items_per_page'] ?? 20;

        $params = [
            'community' => intval( $request->get_param( 'community' ) ),
            'sort'      => $request->get_param( 'sort' ) ? commune_hub_sanitize_text( $request->get_param( 'sort' ) ) : $default_sort,
            'paged'     => max(1, intval( $request->get_param( 'page' ) )),
            'per_page'  => min(50, max(1, intval( $request->get_param( 'per_page' ) ?: $per_page_default ) ) ),
            'search'    => commune_hub_sanitize_text( $request->get_param( 'search' ) ),
            'tag'       => commune_hub_sanitize_text( $request->get_param( 'tag' ) ),
        ];

        wp_enqueue_script( 'commune-hub-app' );
    }
}