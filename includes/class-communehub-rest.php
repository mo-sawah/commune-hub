<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class CommuneHub_REST {

    const NS = 'commune-hub/v1';

    public static function init() {
        add_action( 'rest_api_init', [ __CLASS__, 'register_routes' ] );
    }

    public static function register_routes() {
        register_rest_route( self::NS, '/communities', [
            [
                'methods'  => 'GET',
                'callback' => [ __CLASS__, 'get_communities' ],
                'permission_callback' => '__return_true'
            ],
            [
                'methods'  => 'POST',
                'callback' => [ __CLASS__, 'create_community' ],
                'permission_callback' => function() {
                    return current_user_can( 'publish_posts' );
                }
            ]
        ] );

        register_rest_route( self::NS, '/posts', [
            [
                'methods' => 'GET',
                'callback' => [ __CLASS__, 'get_posts' ],
                'permission_callback' => '__return_true'
            ],
            [
                'methods' => 'POST',
                'callback' => [ __CLASS__, 'create_post' ],
                'permission_callback' => function() {
                    return is_user_logged_in();
                }
            ]
        ] );

        register_rest_route( self::NS, '/vote', [
            'methods' => 'POST',
            'callback' => [ __CLASS__, 'vote' ],
            'permission_callback' => function() { return is_user_logged_in(); }
        ] );

        register_rest_route( self::NS, '/membership', [
            'methods' => 'POST',
            'callback' => [ __CLASS__, 'membership' ],
            'permission_callback' => function() { return is_user_logged_in(); }
        ] );

        register_rest_route( self::NS, '/comments', [
            [
                'methods' => 'GET',
                'callback' => [ __CLASS__, 'get_comments' ],
                'permission_callback' => '__return_true'
            ],
            [
                'methods' => 'POST',
                'callback' => [ __CLASS__, 'create_comment' ],
                'permission_callback' => function() { return is_user_logged_in(); }
            ]
        ] );

        register_rest_route( self::NS, '/me', [
            'methods' => 'GET',
            'callback' => [ __CLASS__, 'me' ],
            'permission_callback' => '__return_true'
        ] );
    }

    public static function verify_nonce( $request ) {
        $nonce = $request->get_header( 'x-wp-nonce' );
        if ( ! wp_verify_nonce( $nonce, 'wp_rest' ) ) {
            return new WP_Error( 'invalid_nonce', __( 'Invalid security token', 'commune-hub' ), [ 'status' => 403 ] );
        }
        return true;
    }

    public static function get_communities( $request ) {
        $items = get_posts([
            'post_type' => 'ch_community',
            'post_status' => 'publish',
            'numberposts' => 100,
            'orderby' => 'date',
            'order' => 'DESC'
        ]);
        $out = [];
        foreach ( $items as $c ) {
            $out[] = [
                'id' => $c->ID,
                'name' => get_the_title( $c ),
                'description' => wp_trim_words( $c->post_content, 30 ),
                'members' => CommuneHub_Membership::member_count( $c->ID ),
                'is_member' => is_user_logged_in() ? CommuneHub_Membership::is_member( $c->ID, get_current_user_id() ) : false,
            ];
        }
        return rest_ensure_response( $out );
    }

    public static function create_community( $request ) {
        $check = self::verify_nonce( $request );
        if ( is_wp_error( $check ) ) return $check;

        $title = commune_hub_sanitize_text( $request->get_param( 'name' ) );
        $desc  = wp_kses_post( $request->get_param( 'description' ) );
        if ( ! $title ) {
            return new WP_Error( 'missing_field', __( 'Name required', 'commune-hub' ), [ 'status' => 400 ] );
        }
        $id = wp_insert_post([
            'post_type' => 'ch_community',
            'post_title' => $title,
            'post_content' => $desc,
            'post_status' => 'publish',
            'post_author' => get_current_user_id()
        ]);
        if ( is_wp_error( $id ) ) return $id;
        return [ 'id' => $id ];
    }

    public static function get_posts( $request ) {
        $params = [
            'community' => intval( $request->get_param( 'community' ) ),
            'sort' => commune_hub_sanitize_text( $request->get_param( 'sort' ) ),
            'paged' => max(1, intval( $request->get_param( 'page' ) )),
            'per_page' => min(50, max(1, intval( $request->get_param( 'per_page' ) ) ) ),
            'search' => commune_hub_sanitize_text( $request->get_param( 'search' ) ),
            'tag' => commune_hub_sanitize_text( $request->get_param( 'tag' ) ),
        ];
        $data = CommuneHub_Query::fetch_posts( $params );

        // Add current user vote
        if ( is_user_logged_in() ) {
            $uid = get_current_user_id();
            foreach ( $data['posts'] as &$p ) {
                $p['user_vote'] = CommuneHub_Votes::get_user_vote( $p['id'], $uid );
            }
        }
        return rest_ensure_response( $data );
    }

    public static function create_post( $request ) {
        $check = self::verify_nonce( $request );
        if ( is_wp_error( $check ) ) return $check;

        $title = commune_hub_sanitize_text( $request->get_param( 'title' ) );
        $content = wp_kses_post( $request->get_param( 'content' ) );
        $community_id = intval( $request->get_param( 'community_id' ) );
        $tags = (array) $request->get_param( 'tags' );

        if ( ! $title || ! $community_id ) {
            return new WP_Error( 'missing_field', __( 'Title and community required', 'commune-hub' ), [ 'status' => 400 ] );
        }

        $post_id = wp_insert_post([
            'post_type' => 'ch_discussion',
            'post_title' => $title,
            'post_content' => $content,
            'post_status' => 'publish',
            'post_author' => get_current_user_id()
        ]);

        if ( is_wp_error( $post_id ) ) return $post_id;

        update_post_meta( $post_id, '_ch_community_id', $community_id );

        if ( $tags ) {
            $clean_tags = array_map( 'sanitize_text_field', $tags );
            wp_set_post_terms( $post_id, $clean_tags, 'ch_tag' );
        }

        return [ 'id' => $post_id ];
    }

    public static function vote( $request ) {
        $check = self::verify_nonce( $request );
        if ( is_wp_error( $check ) ) return $check;

        $post_id = intval( $request->get_param( 'post_id' ) );
        $direction = $request->get_param( 'direction' );
        if ( ! in_array( $direction, [ 'up', 'down', 'clear' ], true ) ) {
            return new WP_Error( 'invalid_direction', __( 'Invalid vote action', 'commune-hub' ), [ 'status' => 400 ] );
        }
        $user_id = get_current_user_id();
        $dir = $direction === 'clear' ? 0 : $direction;
        $result = CommuneHub_Votes::record_vote( $post_id, $user_id, $dir );
        $aggregate = CommuneHub_Votes::aggregate_votes( $post_id );
        return [
            'user_vote' => $result,
            'votes' => $aggregate
        ];
    }

    public static function membership( $request ) {
        $check = self::verify_nonce( $request );
        if ( is_wp_error( $check ) ) return $check;
        $community_id = intval( $request->get_param( 'community_id' ) );
        $action = $request->get_param( 'action' );
        if ( ! $community_id || ! in_array( $action, [ 'join', 'leave' ], true ) ) {
            return new WP_Error( 'invalid_params', __( 'Invalid membership params', 'commune-hub' ), [ 'status' => 400 ] );
        }
        $uid = get_current_user_id();
        if ( 'join' === $action ) {
            CommuneHub_Membership::join( $community_id, $uid );
        } else {
            CommuneHub_Membership::leave( $community_id, $uid );
        }
        return [
            'community_id' => $community_id,
            'is_member' => CommuneHub_Membership::is_member( $community_id, $uid ),
            'members' => CommuneHub_Membership::member_count( $community_id )
        ];
    }

    public static function get_comments( $request ) {
        $post_id = intval( $request->get_param( 'post_id' ) );
        $comments = get_comments([
            'post_id' => $post_id,
            'status' => 'approve',
            'orderby' => 'comment_date_gmt',
            'order' => 'ASC',
            'number' => 200
        ]);
        $out = [];
        foreach ( $comments as $c ) {
            $out[] = [
                'id' => $c->comment_ID,
                'author' => get_comment_author( $c ),
                'author_avatar' => get_avatar_url( $c->user_id, [ 'size' => 32 ] ),
                'content' => wpautop( esc_html( $c->comment_content ) ),
                'time' => get_comment_date( 'c', $c ),
                'user_id' => $c->user_id
            ];
        }
        return $out;
    }

    public static function create_comment( $request ) {
        $check = self::verify_nonce( $request );
        if ( is_wp_error( $check ) ) return $check;
        $post_id = intval( $request->get_param( 'post_id' ) );
        $content = sanitize_textarea_field( $request->get_param( 'content' ) );
        if ( ! $post_id || ! $content ) {
            return new WP_Error( 'missing_field', __( 'Post and content required', 'commune-hub' ), [ 'status' => 400 ] );
        }
        $comment_id = wp_insert_comment([
            'comment_post_ID' => $post_id,
            'comment_content' => $content,
            'user_id' => get_current_user_id(),
            'comment_author' => wp_get_current_user()->display_name,
            'comment_author_email' => wp_get_current_user()->user_email,
            'comment_author_IP' => $_SERVER['REMOTE_ADDR'] ?? '',
            'comment_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'comment_date' => current_time( 'mysql' ),
            'comment_approved' => 1
        ]);
        if ( ! $comment_id ) {
            return new WP_Error( 'failed', __( 'Could not create comment', 'commune-hub' ), [ 'status' => 500 ] );
        }
        return [ 'id' => $comment_id ];
    }

    public static function me( $request ) {
        if ( ! is_user_logged_in() ) {
            return [
                'logged_in' => false
            ];
        }
        $u = wp_get_current_user();
        return [
            'logged_in' => true,
            'id' => $u->ID,
            'display_name' => $u->display_name,
            'avatar' => get_avatar_url( $u->ID, [ 'size' => 64 ] )
        ];
    }
}