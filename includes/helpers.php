<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function commune_hub_sanitize_text( $value ) {
    return sanitize_text_field( wp_strip_all_tags( $value ) );
}

function commune_hub_require_login() {
    if ( ! is_user_logged_in() ) {
        return new WP_Error( 'not_logged_in', __( 'Authentication required.', 'commune-hub' ), [ 'status' => 401 ] );
    }
    return true;
}

function commune_hub_current_user_id() {
    return get_current_user_id();
}