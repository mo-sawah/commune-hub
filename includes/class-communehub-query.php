<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class CommuneHub_Query {

    public static function init() {}

    public static function fetch_posts( $args ) {
        $defaults = [
            'community' => 0,
            'sort' => 'hot',
            'paged' => 1,
            'per_page' => 20,
            'search' => '',
            'tag' => ''
        ];
        $args = wp_parse_args( $args, $defaults );

        $wp_args = [
            'post_type' => 'ch_discussion',
            'post_status' => 'publish',
            'posts_per_page' => $args['per_page'],
            'paged' => $args['paged'],
            's' => $args['search'],
            'no_found_rows' => false,
        ];

        if ( $args['community'] ) {
            $wp_args['meta_query'] = [
                [
                    'key' => '_ch_community_id',
                    'value' => intval( $args['community'] ),
                    'compare' => '='
                ]
            ];
        }

        if ( $args['tag'] ) {
            $wp_args['tax_query'] = [
                [
                    'taxonomy' => 'ch_tag',
                    'field' => 'slug',
                    'terms' => sanitize_title( $args['tag'] )
                ]
            ];
        }

        switch ( $args['sort'] ) {
            case 'new':
                $wp_args['orderby'] = 'date';
                $wp_args['order'] = 'DESC';
                break;
            case 'top':
                // We'll sort by a meta transient scoring (compute fallback).
                $wp_args['orderby'] = 'date';
                $wp_args['order'] = 'DESC';
                break;
            case 'rising':
            case 'hot':
            default:
                $wp_args['orderby'] = 'date';
                $wp_args['order'] = 'DESC';
        }

        $q = new WP_Query( $wp_args );
        $posts = [];

        foreach ( $q->posts as $p ) {
            $votes   = CommuneHub_Votes::aggregate_votes( $p->ID );
            $created = get_post_time( 'Y-m-d H:i:s', true, $p );
            $hot     = CommuneHub_Votes::hot_score( $p->ID, $created );
            $rising  = CommuneHub_Votes::rising_score( $p->ID );
            $community_id = intval( get_post_meta( $p->ID, '_ch_community_id', true ) );
            $community_title = $community_id ? get_the_title( $community_id ) : '';

            $posts[] = [
                'id' => $p->ID,
                'title' => get_the_title( $p ),
                'author' => get_the_author_meta( 'display_name', $p->post_author ),
                'author_avatar' => get_avatar_url( $p->post_author, [ 'size' => 48 ] ),
                'time' => get_post_time( 'c', true, $p ),
                'excerpt' => wp_trim_words( $p->post_content, 40 ),
                'content' => apply_filters( 'the_content', $p->post_content ),
                'tags' => wp_get_post_terms( $p->ID, 'ch_tag', [ 'fields' => 'names' ] ),
                'community_id' => $community_id,
                'community_name' => $community_title,
                'votes' => $votes,
                'hot_score' => $hot,
                'rising_score' => $rising,
                'comment_count' => intval( get_comments_number( $p->ID ) ),
            ];
        }

        // Manual sort enhancement:
        if ( $args['sort'] === 'top' ) {
            usort( $posts, function( $a, $b ) {
                return $b['votes']['score'] <=> $a['votes']['score'];
            } );
        } elseif ( $args['sort'] === 'hot' ) {
            usort( $posts, function( $a, $b ) {
                return $b['hot_score'] <=> $a['hot_score'];
            } );
        } elseif ( $args['sort'] === 'rising' ) {
            usort( $posts, function( $a, $b ) {
                return $b['rising_score'] <=> $a['rising_score'];
            } );
        }

        return [
            'posts' => $posts,
            'pagination' => [
                'current' => intval( $args['paged'] ),
                'total' => intval( $q->max_num_pages ),
            ]
        ];
    }
}