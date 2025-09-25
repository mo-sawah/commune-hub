<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class CommuneHub_Post_Types {

    public static function init() {
        add_action( 'init', [ __CLASS__, 'register' ] );
    }

    public static function register() {

        register_post_type( 'ch_community', [
            'label' => __( 'Communities', 'commune-hub' ),
            'public' => true,
            'show_in_rest' => true,
            'supports' => [ 'title', 'editor', 'excerpt', 'thumbnail', 'author' ],
            'has_archive' => false,
            'rewrite' => [ 'slug' => 'community' ],
            'menu_icon' => 'dashicons-groups'
        ] );

        register_post_type( 'ch_discussion', [
            'label' => __( 'Discussions', 'commune-hub' ),
            'public' => true,
            'show_in_rest' => true,
            'supports' => [ 'title', 'editor', 'author', 'comments' ],
            'has_archive' => false,
            'rewrite' => [ 'slug' => 'discussion' ],
            'menu_icon' => 'dashicons-format-chat'
        ] );

        register_taxonomy( 'ch_tag', [ 'ch_discussion' ], [
            'label' => __( 'Discussion Tags', 'commune-hub' ),
            'public' => true,
            'hierarchical' => false,
            'show_in_rest' => true,
            'rewrite' => [ 'slug' => 'ctag' ],
        ] );
    }
}